# 💰 RÉPARTITION DES PAIEMENTS - TAPRESTATION

## 📊 VUE D'ENSEMBLE

```
╔══════════════════════════════════════════════════════════════════════════════╗
║                        FLUX DE PAIEMENT COMPLET                               ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║                           CLIENT PAIE                                         ║
║                              │                                                ║
║                              ▼                                                ║
║  ┌──────────────────────────────────────────────────────────────────────┐    ║
║  │                         💳 STRIPE                                     │    ║
║  │                    (Prélève ~1.4% + 0.25€)                           │    ║
║  └──────────────────────────────────────────────────────────────────────┘    ║
║                              │                                                ║
║          ┌───────────────────┼───────────────────┐                           ║
║          ▼                   ▼                   ▼                           ║
║  ┌───────────────┐   ┌───────────────┐   ┌───────────────┐                   ║
║  │ 🏢 ADMIN      │   │ 👨‍🍳 PRESTA    │   │ 🚗 LIVREUR   │                   ║
║  │  (Platform)   │   │               │   │ (si delivery) │                   ║
║  │               │   │               │   │               │                   ║
║  │ Commission    │   │ Subtotal -    │   │ Frais de      │                   ║
║  │ + Frais client│   │ Commission    │   │ livraison     │                   ║
║  └───────────────┘   └───────────────┘   └───────────────┘                   ║
║                                                                               ║
╚══════════════════════════════════════════════════════════════════════════════╝
```

---

## 🍕 EXEMPLE CONCRET : COMMANDE FOOD DE 100€

### Paramètres Admin utilisés :
- `commission_food` = **15%** (commission prélevée au prestataire)
- `commission_client_food` = **5%** (frais ajoutés au client)

### ÉTAPE 1 : Ce que le CLIENT paie

| Description | Calcul | Montant |
|-------------|--------|---------|
| Prix des plats (subtotal) | - | 100.00€ |
| + Frais service client | 100€ × 5% | + 5.00€ |
| + Frais de livraison | forfait | + 5.00€ |
| **TOTAL CLIENT** | | **110.00€** |

### ÉTAPE 2 : Stripe prélève ses frais

| Description | Calcul | Montant |
|-------------|--------|---------|
| Frais Stripe | 110€ × 1.4% + 0.25€ | ~1.79€ |

### ÉTAPE 3 : Distribution finale

| Destinataire | Calcul | Montant |
|--------------|--------|---------|
| 🏢 **ADMIN** | Commission (15% × 100€) + Frais client (5€) | **20.00€** |
| 👨‍🍳 **PRESTATAIRE** | Subtotal - Commission (100€ - 15€) | **85.00€** |
| 🚗 **LIVREUR** | Frais de livraison | **5.00€** |
| 💳 **STRIPE** | Frais bancaires | **~1.79€** |

> ⚠️ Note : Les frais Stripe sont déduits du montant global avant distribution.

---

## 🛠️ PARAMÈTRES ADMIN (Clés en Base de Données)

### Commission PRESTATAIRE (prélevée sur leurs gains)

| Type | Clé BD | Description |
|------|--------|-------------|
| Services | `commission_services` | % prélevé sur les prestations de service |
| Locations | `commission_rentals` | % prélevé sur les locations d'équipement |
| Ventes Flash | `commission_urgent_sales` | % prélevé sur les ventes urgentes |
| Food | `commission_food` | % prélevé sur les commandes food |

### Frais CLIENT (ajoutés au prix de base)

| Type | Clé BD | Description |
|------|--------|-------------|
| Services | `commission_client_services` | % ajouté au prix pour le client |
| Locations | `commission_client_rentals` | % ajouté au prix pour le client |
| Ventes Flash | `commission_client_urgent_sales` | % ajouté au prix pour le client |
| Food | `commission_client_food` | % ajouté au prix pour le client |

---

## 📁 FICHIERS CONCERNÉS

### Calcul des totaux (ce que le client paie)

| Fichier | Méthode | Rôle |
|---------|---------|------|
| `app/Models/FoodOrder.php` | `calculateTotals()` | Calcul subtotal + frais client + livraison |
| `app/Http/Controllers/Client/FoodOrderController.php` | `calculateTotals()` | Idem pour les API |

### Distribution des paiements (qui reçoit quoi)

| Fichier | Méthode | Rôle |
|---------|---------|------|
| `app/Models/FoodOrder.php` | `processPayouts()` | Calcul de la répartition finale |
| `app/Services/CommissionService.php` | `feeAmount()` | Calcul du montant de commission |

### Service de commission central

| Fichier | Méthode | Rôle |
|---------|---------|------|
| `app/Services/CommissionService.php` | `ratePercent()` | Récupère le % de commission |
| `app/Services/CommissionService.php` | `feeAmount()` | Calcule le montant de commission |
| `app/Services/CommissionService.php` | `isEnabledFor()` | Vérifie si commission active |

---

## 🔄 FLOW STRIPE CONNECT

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         STRIPE CONNECT FLOW                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  1. Client paie 110€                                                         │
│     └── Stripe crée un PaymentIntent                                        │
│                                                                              │
│  2. Stripe applique :                                                        │
│     └── application_fee_amount = Commission Admin (ex: 20€)                 │
│     └── transfer_data.destination = Compte Stripe du Prestataire            │
│                                                                              │
│  3. Distribution automatique :                                               │
│     └── Admin reçoit : 20€ (application_fee)                                │
│     └── Prestataire reçoit : 90€ - frais Stripe                             │
│                                                                              │
│  4. Le livreur est payé séparément via payout manuel                        │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## ❓ FAQ

### Q: Qui paie les frais Stripe ?
**R:** Les frais Stripe (~1.4% + 0.25€) sont déduits du montant total avant distribution. En pratique, c'est le prestataire qui "absorbe" ces frais car ils sont déduits de sa part.

### Q: Le frais client va où exactement ?
**R:** Le `service_fee` (frais client) va à l'Admin (plateforme). C'est un revenu additionnel pour la plateforme.

### Q: Peut-on désactiver les commissions pour certains utilisateurs ?
**R:** Oui, via les champs :
- `commission_client_disabled` sur la table `users`
- `commission_prestataire_disabled` sur la table `prestataires`

### Q: Comment modifier les pourcentages ?
**R:** Via l'interface Admin → Paramètres → Commissions, ou directement dans la table `settings`.

---

## 📈 RÉSUMÉ VISUEL

```
╔═══════════════════════════════════════════════════════════════════╗
║                    COMMANDE 100€ FOOD                              ║
╠═══════════════════════════════════════════════════════════════════╣
║                                                                    ║
║  CLIENT PAIE           110.00€ (100 + 5 frais + 5 livraison)      ║
║                                                                    ║
║  ┌──────────────────────────────────────────────────────────┐     ║
║  │                                                          │     ║
║  │  💳 STRIPE         ~1.79€   (1.4% + 0.25€)              │     ║
║  │  🏢 ADMIN          20.00€   (15€ commission + 5€ frais) │     ║
║  │  👨‍🍳 PRESTATAIRE    85.00€   (100€ - 15€ commission)    │     ║
║  │  🚗 LIVREUR         5.00€   (frais de livraison)        │     ║
║  │                                                          │     ║
║  └──────────────────────────────────────────────────────────┘     ║
║                                                                    ║
║  TOTAL DISTRIBUÉ : ~111.79€ (égal à ce que client a payé)        ║
║                                                                    ║
╚═══════════════════════════════════════════════════════════════════╝
```
