# Système d'Acompte/Escrow pour les Commandes Food

## 📋 Vue d'ensemble

Ce système permet au vendeur de définir une politique de paiement par produit :
- **Espèces** : paiement à la remise (aucun paiement en ligne)
- **Acompte** : % payé en ligne à la commande, solde en espèces à la remise
- **Prépaiement total** : 100% payé en ligne à la commande

Les fonds payés en ligne sont **bloqués (escrow)** et libérés uniquement après validation du code par le vendeur.

---

## 🔄 Flux complet

### 1. Configuration du produit (vendeur)
Le vendeur configure dans la fiche produit :
- `payment_policy` : `cash`, `deposit`, ou `full_prepay`
- `deposit_percent` : pourcentage d'acompte (si `deposit`)

### 2. Commande (client)
- Le client passe commande
- Selon la politique de paiement des produits :
  - **cash** → aucun paiement, choix espèces affiché
  - **deposit** → acompte calculé (% du total), PaymentIntent Stripe créé
  - **full_prepay** → total payé, PaymentIntent Stripe créé

### 3. Paiement (client)
- Si paiement en ligne : les fonds sont capturés et mis en **escrow**
- `escrow_status` = `held`
- `amount_held` = montant payé
- `held_at` = date/heure
- `code_expires_at` = maintenant + 24h

### 4. Commande prête (vendeur)
- Le vendeur marque la commande comme "prête"
- Le client reçoit une notification push avec le code à 4 chiffres
- Le code est valide 24h

### 5. Vérification du code (vendeur)
Le vendeur saisit le code donné par le client :
- **Code correct** → 
  - `code_verified_at` = maintenant
  - `escrow_status` = `released`
  - `amount_released` = montant
  - Paiement espèces auto-confirmé si applicable
- **Code incorrect** →
  - `code_attempts` +1
  - Après 5 tentatives → verrouillé 30 min
- **Code expiré (24h)** →
  - Commande annulée automatiquement
  - Remboursement intégral via Stripe

### 6. Refus de commande (vendeur)
Si le vendeur refuse la commande et que des fonds étaient bloqués :
- Remboursement automatique via Stripe
- `escrow_status` = `refunded`
- `amount_refunded` = montant
- `refund_reason` = "Commande refusée par le vendeur"

### 7. Timeout 24h (automatique)
Une tâche planifiée tourne toutes les 30 minutes :
- Cherche les commandes avec `escrow_status=held` et `code_expires_at` dépassé
- Rembourse automatiquement
- Annule la commande
- Notifie le client

---

## 📁 Fichiers modifiés/créés

### Base de données (SQL à copier dans phpMyAdmin)
```
database/migrations/2026_01_06_add_food_payment_escrow_fields.sql
```

### Modèles Laravel
```
app/Models/FoodOrder.php        → Ajout escrow_status, amount_held, etc. + méthodes
app/Models/FoodProduct.php      → Ajout payment_policy, deposit_percent
```

### Contrôleurs
```
app/Http/Controllers/Prestataire/FoodOrderController.php   → Sécurité code + auto-refund
app/Http/Controllers/Prestataire/FoodProductController.php → Validation payment_policy
app/Http/Controllers/Client/FoodPaymentController.php      → Gestion acompte/escrow
```

### Vues Blade
```
resources/views/prestataire/food-products/create.blade.php → Formulaire payment_policy
resources/views/prestataire/food-products/edit.blade.php   → Formulaire payment_policy
resources/views/client/food-orders/payment.blade.php       → Affichage acompte/escrow
```

### Commandes Artisan
```
app/Console/Commands/FoodRefundExpiredOrders.php → Remboursement auto timeout
```

### Notifications
```
app/Notifications/FoodOrderRefunded.php → Notification remboursement
```

### Planificateur
```
app/Console/Kernel.php → Ajout food:refund-expired toutes les 30 min
```

---

## 🗃️ Tables modifiées

### Table `food_products`
| Colonne | Type | Description |
|---------|------|-------------|
| payment_policy | ENUM | 'cash', 'deposit', 'full_prepay' |
| deposit_percent | TINYINT | Pourcentage acompte (10-100) |

### Table `food_orders`
| Colonne | Type | Description |
|---------|------|-------------|
| escrow_status | ENUM | 'none', 'held', 'released', 'refunded', 'partial_refund' |
| amount_held | DECIMAL | Montant bloqué |
| amount_released | DECIMAL | Montant libéré au vendeur |
| amount_refunded | DECIMAL | Montant remboursé au client |
| held_at | DATETIME | Date de blocage |
| released_at | DATETIME | Date de libération |
| refunded_at | DATETIME | Date de remboursement |
| refund_reason | VARCHAR | Raison du remboursement |
| code_attempts | TINYINT | Tentatives de code |
| code_locked_until | DATETIME | Verrouillage après 5 erreurs |
| code_expires_at | DATETIME | Expiration du code (24h) |

### Nouvelle table `food_order_code_attempts`
| Colonne | Type | Description |
|---------|------|-------------|
| id | BIGINT | Clé primaire |
| food_order_id | BIGINT | FK vers food_orders |
| user_id | BIGINT | Qui a tenté |
| ip_address | VARCHAR | IP de la tentative |
| code_entered | VARCHAR | Code saisi |
| success | TINYINT | 1 si réussi |
| created_at | TIMESTAMP | Date/heure |

---

## 🔐 Sécurité anti-brute-force

1. **Maximum 5 tentatives** par commande
2. **Verrouillage 30 minutes** après 5 erreurs
3. **Log de chaque tentative** (IP, user, code saisi)
4. **Expiration du code** après 24h

---

## ⏰ Tâche planifiée (Cron)

Ajouter ce cron sur le serveur :
```bash
* * * * * cd /path/to/taprestation && php artisan schedule:run >> /dev/null 2>&1
```

La commande `food:refund-expired` tourne automatiquement toutes les 30 minutes.

---

## 💳 Stripe

Le système utilise Stripe pour :
- Créer les PaymentIntent (paiement)
- Créer les Refunds (remboursement)

Assurez-vous que les clés Stripe sont configurées dans `.env` :
```
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
```

---

## ✅ Résumé des protections

| Risque | Protection |
|--------|------------|
| Vendeur ne donne pas le produit | Client garde son argent (non libéré) |
| Client dit le code sans payer le solde | Le vendeur a reçu l'acompte |
| Brute-force du code | 5 tentatives max + lock 30 min |
| Code jamais vérifié | Auto-refund après 24h |
| Vendeur refuse | Auto-refund immédiat |

---

## 📤 Liste des fichiers à uploader

1. `database/migrations/2026_01_06_add_food_payment_escrow_fields.sql` _(exécuter dans phpMyAdmin)_
2. `app/Models/FoodOrder.php`
3. `app/Models/FoodProduct.php`
4. `app/Http/Controllers/Prestataire/FoodOrderController.php`
5. `app/Http/Controllers/Prestataire/FoodProductController.php`
6. `app/Http/Controllers/Client/FoodPaymentController.php`
7. `resources/views/prestataire/food-products/create.blade.php`
8. `resources/views/prestataire/food-products/edit.blade.php`
9. `resources/views/client/food-orders/payment.blade.php`
10. `app/Console/Commands/FoodRefundExpiredOrders.php`
11. `app/Notifications/FoodOrderRefunded.php`
12. `app/Console/Kernel.php`

**Après upload, exécuter :**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
