# 🔥 VENTES FLASH / URGENT SALES

> **⚠️ DOCUMENT VÉRIFIÉ AVEC LA BASE DE PRODUCTION (tapresr689) - 14 Janvier 2026**
> **Commission Urgent Sales en production : 0% (DÉSACTIVÉ)**

## Vue d'ensemble

Permet aux utilisateurs (prestataires OU clients) de publier des annonces de vente avec paiement sécurisé via **Escrow** ou **réservation simple** (sans paiement en ligne).

---

## 📊 ARCHITECTURE - 3 MODÈLES

### 1. `UrgentSale` (L'annonce)

**Statuts:**
| Statut | Description |
|--------|-------------|
| `active` | Visible et achetable |
| `sold` | Stock épuisé ou vendu manuellement |
| `withdrawn` | Retiré par le vendeur |
| `reported` | Signalé par des utilisateurs |
| `blocked` | Bloqué par l'admin |

**Champs importants:**
- `payment_requirement` → `'none'` ou `'full'`
- `quantity` → Stock total
- `reserved_quantity` → Stock réservé
- `sold_quantity` → Stock vendu

### 2. `UrgentSalePurchase` (L'achat avec paiement)

**Statuts:**
| Statut | Description |
|--------|-------------|
| `paid` | Paiement effectué |
| `cancelled` | Annulé |
| `refunded` | Remboursé |

**Champs:**
```sql
id, urgent_sale_id, buyer_user_id, payment_transaction_id, escrow_id,
quantity, unit_price, total_amount, currency, status, created_at, updated_at
```

### 3. `UrgentSaleReservation` (Sans paiement en ligne)

**Statuts:**
| Statut | Description |
|--------|-------------|
| `pending` | En attente confirmation vendeur |
| `confirmed` | Confirmé, stock réservé |
| `cancelled` | Annulé |
| `completed` | Vendu/finalisé hors ligne |

---

## 🔄 FLUX SELON `payment_requirement`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    CLIENT CONSULTE UNE ANNONCE                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
                      [payment_requirement?]
                                    ↓
          ┌─────────────────────────┴─────────────────────────┐
          ↓                                                   ↓
┌─────────────────────────────────────┐     ┌─────────────────────────────────────┐
│    payment_requirement = 'full'     │     │    payment_requirement = 'none'     │
├─────────────────────────────────────┤     ├─────────────────────────────────────┤
│                                     │     │                                     │
│ 1. Ajoute au panier                 │     │ 1. Envoie demande de réservation    │
│    → Cart + CartItem                │     │    → UrgentSaleReservation          │
│                                     │     │    → status='pending'               │
│ 2. Checkout Stripe                  │     │                                     │
│    → CartPaymentController::        │     │ 2. Vendeur confirme                 │
│       createIntent()                │     │    → status='confirmed'             │
│    → PaymentIntent sur PLATEFORME   │     │    → Stock réservé                  │
│    → PAS sur compte vendeur         │     │                                     │
│                                     │     │ 3. Transaction hors-ligne           │
│ 3. Paiement réussi                  │     │    (espèces, virement, etc.)        │
│    → CartPaymentController::        │     │                                     │
│       confirm()                     │     │ 4. Vendeur marque 'completed'       │
│    → UrgentSalePurchase créé        │     │    → Stock mis à jour               │
│    → EscrowTransaction créé         │     │                                     │
│    → Stock décrémenté               │     │                                     │
│    → ARGENT BLOQUÉ EN ESCROW        │     │                                     │
│                                     │     │                                     │
│ 4. Vendeur expédie/remet            │     │                                     │
│    → Marque 'delivered'             │     │                                     │
│    → Démarre délai 48h              │     │                                     │
│                                     │     │                                     │
│ 5. ACHETEUR CONFIRME                │     │                                     │
│    ou 48h sans action               │     │                                     │
│    → Escrow libéré au vendeur       │     │                                     │
│    → Transfer Stripe Connect        │     │                                     │
└─────────────────────────────────────┘     └─────────────────────────────────────┘
```

---

## 💰 SYSTÈME ESCROW POUR URGENT SALES

### Création escrow (dans CartPaymentController::confirm)

```php
// Ligne ~570-590 dans CartPaymentController.php
$escrow = $this->escrowService->createEscrow(
    escrowable: $purchase,                    // UrgentSalePurchase
    clientId: (int) $clientId,
    prestataireId: $prestataireId,
    amount: $lineCharged,                     // Montant total payé
    stripePaymentIntentId: $paymentIntent->id,
    platformFeeOverride: $platformFee,        // Commission calculée
);

// Lien escrow_id → urgent_sale_purchases
DB::table('urgent_sale_purchases')
    ->where('id', $purchase->id)
    ->update(['escrow_id' => $escrow->id]);
```

### Flux escrow

```
Paiement réussi
     ↓
Fonds bloqués sur PLATEFORME (escrow status: pending/held)
     ↓
Vendeur expédie → marque 'delivered'
     ↓
Acheteur confirme OU 48h passent
     ↓
EscrowService::releaseToPrestataire()
     ↓
