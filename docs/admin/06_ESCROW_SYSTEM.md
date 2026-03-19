# 🔐 SYSTÈME ESCROW - DOCUMENTATION TECHNIQUE

> **⚠️ DOCUMENT VÉRIFIÉ AVEC LA BASE DE PRODUCTION (tapresr689) - 14 Janvier 2026**
> **Paramètres Escrow en production:**
> - `escrow_enabled` = 1 (activé)
> - `escrow_auto_release_hours` = 48h
> - `escrow_commission_rate` = 20% (défaut)
> - `escrow_min_amount` = 5€
> - `escrow_max_dispute_days` = 30 jours

## Vue d'ensemble

L'**Escrow** (séquestre) est un système de paiement sécurisé où les fonds sont **bloqués sur le compte de la plateforme** jusqu'à ce que la prestation/livraison soit confirmée.

```
CLIENT PAIE → Fonds BLOQUÉS (plateforme) → PRESTATION → Fonds LIBÉRÉS (prestataire)
```

---

## 📊 STATUTS ESCROW (`escrow_transactions.status`)

| Statut | Description |
|--------|-------------|
| `pending` | Fonds en attente / bloqués |
| `held` | Argent capturé et bloqué |
| `partial` | Partiellement libéré |
| `released` | Entièrement libéré au prestataire |
| `refunded` | Remboursé au client |
| `disputed` | Litige en cours |
| `cancelled` | Annulé |

---

## 🏗️ ARCHITECTURE

### Table `escrow_transactions`

```sql
id
escrowable_type         -- 'App\Models\Booking', 'App\Models\FoodOrder', etc.
escrowable_id           -- ID de l'entité liée
client_id               -- → users.id
prestataire_id          -- → prestataires.id

-- Montants
total_amount            -- Montant total bloqué
deposit_amount          -- Caution (pour locations)
remaining_amount        -- Reste à payer
commission_rate         -- Taux commission (ex: 10.00)
commission_amount       -- Montant commission
prestataire_amount      -- Montant pour le presta

-- Statut
status                  -- pending/held/partial/released/refunded/disputed/cancelled

-- Stripe
stripe_payment_intent_id
stripe_transfer_id

-- Dates
auto_release_at         -- Date de libération automatique (48h)
paid_at
released_at
refunded_at

-- Metadata
metadata                -- JSON avec infos supplémentaires

created_at, updated_at
```

### Relation polymorphique (escrowable)

```php
// Dans EscrowTransaction.php
public function escrowable()
{
    return $this->morphTo();
}

// Types supportés:
// - App\Models\Booking (services)
// - App\Models\EquipmentRentalRequest (locations)
// - App\Models\UrgentSalePurchase (ventes flash)
// - App\Models\FoodOrder (food) → utilise son propre système escrow
```

---

## 🔧 EscrowService.php - MÉTHODES PRINCIPALES

### `createEscrow()`

```php
public function createEscrow(
    $escrowable,                  // Booking, EquipmentRentalRequest, UrgentSalePurchase
    int $clientId,
    int $prestataireId,
    float $amount,
    float $depositAmount = 0,     // Caution (locations)
    ?string $stripePaymentIntentId = null,
    ?float $platformFeeOverride = null,
    array $metadata = []
): EscrowTransaction
```

**Ce qui se passe:**
1. Auto-détection du type (service, rental, urgent_sale)
2. Calcul via `CommissionService::netAmountForPrestataire()`
3. INSERT dans `escrow_transactions`:
   - `status = 'pending'`
   - `auto_release_at = now() + 48h`
   - Tous les montants ventilés

### `releaseToPrestataire()`

```php
public function releaseToPrestataire(int $escrowId): array
```

**Ce qui se passe:**
1. Vérifier que escrow n'est pas déjà released/refunded
2. Calculer le montant à transférer (`prestataire_amount`)
3. Si Stripe Connect actif:
   - `StripeService::transferToConnectedAccount()`
4. Mettre à jour `escrow.status = 'released'`
5. Incrémenter `prestataires.balance`
6. Enregistrer dans `finance_ledger`

### `refundClient()`

```php
public function refundClient(int $escrowId, ?string $reason = null): array
```

**Ce qui se passe:**
1. Créer un Stripe Refund sur le PaymentIntent original
2. Mettre à jour `escrow.status = 'refunded'`
3. Enregistrer `refunded_at` et `refund_reason`

### `clientConfirm()`

```php
public function clientConfirm(int $escrowId): array
```

