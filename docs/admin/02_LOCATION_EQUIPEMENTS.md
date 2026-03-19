# 📦 LOCATION D'ÉQUIPEMENTS

> **⚠️ DOCUMENT VÉRIFIÉ AVEC LA BASE DE PRODUCTION (tapresr689) - 14 Janvier 2026**
> **Commission Rentals en production : 0% (DÉSACTIVÉ)**

## Vue d'ensemble

Permet aux prestataires de louer du matériel (outils, véhicules, etc.) avec gestion de la **caution** et **escrow**.

---

## 📊 ARCHITECTURE - 3 MODÈLES

### 1. `Equipment` (L'équipement)

**Champs tarification:**
- `price_per_hour`, `price_per_day`, `price_per_week`, `price_per_month`
- `security_deposit` → Montant caution
- `deposit_percentage` → % acompte si paiement partiel

**Champs annulation:**
- `cancellation_hours` → Heures avant annulation gratuite
- `cancellation_refund_percentage` → % remboursé si hors délai

**Champs paiement:**
- `payment_requirement` → `'none'`, `'deposit'`, `'full'`
- `auto_accept_on_deposit` → Acceptation auto après acompte

### 2. `EquipmentRentalRequest` (La demande)

**Statuts:**
| Statut | Description |
|--------|-------------|
| `pending` | En attente réponse presta |
| `accepted` | Acceptée |
| `rejected` | Refusée |
| `cancelled` | Annulée |
| `expired` | 7 jours sans réponse |
| `confirmed` | Confirmée après paiement |

### 3. `EquipmentRental` (La location effective)

**Statuts:**
```
confirmed → in_preparation → ready_for_delivery → delivered → in_use → ready_for_pickup → returned → completed
                                                                                            ↓
                                                                                     cancelled | disputed
```

**Statuts paiement:**
| Statut | Description |
|--------|-------------|
| `pending` | En attente |
| `deposit_paid` | Acompte payé |
| `full_paid` | Payé intégralement |
| `refund_pending` | Remboursement en cours |
| `completed` | Terminé |

---

## 🔄 FLUX COMPLET

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 1: DEMANDE DE LOCATION                                                │
│ → EquipmentRentalRequestController::store()                                 │
│   ├─ Vérification disponibilité: isAvailableForPeriod()                     │
│   ├─ Calcul prix: calculatePrice() selon durée                              │
│   └─ Crée EquipmentRentalRequest (status='pending')                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
                        [payment_requirement?]
                                    ↓
          ┌─────────────────────────┴─────────────────────────┐
          ↓                                                   ↓
┌─────────────────────────┐                     ┌─────────────────────────┐
│ 'deposit' ou 'full'     │                     │ 'none'                  │
│ → Redirect vers paiement│                     │ → Notifie presta        │
│ → Presta notifié APRÈS  │                     │ → Attend réponse        │
└─────────────────────────┘                     └─────────────────────────┘
          ↓                                                   ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 2: PAIEMENT (si requis)                                               │
│ → EquipmentRentalRequestPaymentController::show()                           │
│                                                                             │
│ Montants affichés:                                                          │
│   • Acompte = total_amount × deposit_percentage%                            │
│   • Caution = security_deposit                                              │
│   • Total dû = acompte (ou full) + caution                                  │
│                                                                             │
│ → createIntent() → Stripe PaymentIntent                                     │
│ → confirm()                                                                 │
│   ├─ Crée EquipmentRental (status='confirmed')                              │
│   ├─ EscrowService::createEscrow() avec depositAmount = caution             │
│   └─ Notifie presta                                                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 3: ACCEPTATION (si pas de paiement préalable)                         │
│ → EquipmentRentalRequestController::accept()                                │
│   ├─ Vérification double disponibilité                                      │
│   ├─ Crée EquipmentRental (status='confirmed')                              │
│   └─ Notifie client                                                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 4: CYCLE DE VIE LOCATION                                              │
│                                                                             │
│ confirmed        → Location créée                                           │
│ in_preparation   → Presta prépare l'équipement                              │
│ ready_for_delivery → Prêt à récupérer/livrer                                │
│ delivered        → Remis au client                                          │
│ in_use           → En cours d'utilisation                                   │
│ ready_for_pickup → Client signale fin                                       │
│ returned         → Équipement rendu, inspection                             │
│ completed        → Terminé                                                  │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 5: RETOUR ET INSPECTION                                               │
│ → EquipmentRentalController::markReturned()                                 │
│                                                                             │
│ Inspection:                                                                 │
│   • Photos de l'état                                                        │
│   • Évaluation: excellent/very_good/good/fair                               │
│   • Rapport dommages si nécessaire                                          │
│                                                                             │
│ Calcul frais:                                                               │
│   • late_fee (si retard)                                                    │
│   • damage_fee (si dégâts)                                                  │
│   • cleaning_fee (si nettoyage)                                             │
│                                                                             │
│ Calcul caution:                                                             │
│   • deposit_returned = caution - frais                                      │
│   • deposit_retained = frais retenus                                        │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 6: LIBÉRATION ESCROW                                                  │
│ → EscrowService::returnEquipment($escrowId, $condition, $damagePercent)     │
│                                                                             │
│ SI condition = 'good':                                                      │
│   → 100% caution remboursée au client                                       │
│   → Montant location transféré au presta                                    │
│                                                                             │
│ SI condition = 'damaged':                                                   │
│   → X% caution retenue → va au presta                                       │
│   → Reste caution → remboursé client                                        │
│   → Montant location transféré au presta                                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 💰 GESTION DE LA CAUTION