Transfer Stripe vers compte connecté vendeur
```

---

## 🚚 GESTION LIVRAISON

### Options disponibles

| Mode | Description |
|------|-------------|
| Remise en main propre | Vendeur et acheteur se rencontrent |
| Expédition | Envoi par transporteur avec suivi |
| Point relais | Via réseau de points relais |

### Routes escrow (routes/escrow.php)

```php
// Actions vendeur
Route::post('/{escrow}/shipment', [EscrowController::class, 'createShipment']);
Route::post('/{escrow}/mark-delivered', [EscrowController::class, 'markUrgentSaleDelivered']);

// Actions acheteur
Route::post('/{escrow}/confirm-urgent-sale', [EscrowController::class, 'confirmUrgentSale']);
Route::post('/{escrow}/non-conformity', [EscrowController::class, 'reportNonConformity']);
```

---

## 💰 CALCUL MONTANTS (Exemple: 80€)

### AVEC COMMISSION 10% (valeur code, mais 0% en production)

```
ACHETEUR PAIE .......................... 80,00€
STRIPE PRÉLÈVE (~1,4% + 0,25€) .........  1,37€
NET PLATEFORME ......................... 78,63€

═══ VENTE RÉUSSIE (si commission 10%) ═══
├── 🏢 Admin (commission 10%) .......... 8,00€
└── 👨‍💼 Vendeur (90%) .................. 72,00€ ← Transfer Stripe

═══ VENTE RÉUSSIE PRODUCTION (commission 0%) ═══
├── 🏢 Admin (commission 0%) ...........  0,00€
└── 👨‍💼 Vendeur (100%) ................. 80,00€ ← Transfer Stripe

═══ LITIGE SPLIT AUTO (après 7j) ═══
├── 👤 Acheteur (40%) .................. 32,00€ ← Refund
├── 👨‍💼 Vendeur (60%) .................. 48,00€ ← Transfer
└── 🏢 Admin ........................... 0,00€ (rendue)
```

### 📝 EXEMPLES RÉELS DE PRODUCTION

```
UrgentSalePurchase #2 (escrow_id=1):
├── Total: 10,00€
├── Commission rate: 10%
├── Commission: 1,00€
├── Vendeur reçoit: 9,00€
└── Status: partial (en attente confirmation)

UrgentSalePurchase #3 (escrow_id=2):
├── Total: 3,00€
├── Commission rate: 10%
├── Commission: 0,59€
├── Vendeur reçoit: 2,41€
├── Status: partial
└── Transfer: tr_3SoOdsGxYcZCLnN0042kQPBc

UrgentSalePurchase #4 (escrow_id=3):
├── Total: 3,00€
├── Commission rate: 5% (réduit ?)
├── Commission: 0,59€
├── Vendeur reçoit: 2,41€
└── Status: partial
```

---

## 🚨 RÈGLES AUTOMATIQUES

### Auto-release après 48h

```
SI escrow.status = 'pending'
ET livraison marquée 'delivered'
ET acheteur n'a pas confirmé
ET (NOW - delivered_at) > 48 heures
ALORS releaseToPrestataire() automatique
```

### Split automatique litige (7 jours)

```
SI litige.status = 'open'
ET (NOW - litige.opened_at) > 7 jours
ET aucun accord trouvé
ALORS 
    rembourser_acheteur(40%)
    payer_vendeur(60%)
    commission_admin = 0% (rendue)
```

### Commission rendue si problème

```
SI remboursement total OU litige
ALORS commission_admin = 0 (pas prélevée)
```

---

## 🗄️ TABLE `urgent_sales`

```sql
id, prestataire_id, user_id,  -- Vendeur peut être presta OU client
title, description, price,
payment_requirement,          -- 'none' ou 'full'
condition,                    -- new/good/used/fair/excellent/very_good/poor
category_id, photos,          -- JSON array
quantity, reserved_quantity, sold_quantity,
location, latitude, longitude,
status,                       -- active/sold/withdrawn/reported/blocked
slug, views_count, contact_count,
inventory_item_id,            -- Lien inventaire si applicable
payment_consent_at, payment_consent_ip, payment_consent_user_agent,
created_at, updated_at, deleted_at
```

## 🗄️ TABLE `urgent_sale_purchases`

```sql
id, urgent_sale_id, buyer_user_id,
payment_transaction_id, escrow_id,
quantity, unit_price, total_amount, currency,
status,  -- paid/cancelled/refunded
created_at, updated_at
```

## 🗄️ TABLE `urgent_sale_reservations`

```sql
id, urgent_sale_id, client_id,
quantity, status,  -- pending/confirmed/cancelled/completed
message, seller_notes,
confirmed_at, completed_at, cancelled_at,
created_at, updated_at
```

---

## 📁 FICHIERS CODE

| Fichier | Rôle |
|---------|------|
| `app/Models/UrgentSale.php` | Modèle annonce |
| `app/Models/UrgentSalePurchase.php` | Modèle achat |
| `app/Models/UrgentSaleReservation.php` | Modèle réservation |
| `app/Http/Controllers/Payment/CartPaymentController.php` | Checkout panier |
| `app/Http/Controllers/Payment/EscrowController.php` | Gestion escrow |
| `app/Http/Controllers/Prestataire/UrgentSaleController.php` | CRUD vendeur |
| `app/Http/Controllers/Client/UrgentSaleController.php` | Vue acheteur |
| `app/Http/Controllers/UrgentSaleReservationController.php` | Réservations |
| `app/Services/EscrowService.php` | Logique escrow |