**Ce qui se passe:**
1. Client confirme la prestation réalisée
2. Appelle `releaseToPrestataire()`

### `processExpiredEscrows()` (CRON)

```php
public function processExpiredEscrows(): int
```

**Ce qui se passe:**
1. Cherche escrows avec `status='pending'` ET `auto_release_at <= now()`
2. Pour chaque: `releaseToPrestataire()`
3. Retourne le nombre traité

---

## 🔄 FLUX PAR TYPE

### Services (Booking)

```
Paiement → createEscrow() → status='pending', auto_release_at=+48h
                                    ↓
Service réalisé → BookingController::complete()
                                    ↓
Client confirme → clientConfirm() → releaseToPrestataire()
    OU
48h passent → processExpiredEscrows() → releaseToPrestataire()
```

### Locations (Equipment)

```
Paiement → createEscrow() avec deposit_amount (caution)
                                    ↓
Location en cours
                                    ↓
Retour équipement → returnEquipment($escrowId, $condition, $damagePercent)
                                    ↓
SI bon état:
  → 100% caution remboursée client
  → Location transférée presta
                                    ↓
SI dégâts:
  → X% caution retenue → presta
  → Reste caution → client
  → Location transférée presta
```

### Ventes Flash (UrgentSale)

```
Achat via panier → CartPaymentController::confirm()
                → createEscrow(UrgentSalePurchase)
                                    ↓
Vendeur marque livré → markUrgentSaleDelivered()
                                    ↓
Acheteur confirme → confirmUrgentSale() → releaseToPrestataire()
    OU
48h passent → processExpiredEscrows() → releaseToPrestataire()
```

### Food

⚠️ **Food utilise son propre système escrow** dans `FoodOrder`:
- Champs: `escrow_status`, `amount_held`, `amount_released`
- Méthode: `processPayouts()` dans le modèle FoodOrder
- Pas de création dans `escrow_transactions`

---

## 🚨 GESTION DES LITIGES

### Ouvrir un litige

```php
EscrowService::openDispute(
    int $escrowId, 
    int $userId, 
    string $reason,  // 'service_not_provided', 'item_damaged', etc.
    ?string $description = null
): array
```

**Actions:**
- `escrow.status = 'disputed'`
- Crée une entrée dans `disputes`
- Notifications envoyées

### Types de litiges

| Raison | Comportement |
|--------|--------------|
| `service_not_provided` | Remboursement total automatique |
| `item_damaged` | Médiation requise |
| `item_not_received` | Médiation requise |
| `other` | Médiation requise |

### Auto-split après 7 jours

```php
EscrowService::processExpiredDisputesAutoSplit()
```

Si litige ouvert > 7 jours sans accord:
- **40%** remboursé au client
- **60%** payé au vendeur
- **Commission admin = 0%** (rendue)

---

## 💰 RÈGLES D'ANNULATION

### Méthode `calculateCancellationRefund()`

```php
// Pour SERVICES (Booking)
$cancelHours = $service->cancellation_hours ?? 24;

if ($hoursUntilStart < 0) {
    // Prestation en cours = 0%
    $refundPercent = 0;
} elseif ($hoursUntilStart < $cancelHours) {
    // Hors délai = % configuré
    $refundPercent = $service->cancellation_refund_percentage ?? 0;
} else {
    // Dans les délais = 100%
    $refundPercent = 100;
}

// Pour LOCATIONS (Equipment)
$cancelHours = $equipment->cancellation_hours ?? 48;
// Même logique
```

---

## ⚙️ TÂCHES CRON

### 1. Auto-release 48h

```php
// Schedule: toutes les heures
EscrowService::processExpiredEscrows()

// Query
SELECT * FROM escrow_transactions 
WHERE status = 'pending' 
AND auto_release_at <= NOW()
AND dispute_id IS NULL;
```

### 2. Auto-split litiges 7 jours

```php
// Schedule: quotidien
EscrowService::processExpiredDisputesAutoSplit()

// Query
SELECT * FROM disputes
WHERE status = 'open'
AND created_at <= NOW() - INTERVAL 7 DAY;
```

### 3. Nettoyage escrows expirés (Food)

```php
// Schedule: toutes les 30 min
FoodRefundExpiredOrders

// Query
SELECT * FROM food_orders
WHERE escrow_status = 'held'
AND code_expires_at < NOW();
```

---

## 📁 FICHIERS CODE

