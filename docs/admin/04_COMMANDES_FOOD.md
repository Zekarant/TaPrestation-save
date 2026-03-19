# 🍕 COMMANDES FOOD (Restauration)

> **⚠️ DOCUMENT VÉRIFIÉ AVEC LA BASE DE PRODUCTION (tapresr689) - 14 Janvier 2026**
> **Commission Food en production : 2% (pas 15%)**

## Vue d'ensemble

Module de commandes restauration avec **code de livraison à 4 chiffres**, gestion **pickup/delivery**, et **politiques de paiement** configurables par produit.

---

## 📊 STATUTS

### Statuts Commande (`food_orders.status`)

| Statut | Label | Description |
|--------|-------|-------------|
| `pending` | En attente | Commande créée, attente validation presta |
| `accepted` | Acceptée | Prestataire a accepté |
| `scheduled` | Planifiée | Commande pour date future (agenda) |
| `preparing` | En préparation | Cuisine en cours |
| `ready` | Prête | Prête à récupérer/livrer |
| `delivered` | Livrée | Remise au client |
| `completed` | Terminée | Code vérifié, paiements distribués |
| `cancelled` | Annulée | Annulée (avec remboursement si payée) |

### Statuts Livraison (`food_orders.delivery_status`)

| Statut | Description |
|--------|-------------|
| `pending` | En attente d'un livreur |
| `assigned` | Livreur assigné |
| `picked_up` | Récupérée par le livreur |
| `in_transit` | En cours de livraison |
| `delivered` | Livrée au client |
| `failed` | Échec de livraison |

### Statuts Escrow (`food_orders.escrow_status`)

| Statut | Description |
|--------|-------------|
| `none` | Pas d'escrow (paiement cash) |
| `pending` | Autorisé, capture en attente |
| `held` | Fonds bloqués sur plateforme |
| `released` | Fonds libérés |
| `refunded` | Remboursé au client |
| `partial_refund` | Remboursement partiel |
| `cancelled` | Autorisation annulée |

---

## 💳 POLITIQUES DE PAIEMENT (`FoodProduct.payment_policy`)

| Policy | Description | Comportement |
|--------|-------------|--------------|
| `cash` | Espèces uniquement | Pas de paiement en ligne |
| `deposit` | Acompte (%) | X% en ligne, reste à la remise |
| `full_prepay` | Prépaiement total | 100% en ligne avant préparation |

### Logique de sélection (`FoodOrder::getPaymentPolicy()`)

```php
// Parcourt les items de la commande
// Si UN produit a 'full_prepay' → toute la commande est 'full_prepay'
// Sinon, prend le deposit_percent le plus élevé
// Par défaut: 'cash'
```

---

## 🔐 SYSTÈME DE CODE À 4 CHIFFRES

### Constantes (FoodOrder.php)

```php
const CODE_EXPIRY_HOURS = 24;      // Code valide 24h
const MAX_CODE_ATTEMPTS = 5;       // Max tentatives
const CODE_LOCK_MINUTES = 30;      // Verrouillage après échecs
```

### Champs associés

```sql
delivery_code        -- Le code 4 chiffres (0000-9999)
code_expires_at      -- Date d'expiration
code_attempts        -- Nombre de tentatives
code_locked_until    -- Date de déverrouillage
code_verified_at     -- Date de vérification réussie
```