### Flux caution

```
1. PAIEMENT: Caution bloquée dans escrow (deposit_amount)
2. LOCATION: Caution reste bloquée pendant toute la durée
3. RETOUR:
   - État "good" → 100% remboursé au client
   - État "damaged" → % retenu va au prestataire
4. La caution n'est JAMAIS libérée avant le retour
```

### Méthode: `EscrowService::returnEquipment()`

```php
public function returnEquipment(
    int $escrowId, 
    string $condition,           // 'good', 'damaged', 'partial_damage'
    float $damagePercent = 0,    // % de caution à retenir (0-100)
    ?string $damageDescription = null,
    ?array $photos = null
): array
```

### Calcul retour caution

```php
// Dans EquipmentRental
public function calculateDepositReturn() {
    $depositPaid = $this->deposit_paid ?? $this->deposit_amount;
    $deductions = ($this->damage_cost ?? 0) + ($this->cleaning_fee ?? 0);
    return max(0, $depositPaid - $deductions);
}
```

---

## 💰 EXEMPLE: Location 150€ + Caution 50€

### AVEC COMMISSION 8% (valeur code) vs 0% (production)

```
CLIENT PAIE:
├── Location (5 jours × 30€) ........... 150,00€
├── Caution ............................  50,00€
└── TOTAL .............................. 200,00€

STRIPE PRÉLÈVE (~1,4% + 0,25€) .........   3,05€
NET PLATEFORME ......................... 196,95€

═══ RETOUR BON ÉTAT (si commission 8%) ═══
├── 🏢 Admin (commission 8%) ........... 12,00€
├── 👨‍💼 Presta (location - commission) . 138,00€ ← Transfer
└── 👤 Client (caution remboursée) .....  50,00€ ← Refund

═══ RETOUR BON ÉTAT PRODUCTION (commission 0%) ═══
├── 🏢 Admin (commission 0%) ...........  0,00€
├── 👨‍💼 Presta (100% de la location) ... 150,00€ ← Transfer
└── 👤 Client (caution remboursée) .....  50,00€ ← Refund

═══ RETOUR AVEC DÉGÂTS (30€ - commission 0%) ═══
├── 🏢 Admin (commission 0%) ...........  0,00€
├── 👨‍💼 Presta (location + dédommagement) 180,00€ ← Transfer
└── 👤 Client (caution - dégâts) .......  20,00€ ← Refund partiel
```

**Note: Avec commission_rentals=0%, le prestataire reçoit 100% du prix de location.**

---

## 🚨 RÈGLES ANNULATION

```php
// Dans EscrowService::calculateCancellationRefund()

$cancelHours = $equipment->cancellation_hours ?? 48;

if ($hoursUntilStart < 0) {
    // Location en cours = PAS de remboursement
    $refundPercent = 0;
} elseif ($hoursUntilStart < $cancelHours) {
    // Hors délai = % configuré par presta
    $refundPercent = $equipment->cancellation_refund_percentage ?? 0;
} else {
    // Dans les délais = remboursement total (location + caution)
    $refundPercent = 100;
}
```

---

## 🗄️ TABLES

### `equipment_rental_requests`
```sql
id, request_number, equipment_id, client_id, prestataire_id,
start_date, end_date, start_time, end_time, duration_days, duration_hours,
unit_price, total_amount, security_deposit, delivery_fee, final_amount,
delivery_address, pickup_address, delivery_required, pickup_required,
client_message, prestataire_response, special_requirements,
status, rejection_reason, cancellation_reason,
expires_at, responded_at, confirmed_at,
payment_terms_version, payment_terms_accepted_at, payment_terms_ip,
metadata, source, client_ip, created_at, updated_at
```

### `equipment_rentals`
```sql
id, rental_number, rental_request_id, equipment_id, client_id, prestataire_id,
start_date, end_date, start_time, end_time,
actual_start_datetime, actual_end_datetime,
planned_duration_days, actual_duration_days,
unit_price, base_amount, security_deposit,
delivery_fee, pickup_fee, late_fee, damage_fee, cleaning_fee, additional_fees,
discount_amount, total_amount, final_amount,
deposit_returned, deposit_retained,
delivery_address, pickup_address,
delivered_at, picked_up_at, delivered_by, picked_up_by,
status, payment_status,
equipment_condition_delivered, equipment_condition_returned,
damage_report, damage_photos,
late_return, late_days, late_hours,
client_signature_delivery, client_signature_pickup,
prestataire_signature_delivery, prestataire_signature_pickup,
cancellation_reason, cancelled_at, cancelled_by, completed_at,
created_at, updated_at
```

---

## 📁 FICHIERS CODE

| Fichier | Rôle |
|---------|------|
| `app/Models/Equipment.php` | Modèle équipement |
| `app/Models/EquipmentRentalRequest.php` | Modèle demande |
| `app/Models/EquipmentRental.php` | Modèle location |
| `app/Http/Controllers/Client/EquipmentRentalRequestController.php` | Demande client |
| `app/Http/Controllers/Client/EquipmentRentalRequestPaymentController.php` | Paiement |
| `app/Http/Controllers/Prestataire/EquipmentRentalController.php` | Gestion presta |
| `app/Services/EscrowService.php` | returnEquipment() |
