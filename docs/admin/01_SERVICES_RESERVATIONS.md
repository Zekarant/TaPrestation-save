# 🛠️ SERVICES & RÉSERVATIONS (Bookings)

> **⚠️ DOCUMENT VÉRIFIÉ AVEC LA BASE DE PRODUCTION (tapresr689) - 14 Janvier 2026**
> **Commission Services en production : 10%**

## Vue d'ensemble

Permet aux prestataires de proposer des prestations et aux clients de réserver avec paiement sécurisé via **Escrow**.

---

## 📊 STATUTS

### Statuts de Réservation (`bookings.status`)

| Statut | Description |
|--------|-------------|
| `pending` | En attente de confirmation prestataire |
| `confirmed` | Confirmée par le prestataire |
| `cancelled` | Annulée |
| `completed` | Terminée |
| `no_show` | Client absent |

### Statuts de Paiement (`bookings.payment_status`)

| Statut | Description |
|--------|-------------|
| `pending` | Aucun paiement effectué |
| `deposit_paid` | Acompte payé |
| `paid` | Intégralement payé |
| `refunded` | Remboursé |

### Statuts Escrow (`escrow_transactions.status`)

| Statut | Description |
|--------|-------------|
| `pending` | Fonds bloqués |
| `held` | Argent capturé |
| `partial` | Partiellement libéré |
| `released` | Libéré au prestataire |
| `refunded` | Remboursé au client |
| `disputed` | Litige en cours |
| `cancelled` | Annulé |

---

## 💳 MODES DE PAIEMENT (`service.payment_requirement`)

| Mode | Description |
|------|-------------|
| `none` | Pas de paiement en ligne requis |
| `deposit` | Acompte requis (X%) |
| `full` | Prépaiement total (100%) |

---

## 🔄 FLUX COMPLET

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 1: SÉLECTION SERVICE                                                  │
│ → BookingController::create($service)                                       │
│ → Affiche créneaux disponibles                                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 2: SOUMISSION RÉSERVATION                                             │
│ → BookingController::store()                                                │
│                                                                             │
│ SI payment_requirement IN ('full', 'deposit'):                              │
│    → Stocke en session → redirect vers bookings.prepayment                  │
│ SINON:                                                                      │
│    → Crée booking (payment_status='pending') → Notifie presta               │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 3: PAIEMENT (si requis)                                               │
│ → PaymentController::createPaymentIntent()                                  │
│                                                                             │
│ Mode ESCROW (actuel):                                                       │
│   → StripeService::createEscrowPaymentIntent()                              │
│   → Fonds capturés sur COMPTE PLATEFORME                                    │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 4: CONFIRMATION PAIEMENT                                              │
│ → PaymentController::confirmPayment()                                       │
│                                                                             │
│ Vérifie: PaymentIntent.status === 'succeeded'                               │
│ → StripeService::recordPayment() → PaymentTransaction                       │
│                                                                             │
│ → EscrowService::createEscrow()                                             │
│   ├─ CommissionService::netAmountForPrestataire()                           │
│   ├─ INSERT escrow_transactions (status='pending')                          │
│   └─ auto_release_at = NOW() + 48h                                          │
│                                                                             │
│ → booking.payment_status = 'paid' ou 'deposit_paid'                         │
│ → Notification prestataire                                                  │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 5: SERVICE RÉALISÉ                                                    │
│ → Prestataire effectue la prestation                                        │
│ → BookingController::complete() → status='completed'                        │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 6: LIBÉRATION FONDS                                                   │
│                                                                             │
│ OPTION A: Client confirme                                                   │
│   → EscrowService::clientConfirm()                                          │
│   → EscrowService::releaseToPrestataire()                                   │
│                                                                             │
│ OPTION B: Auto-release 48h (CRON)                                           │
│   → EscrowService::processExpiredEscrows()                                  │
│   → releaseToPrestataire()                                                  │
│                                                                             │
│ releaseToPrestataire():                                                     │
│   ├─ StripeService::transferToConnectedAccount()                            │
│   ├─ escrow.status = 'released'                                             │
│   ├─ prestataires.balance += montant                                        │
│   └─ INSERT finance_ledger (type='escrow_release')                          │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 💰 CALCUL COMMISSION

### Méthode: `CommissionService::netAmountForPrestataire()`

```php
return [
    'client_pays' => $clientPays,
    'stripe_fees' => $stripeFees,
    'amount_after_stripe' => $amountAfterStripe,
    'platform_commission' => $platformCommission,
    'platform_commission_rate' => self::ratePercent('service'),
    'prestataire_receives' => $prestataireReceives,
];
```

### Exemple: Service 100€ (commission 10%)

```
CLIENT PAIE .......................... 100,00€
STRIPE PRÉLÈVE (~1,4% + 0,25€) .......   1,65€
NET PLATEFORME ....................... 98,35€

COMMISSION ADMIN (10%) ............... 10,00€
PRESTATAIRE REÇOIT ................... 90,00€ ← Transfer Stripe

Admin absorbe frais Stripe: 10€ - 1,65€ = 8,35€ net
```

---

## 🚨 CAS SPÉCIAUX

### Annulation
```php
EscrowService::cancelWithRefund($escrowId, 'client')
→ calculateCancellationRefund() selon délai configuré
```

### Litige "Service non réalisé"
```php
EscrowService::openDispute($escrowId, $userId, 'service_not_provided')
→ Remboursement total automatique
→ escrow.status = 'refunded'
```

### Auto-split litige (7 jours)
```php
EscrowService::processExpiredDisputesAutoSplit()
→ 40% client / 60% vendeur
→ Commission admin = 0%
```

---

## 🗄️ TABLE `bookings`

```sql
id, booking_number, client_id, prestataire_id, service_id, time_slot_id,
start_datetime, end_datetime, status, total_price, deposit_amount,
payment_status, client_notes, prestataire_notes, cancellation_reason,
confirmed_at, cancelled_at, completed_at,
payment_terms_version, payment_terms_accepted_at, payment_terms_ip,
created_at, updated_at
```

---

## 📁 FICHIERS CODE

| Fichier | Rôle |
|---------|------|
| `app/Models/Booking.php` | Modèle |
| `app/Http/Controllers/Client/BookingController.php` | Création réservation |
| `app/Http/Controllers/Client/PaymentController.php` | Paiement + escrow |
| `app/Http/Controllers/Prestataire/BookingController.php` | Gestion presta |
| `app/Services/EscrowService.php` | Blocage/libération |
| `app/Services/CommissionService.php` | Calcul commissions |