### Flux du code

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. GÉNÉRATION                                                               │
│    → Quand: commande marquée "ready"                                        │
│    → Méthode: generateDeliveryCode()                                        │
│    → Code: 4 chiffres aléatoires                                            │
│    → Expire: 24h après génération                                           │
│    → Client notifié avec le code                                            │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ 2. VÉRIFICATION                                                             │
│    → Méthode: verifyDeliveryCode($request, $foodOrder)                      │
│    → Contrôles: expiration, verrouillage, validité                          │
│                                                                             │
│    SI code expiré:                                                          │
│       → Erreur "Code expiré"                                                │
│                                                                             │
│    SI verrouillé (code_locked_until > now):                                 │
│       → Erreur "Trop de tentatives, réessayez dans X minutes"               │
│                                                                             │
│    SI code incorrect:                                                       │
│       → code_attempts++                                                     │
│       → Si attempts >= 5: verrouillage 30 min                               │
│       → Erreur "Code incorrect"                                             │
│                                                                             │
│    SI code correct:                                                         │
│       → code_verified_at = now()                                            │
│       → processPayouts() appelé                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 FLUX COMPLET

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 1: COMMANDE                                                           │
│ → FoodOrderController::placeOrder()                                         │
│ → FoodOrder::create() [status=pending]                                      │
│ → Si cash: notifie prestataire                                              │
│ → Si online: redirect vers paiement                                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 2: PAIEMENT (si online)                                               │
│ → FoodPaymentController::createPaymentIntent()                              │
│   → StripePaymentService::createEscrowPaymentIntent()                       │
│                                                                             │
│ Mode capture selon delivery_type:                                           │
│   • pickup → capture 'automatic' (immédiate)                                │
│   • delivery externe → capture 'manual' (après acceptation)                 │
│                                                                             │
│ → FoodPaymentController::confirmPayment()                                   │
│   → escrow_status='held', amount_held=montant                               │
│   → Notifie prestataire                                                     │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 3: ACCEPTATION                                                        │
│ → FoodOrderController::accept()                                             │
│ → status='accepted'                                                         │
│ → Si date future: status='scheduled'                                        │
│ → Si livraison externe: notifie livreurs                                    │
│                                                                             │
│ Si capture manuelle (delivery externe):                                     │
│   → Attend acceptation vendeur ET livreur                                   │
│   → Puis capturePayment()                                                   │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 4: PRÉPARATION                                                        │
│ → FoodOrderController::startPreparing()                                     │
│ → status='preparing'                                                        │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 5: PRÊT                                                               │
│ → FoodOrderController::markReady()                                          │
│ → status='ready'                                                            │
│ → generateDeliveryCode() → Code 4 chiffres                                  │
│ → Notifie client + livreur (si delivery)                                    │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 6: VÉRIFICATION CODE                                                  │
│ → FoodOrderController::verifyDeliveryCode()                                 │
│ → verifyDeliveryCode($code)                                                 │
│                                                                             │
│ SI CODE CORRECT:                                                            │
│   → processPayouts()                                                        │
│     ├─ Calcule platform_fee via CommissionService::feeAmount()              │
│     ├─ Calcule prestataire_payout = subtotal - platform_fee                 │
│     ├─ Calcule driver_payout = delivery_fee (si livraison)                  │
│     ├─ Stripe Transfer au prestataire (compte connecté)                     │
│     ├─ Stripe Transfer au livreur (compte connecté)                         │
│     ├─ status = 'completed'                                                 │
│     └─ escrow_status = 'released'                                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 💰 MÉTHODE `processPayouts()` (FoodOrder.php)

```php
/**
 * DISTRIBUTION DES PAIEMENTS (sur 100€ subtotal):
 * 
 * 💰 STRIPE (prélevé automatiquement)
 *    ~1.4% + 0.25€ sur le total
 * 
 * 🏢 ADMIN reçoit:
 *    • Commission prestataire (2% de 100€ = 2€) ← VALEUR PRODUCTION !
 *    • Frais service client (si configuré)
 * 
 * 👨‍🍳 PRESTATAIRE reçoit:
 *    Subtotal - Commission = 100€ - 2€ = 98€
 * 
 * 🚗 LIVREUR reçoit (si livraison):
 *    Frais de livraison = 5€ (100% au livreur)
 */
```

### Actions de processPayouts()

1. Calcule `platform_fee` via `CommissionService::feeAmount()` (param: `commission_food`)
2. Calcule `prestataire_payout = subtotal - platform_fee`
3. Calcule `driver_payout = delivery_fee` (si mode livraison)
4. **Stripe Transfer au prestataire** si compte connecté valide
5. **Stripe Transfer au livreur** si compte connecté valide
6. Met à jour: `status = completed`, `escrow_status = released`
7. Enregistre les IDs de transfer Stripe

---

## 💰 EXEMPLE: Commande 100€ + Livraison 5€ (AVEC COMMISSION 2%)