| Fichier | Rôle |
|---------|------|
| `app/Services/EscrowService.php` | Logique métier principale |
| `app/Models/EscrowTransaction.php` | Modèle Eloquent |
| `app/Http/Controllers/Payment/EscrowController.php` | API escrow |
| `app/Services/CommissionService.php` | Calcul commissions |
| `app/Services/StripePaymentService.php` | Transfers Stripe |
| `routes/escrow.php` | Routes escrow |
| `database/migrations/*escrow*.php` | Structure table |

---

## 🔍 DEBUG - Vérifier un escrow

```php
$escrow = EscrowTransaction::with('escrowable')->find($id);

dd([
    'id' => $escrow->id,
    'type' => $escrow->escrowable_type,
    'status' => $escrow->status,
    'total_amount' => $escrow->total_amount,
    'commission_amount' => $escrow->commission_amount,
    'prestataire_amount' => $escrow->prestataire_amount,
    'auto_release_at' => $escrow->auto_release_at,
    'stripe_payment_intent_id' => $escrow->stripe_payment_intent_id,
    'stripe_transfer_id' => $escrow->stripe_transfer_id,
]);
```

### Via SQL

```sql
SELECT 
    et.*,
    TIMESTAMPDIFF(HOUR, NOW(), auto_release_at) as hours_until_release
FROM escrow_transactions et
WHERE status = 'pending'
ORDER BY auto_release_at ASC;
```

---

## 📝 DONNÉES RÉELLES DE PRODUCTION (14 Jan 2026)

### Escrow #1 - UrgentSalePurchase #2
```
┌─────────────────────────────────────────────────────────────────┐
│ escrowable_type: App\Models\UrgentSalePurchase                  │
│ escrowable_id: 2                                                │
│ client_id: 42 → prestataire_id: 40                             │
├─────────────────────────────────────────────────────────────────┤
│ total_amount: 10.00€                                            │
│ commission_rate: 10.00%                                         │
│ commission_amount: 1.00€                                        │
│ prestataire_amount: 9.00€                                       │
│ remaining_amount: 1.00€                                         │
├─────────────────────────────────────────────────────────────────┤
│ status: partial                                                 │
│ stripe_pi: pi_3SnoWdK7c0bu8iVm1QGt20wI                         │
│ paid_at: 2026-01-11 13:50:36                                   │
│ client_confirmed_at: 2026-01-11 14:23:02                       │
└─────────────────────────────────────────────────────────────────┘
```

### Escrow #2 - UrgentSalePurchase #3
```
┌─────────────────────────────────────────────────────────────────┐
│ escrowable_type: App\Models\UrgentSalePurchase                  │
│ escrowable_id: 3                                                │
│ client_id: 42 → prestataire_id: 29                             │
├─────────────────────────────────────────────────────────────────┤
│ total_amount: 3.00€                                             │
│ commission_rate: 10.00%                                         │
│ commission_amount: 0.59€                                        │
│ prestataire_amount: 2.41€                                       │
│ remaining_amount: 0.59€                                         │
├─────────────────────────────────────────────────────────────────┤
│ status: partial                                                 │
│ stripe_pi: pi_3SoOdsGxYcZCLnN009o00yCo                         │
│ stripe_transfer: tr_3SoOdsGxYcZCLnN0042kQPBc ✅                 │
│ metadata: escrow_flow=platform_hold, stripe_fee=0.29€           │
└─────────────────────────────────────────────────────────────────┘
```

### Escrow #3 - UrgentSalePurchase #4
```
┌─────────────────────────────────────────────────────────────────┐
│ escrowable_type: App\Models\UrgentSalePurchase                  │
│ escrowable_id: 4                                                │
│ client_id: 42 → prestataire_id: 29                             │
├─────────────────────────────────────────────────────────────────┤
│ total_amount: 3.00€                                             │
│ commission_rate: 5.00% (réduit!)                                │
│ commission_amount: 0.59€                                        │
│ prestataire_amount: 2.41€                                       │
├─────────────────────────────────────────────────────────────────┤
│ status: partial                                                 │
│ stripe_pi: pi_3SoRIBGxYcZCLnN00ke0Fv8y                         │
│ client_confirmed_at: 2026-01-11 15:15:54                       │
└─────────────────────────────────────────────────────────────────┘
```

