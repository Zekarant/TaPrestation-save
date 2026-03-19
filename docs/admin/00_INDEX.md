# 📚 DOCUMENTATION ADMIN - TAPRESTATION

> **⚠️ DOCUMENT VÉRIFIÉ AVEC LA BASE DE PRODUCTION (tapresr689) - 14 Janvier 2026**

## Index des Flux de Paiement

Cette documentation détaille les **flux exacts** de la plateforme TaPrestation basés sur le code source réel ET la base de données de production.

---

## 📁 Fichiers de documentation

| Fichier | Description |
|---------|-------------|
| [01_SERVICES_RESERVATIONS.md](01_SERVICES_RESERVATIONS.md) | Réservations de services (coiffure, massage, etc.) |
| [02_LOCATION_EQUIPEMENTS.md](02_LOCATION_EQUIPEMENTS.md) | Location d'équipements avec caution |
| [03_VENTES_FLASH.md](03_VENTES_FLASH.md) | Ventes urgentes / petites annonces |
| [04_COMMANDES_FOOD.md](04_COMMANDES_FOOD.md) | Commandes restauration avec code de retrait |
| [05_COMMISSIONS.md](05_COMMISSIONS.md) | Récapitulatif des commissions |
| [06_ESCROW_SYSTEM.md](06_ESCROW_SYSTEM.md) | Système Escrow détaillé |

---

## 🎯 Commission par type - VALEURS PRODUCTION

| Type | Clé BD | **Valeur Production** | Notes |
|------|--------|----------------------|-------|
| Services | `commission_services` | **10%** ✅ | Actif |
| Locations | `commission_rentals` | **0%** ⚠️ | Désactivé |
| Ventes Flash | `commission_urgent_sales` | **0%** ⚠️ | Désactivé |
| Food | `commission_food` | **2%** | Actif |

### Autres paramètres en production
```
escrow_enabled = 1 (activé)
escrow_auto_release_hours = 48
escrow_commission_rate = 20 (défaut)
escrow_min_amount = 5€
deposit_default_percentage = 30%
deposit_minimum_amount = 10€
caution_default_amount = 100€
```

---

## 🔐 Principe Escrow

```
CLIENT PAIE → Fonds BLOQUÉS sur compte PLATEFORME → PRESTATION RÉALISÉE → Fonds LIBÉRÉS au prestataire
```

**Auto-release** : 48h après confirmation/livraison si pas de litige.

---

## 📁 Fichiers Code Principaux

| Fichier | Rôle |
|---------|------|
| `app/Services/EscrowService.php` | Gestion complète escrow |
| `app/Services/CommissionService.php` | Calcul des commissions |
| `app/Services/StripePaymentService.php` | Intégration Stripe |
| `app/Models/EscrowTransaction.php` | Modèle escrow |
