# 📊 COMMISSIONS - RÉCAPITULATIF

> **⚠️ DOCUMENT VÉRIFIÉ AVEC LA BASE DE PRODUCTION (tapresr689) - 14 Janvier 2026**

## Taux par type (table `cache` → `site_settings_v2`)

| Type | Clé BD | **VALEUR PRODUCTION** | Description |
|------|--------|----------------------|-------------|
| Services | `commission_services` | **10%** ✅ | Réservations de prestations |
| Locations | `commission_rentals` | **0%** ⚠️ | Location d'équipements (DÉSACTIVÉ) |
| Ventes Flash | `commission_urgent_sales` | **0%** ⚠️ | Petites annonces (DÉSACTIVÉ) |
| Food | `commission_food` | **2%** | Commandes restauration |

**Notes importantes :**
- Les commissions rentals et urgent_sales sont à **0%** en production
- La commission food est **2%** (pas 15%)
- Escrow commission rate = **20%** (paramètre séparé)

---

## Base de calcul

| Type | Base | Formule |
|------|------|---------|
| Services | Prix total service | `prix × commission%` |
| Locations | Prix location total | `total × commission%` |
| Ventes Flash | Prix de vente | `prix × commission%` |
| **Food** | **SUBTOTAL** (prix plats) | `subtotal × commission%` |

⚠️ **Food** : Commission calculée sur le **subtotal** uniquement, PAS sur les frais de livraison ni frais service client.

---

## CommissionService.php

### Méthode `ratePercent()`

```php
public static function ratePercent(string $type): float
{
    $key = match($type) {
        'service'      => 'commission_services',
        'rental'       => 'commission_rentals',
        'urgent_sale'  => 'commission_urgent_sales',
        'food'         => 'commission_food',
        default        => 'commission_services',
    };
    
    return (float) Setting::get($key, 10);
}
```

### Méthode `netAmountForPrestataire()`

```php
// Retourne un array avec tous les montants calculés
return [
    'client_pays' => $clientPays,
    'stripe_fees' => $stripeFees,
    'amount_after_stripe' => $amountAfterStripe,
    'platform_commission' => $platformCommission,
    'platform_commission_rate' => self::ratePercent($type),
    'prestataire_receives' => $prestataireReceives,
];
```

### Méthode `feeAmount()` (pour Food)

```php
// Utilisé par FoodOrder::processPayouts()
$platformFee = CommissionService::feeAmount($subtotal, 'food');
// Retourne le montant de la commission (pas le %)
```

---

## Tableau comparatif (transaction 100€) - VALEURS PRODUCTION

| Type | Commission | Presta reçoit | Admin garde |
|------|------------|---------------|-------------|
| Services (10%) | 10€ | 90€ | 10€ |
| **Locations (0%)** | **0€** | **100€** | **0€** |
| **Ventes Flash (0%)** | **0€** | **100€** | **0€** |
| Food (2%) | 2€ | 98€ | 2€ |

*Note: Frais Stripe (~1,65€ sur 100€) absorbés par l'admin*

**⚠️ Attention : Avec les taux actuels (0% rentals/urgent_sales), aucun revenu commission sur ces types !**

---

## Vérifier les valeurs actuelles

### Via SQL

```sql
SELECT * FROM settings WHERE `key` LIKE 'commission%';
```

### Via PHP

```php
use App\Models\Setting;

// VALEURS ACTUELLES EN PRODUCTION (14 Jan 2026):
echo "Services: " . Setting::get('commission_services', 10) . "%\n";      // 10%
echo "Rentals: " . Setting::get('commission_rentals', 8) . "%\n";         // 0% !
echo "Urgent Sales: " . Setting::get('commission_urgent_sales', 10) . "%\n"; // 0% !
echo "Food: " . Setting::get('commission_food', 15) . "%\n";              // 2% !
```

---

## Cas particuliers

### ⚠️ PROBLÈME CRITIQUE : Commission à 0%

**ATTENTION !** Si configuré à **0%**, il y a un **BUG** dans le code actuel :

```
FLUX ACTUEL (INCORRECT):
├── Client paie: 100€
├── Stripe prend: 1.65€ (1.4% + 0.25€)
├── Admin reçoit: 98.35€
├── Commission 0%: 0€
├── Presta reçoit: 100€ (code transfère total_amount!)
└── ADMIN PERD: -1.65€ ❌
```

**Exemple réel** : Food order #82
- Client payé: 6€
- Stripe prélevé: ~0.33€
- Admin a reçu: 5.67€
- **Transféré au presta: 6€** (100%)
- **Perte admin: -0.33€**

### 🔍 Localisation du BUG dans le code

**Fichier**: `app/Models/FoodOrder.php` ligne ~248

```php
// CODE ACTUEL (INCORRECT):
$prestatairePayout = round((float) $this->subtotal - (float) $platformFee, 2);
// Si platformFee = 0, alors prestatairePayout = subtotal = 100%
```

```php
// CODE CORRIGÉ:
$stripeFees = \App\Services\CommissionService::stripeFeesAmount((float) $this->subtotal);
$prestatairePayout = round((float) $this->subtotal - (float) $platformFee - $stripeFees, 2);
// Même si platformFee = 0, on déduit les frais Stripe
```

### ✅ Ce qui DEVRAIT se passer (commission 0%)

```
FLUX CORRECT:
├── Client paie: 100€
├── Stripe prend: 1.65€
├── Admin reçoit: 98.35€
├── Commission 0%: 0€ (admin garde 0€)
├── Presta reçoit: 98.35€ (total - stripe fees)
└── ADMIN: ±0€ ✅
```

**Conclusion:** Avec commission à 0%, le presta devrait recevoir `total - frais_stripe`, PAS `total`.

### Paiement espèces (cash)

Pour les paiements **hors ligne** :
- **Aucune commission** via la plateforme
- Prestataire encaisse **100%** en main propre
- Admin ne peut pas prélever de commission
- ✅ Pas de frais Stripe (pas de paiement en ligne)

### Remboursement

En cas de **refund** :
- Commission **non prélevée** (ou rendue si déjà prélevée)
- Admin reçoit **0€**
- Frais Stripe perdus (~1,65€ sur 100€)