### Escrow #4 - FoodOrder #82 (RELEASED ✅)
```
┌─────────────────────────────────────────────────────────────────┐
│ escrowable_type: App\Models\FoodOrder                           │
│ escrowable_id: 82                                               │
│ client_id: 47 → prestataire_id: 29                             │
├─────────────────────────────────────────────────────────────────┤
│ total_amount: 6.00€                                             │
│ commission_rate: 0.00% ← Pas de commission!                     │
│ commission_amount: 0.00€                                        │
│ prestataire_amount: 6.00€ ← Tout va au presta                  │
├─────────────────────────────────────────────────────────────────┤
│ status: released ✅                                             │
│ stripe_pi: pi_3SpFBMGxYcZCLnN01TRcFVNA                         │
│ stripe_transfer: tr_3SpFBMGxYcZCLnN01UqkaW2w ✅                 │
│ paid_at: 2026-01-14 13:09:59                                   │
│ released_at: 2026-01-14 13:22:07                               │
│ metadata: type=food_order, fixed_retroactively=true             │
└─────────────────────────────────────────────────────────────────┘

⚠️ PROBLÈME IDENTIFIÉ SUR CET ESCROW:
   Client a payé: 6.00€
   Stripe a prélevé: ~0.33€ (1.4% + 0.25€)
   Admin a reçu: 5.67€
   Admin a transféré: 6.00€ (prestataire_amount)
   PERTE ADMIN: -0.33€ ❌
   
   Ce problème survient quand commission = 0% car le code
   transfère prestataire_amount = total_amount au lieu de
   prestataire_amount = total_amount - stripe_fees
```

### 📊 Résumé des escrows en production

| ID | Type | Montant | Commission | Stripe Fee | Presta reçoit | Status |
|----|------|---------|------------|------------|---------------|--------|
| 1 | UrgentSalePurchase | 10€ | 10% | ~0.39€ | 9.00€ | partial |
| 2 | UrgentSalePurchase | 3€ | 10% | 0.29€ | 2.41€ | partial |
| 3 | UrgentSalePurchase | 3€ | 5% | ~0.29€ | 2.41€ | partial |
| 4 | FoodOrder | 6€ | **0%** | ~0.33€ | **6.00€** ❌ | released |

---

## ⚠️ PROBLÈME CRITIQUE : FLUX FINANCIER QUAND COMMISSION = 0%

### Le flux CORRECT (commission > 0%)

```
CLIENT PAIE 100€
        ↓
STRIPE PRÉLÈVE ~1.65€ (1.4% + 0.25€)
        ↓
ADMIN REÇOIT 98.35€
        ↓
┌─────────────────────────────────┐
│ Commission (ex: 10%) = 10€      │ ← ADMIN GARDE
│ Presta reçoit = 88.35€          │ ← ADMIN TRANSFÈRE
│ TOTAL = 98.35€ ✅               │
└─────────────────────────────────┘
```

### Le flux PROBLÉMATIQUE (commission = 0%)

```
CLIENT PAIE 100€
        ↓
STRIPE PRÉLÈVE ~1.65€ (1.4% + 0.25€)
        ↓
ADMIN REÇOIT 98.35€
        ↓
┌─────────────────────────────────┐
│ Commission (0%) = 0€            │
│ Presta reçoit = 100€ ❌         │ ← CODE TRANSFÈRE total_amount !
│ ADMIN PERD = -1.65€ ❌          │
└─────────────────────────────────┘
```

### Exemple réel FoodOrder #82

```
CLIENT PAIE ........................ 6.00€
STRIPE PRÉLÈVE ..................... 0.33€ (1.4% + 0.25€)
ADMIN REÇOIT ....................... 5.67€

MAIS:
prestataire_amount stocké .......... 6.00€ (total, pas net!)
stripe_transfer effectué ........... 6.00€

RÉSULTAT:
ADMIN A PERDU ...................... -0.33€ sur cette commande
```

### 🔧 FIX NÉCESSAIRE DANS LE CODE

Le problème est dans `EscrowService::createEscrow()` ou dans le code Food.
Quand commission = 0%, il faut quand même déduire les frais Stripe :

```php
// ACTUEL (INCORRECT quand commission = 0%):
$prestataireAmount = $amount - $commissionAmount;
// Si commission = 0%, presta reçoit total_amount = 100%

// CORRECT:
$stripeFees = CommissionService::stripeFeesAmount($amount);
$prestataireAmount = $amount - $stripeFees - $commissionAmount;
// Même si commission = 0%, presta reçoit total - stripe_fees
```

**Observations:**
- 3 escrows UrgentSale en status "partial" (confirmés mais pas fully released)
- 1 escrow FoodOrder "released" avec 0% commission (commission_food était 0 quand passé)
- Les transfers Stripe sont créés lors du release
- **BUG**: Quand commission=0%, l'admin perd les frais Stripe sur chaque transaction !
