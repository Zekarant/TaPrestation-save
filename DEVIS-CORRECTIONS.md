# Devis -- Audit & Corrections TaPrestation

**De :** [Ton nom]
**Pour :** [Nom du collègue]
**Date :** Mars 2026

---

Salut !

J'ai pris le temps de passer tout ton projet au peigne fin. Y'a pas mal de boulot, je te cache pas. Voici le topo complet de ce que j'ai trouvé et ce que je propose de corriger.

---

## L'audit

J'ai audité tout le projet de A à Z :
- Sécurité (auth, sessions, CSRF, uploads, XSS, injections)
- Système de paiement Stripe (intents, webhooks, escrow, remboursements, commissions)
- Base de données (modèles, migrations, FK, race conditions, transactions)
- Architecture (code mort, doublons, contrôleurs obèses, services, N+1)
- Frontend (templates Blade, JS, accessibilité, fichiers orphelins)

**Résultat :** ~80 problèmes identifiés, dont une dizaine de critiques. Le rapport complet est dans `AUDIT.md`.

Pour résumer en gros : **les paiements booking et food sont complètement cassés**, y'a des failles de sécu (un mec peut libérer les fonds d'un autre), les clés Stripe LIVE sont en clair dans le `.env`, et y'a un endpoint public qui peut wipe toute la base. Le reste c'est du nettoyage, de l'archi et de la robustesse.

---

## Ce que je corrige

### Bloc 1 -- Paiements (le plus urgent)

12 bugs critiques/hauts sur le système de paiement Stripe :

| # | Quoi | Impact |
|---|------|--------|
| 1 | `user_id` manquant dans metadata Stripe (bookings) | **Tous les paiements de réservation sont cassés** |
| 2 | `user_id` manquant dans metadata Stripe (food) | **Tous les paiements food sont cassés** |
| 3 | `$fillable` incomplet sur PaymentTransaction | Les remboursements Stripe ne se reflètent pas en BDD |
| 4 | Race condition double remboursement (EscrowService) | Double remboursement possible sous charge |
| 5 | Faille IDOR escrow (orWhere) | Un user peut confirmer les escrows d'un autre |
| 6 | Race condition panier (lock trop tard) | Double achat + double décrémentation stock |
| 7 | Remboursement partiel marqué comme total | Impossible de faire un 2ème remboursement partiel |
| 8 | `DB::rollBack()` orphelin | Corrompt les transactions d'autres opérations |
| 9 | Refund Stripe avant transaction DB | Incohérence Stripe/BDD si la DB échoue |
| 10 | Webhook sans vérification de montant | Attaque par sous-paiement possible |
| 11 | Bypass abonnement sans Stripe | Abonnement gratuit en POST direct |
| 12 | Double remboursement admin | Pas de vérif des refunds existants |

### Bloc 2 -- Sécurité

| # | Quoi | Impact |
|---|------|--------|
| 13 | Rotation/nettoyage clés `.env` + `.env.prod` | Clés Stripe LIVE, SMTP, Google, API keys exposées en clair |
| 14 | Supprimer ou protéger le seed endpoint démo | N'importe qui peut wipe la BDD prod |
| 15 | Passer `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` | Stack traces visibles + session hijackable |
| 16 | Supprimer le logout GET | Déconnexion forcée via CSRF |
| 17 | Restreindre les types d'upload admin | Upload de `.php`/`.exe` possible |
| 18 | Corriger le CSP (`unsafe-eval`) | Protection XSS affaiblie |
| 19 | Ajouter les rôles manquants sur routes food/tenders | N'importe quel user authentifié peut passer des commandes |
| 20 | Corriger BookingPolicy (`admin` vs `administrateur`) | Les admins sont refusés par la policy |
| 21 | Ajouter du rate limiting sur les endpoints sensibles | Pas de protection contre le spam/brute force |
| 22 | Hijack compte Stripe via email matching | Un user peut rediriger les paiements d'un autre presta |

### Bloc 3 -- Base de données & modèles

| # | Quoi | Impact |
|---|------|--------|
| 23 | `User::hasRole()` qui masque le trait Spatie | Le système de permissions Spatie est complètement bypassé |
| 24 | Merger les `$casts` dupliqués de User.php | `blocked_at`, `is_online` silencieusement non-castés |
| 25 | Corriger `Prestataire::verificationRequest()` (mauvais modèle) | Pointe vers ClientVerificationRequest au lieu de Prestataire |
| 26 | Remplacer `$dates` déprécié (Prestataire.php) | Supprimé en Laravel 12, va casser |
| 27 | Wrapper les opérations multi-tables dans des transactions | Booking confirm/cancel, UrgentSale, EquipmentRental |
| 28 | Ajouter du locking sur les race conditions | Booking numbers, equipment availability, stock |
| 29 | `FoodOrder::processPayouts()` -- transaction manquante | `lockForUpdate` inopérant = double paiement prestataire possible |
| 30 | Ajouter les FK manquantes | bookings, payment_transactions, escrow_transactions, escrow_disputes |
| 31 | Corriger les casts manquants/incorrects | `amount` en float au lieu de decimal:2, dates non castées |
| 32 | Paginer la requête qui charge tous les users | OOM sur base croissante (admin notifications) |

### Bloc 4 -- Nettoyage & architecture

| # | Quoi |
|---|------|
| 33 | Supprimer les 5 contrôleurs dupliqués/backup |
| 34 | Supprimer les 4 scripts PHP orphelins à la racine |
| 35 | Supprimer les 19 vues backup/old |
| 36 | Supprimer les vues orphelines non référencées |
| 37 | Déplacer les fichiers de routes mal placés dans `resources/views/` |
| 38 | Supprimer les contrôleurs orphelins (5 non référencés dans les routes) |
| 39 | Corriger les 19+ catch blocks vides (ajouter du logging) |
| 40 | Corriger les N+1 queries (conversations, agenda, profile delete) |

### Bloc 5 -- Frontend

| # | Quoi |
|---|------|
| 41 | Vérifier/sanitizer les 3 variables `{!! $safe* !!}` (XSS) |
| 42 | Remplacer `addslashes()` par `@js()` dans les onclick |
| 43 | Remplacer `{{ }}` par `@js()` dans le contexte JS (30+ occurrences) |
| 44 | Ajouter le `@csrf` manquant (client/dashboard) |
| 45 | Nettoyer les 30+ `console.log` en production |

---

## Tarif -- au black, pour tout

| Prestation | Tarif |
|-----------|-------|
| Audit complet du site | 350 EUR |
| Bloc 1 -- Corrections paiement (12 bugs) | 450 EUR |
| Bloc 2 -- Corrections sécurité (10 fixes) | 300 EUR |
| Bloc 3 -- Corrections BDD & modèles (10 fixes) | 400 EUR |
| Bloc 4 -- Nettoyage & architecture (8 tâches) | 250 EUR |
| Bloc 5 -- Corrections frontend (5 fixes) | 150 EUR |
| **Total** | **1 900 EUR** |

Cash, pas de facture. On fait ça propre et tranquille.

---

Dis-moi si ça te va, je peux attaquer dès que t'as validé.

A+