```
CLIENT PAIE:
├── Prix des plats (subtotal) .......... 100,00€
├── Frais service client (5%) ..........   5,00€
├── Frais de livraison .................   5,00€
└── TOTAL .............................. 110,00€

STRIPE PRÉLÈVE (~1,4% + 0,25€) .........   1,79€
NET PLATEFORME ......................... 108,21€

═══ DISTRIBUTION FINALE ═══

🏢 ADMIN REÇOIT:
├── Commission prestataire (2% de 100€)  2,00€  ← VALEUR PRODUCTION !
└── Frais service client ............... 5,00€
    TOTAL ADMIN ........................ 7,00€

👨‍🍳 PRESTATAIRE REÇOIT:
└── Subtotal - Commission .............. 98,00€ ← Transfer Stripe

🚗 LIVREUR REÇOIT:
└── Frais de livraison ................. 5,00€ ← Transfer Stripe

💳 STRIPE A PRÉLEVÉ:
└── Frais bancaires .................... 1,79€

Note: Commission prélevée sur SUBTOTAL (pas sur livraison ni frais service)
```

### 📝 EXEMPLE RÉEL DE PRODUCTION (Food Order #82)

```
Commande #82 (14 Jan 2026):
├── Total: 6,00€
├── Commission rate: 0% (commission food était 0% à ce moment)
├── Prestataire reçoit: 6,00€ (100%)
├── escrow_status: released
└── Stripe Transfer: tr_3SpFBMGxYcZCLnN01UqkaW2w
```

---

## 🚨 CAS SPÉCIAUX

### Prestataire refuse

```
→ status = 'cancelled'
→ Si paiement en ligne: Stripe refund 100%
→ escrow_status = 'refunded'
→ Client notifié
```

### Code expiré (24h)

```
→ CRON: FoodRefundExpiredOrders
→ Cherche commandes avec escrow_status='held' ET code_expires_at < NOW
→ Rembourse automatiquement via Stripe
→ escrow_status = 'refunded'
→ Client notifié
```

### Paiement cash

```
→ Aucun paiement en ligne
→ escrow_status = 'none'
→ Client paie en espèces au retrait/livraison
→ Commission plateforme = 0% (hors système)
```

---

## 🗄️ TABLE `food_orders`

```sql
-- Identifiants
id, order_number, client_id, prestataire_id, driver_id

-- Statuts
status, delivery_status, payment_status, escrow_status

-- Montants
subtotal, delivery_fee, driver_commission, service_fee, total
prestataire_payout, driver_payout, platform_fee
amount_held, amount_released, amount_refunded

-- Livraison
delivery_type, delivery_address, delivery_lat, delivery_lng
delivery_distance, estimated_delivery_time, delivery_phone
delivery_floor, delivery_door_code, delivery_building_info
delivery_contact_name, driver_notes

-- Code de retrait
delivery_code, code_expires_at, code_attempts, code_locked_until, code_verified_at

-- Paiement
payment_method, payment_intent_id, stripe_payment_intent_id
stripe_transfer_id, driver_stripe_transfer_id

-- RGPD
payment_terms_version, payment_terms_accepted_at, payment_terms_ip

-- Dates
requested_at, accepted_at, preparing_at, ready_at
picked_up_at, delivered_at, completed_at, cancelled_at
paid_at, prestataire_paid_at, driver_paid_at
held_at, released_at, refunded_at, driver_accepted_at

-- Autres
notes, cancellation_reason, refund_reason
client_confirmed, prestataire_confirmed
created_at, updated_at, deleted_at
```

---

## 📁 FICHIERS CODE

| Fichier | Rôle |
|---------|------|
| `app/Models/FoodOrder.php` | Modèle + processPayouts(), verifyDeliveryCode() |
| `app/Models/FoodProduct.php` | Modèle produit + payment_policy |
| `app/Http/Controllers/Client/FoodOrderController.php` | Passage commande |
| `app/Http/Controllers/Client/FoodPaymentController.php` | Paiement client |
| `app/Http/Controllers/Prestataire/FoodOrderController.php` | Gestion presta |
| `app/Console/Commands/FoodRefundExpiredOrders.php` | CRON remboursement |
| `app/Services/CommissionService.php` | Calcul commissions |
