<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\FoodOrder;
use App\Models\UrgentSale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Support\TableExistenceCache;
use Illuminate\Support\Str;

class GuidanceAssistantService
{
    public function chat(User $user, string $message, ?string $previousResponseId = null, ?string $currentPath = null): array
    {
        $role = $this->resolveRole($user);
        $context = $this->buildContext($user, $role);

        return $this->buildLocalResponse($role, $message, $context, $previousResponseId, $currentPath);
    }

    public function initialPayload(User $user): array
    {
        $role = $this->resolveRole($user);
        $context = $this->buildContext($user, $role);
        $welcome = $role === 'prestataire'
            ? 'Posez une question simple: commande, paiement, message.'
            : 'Posez une question simple: commande, paiement, vendeur.';

        return [
            'role' => $role,
            'welcome_message' => $welcome,
            'starter_actions' => [],
            'starter_questions' => array_values(array_slice($this->starterQuestions($role, $context), 0, 2)),
        ];
    }

    private function buildContext(User $user, string $role): array
    {
        $actions = [];
        $starterActionIds = [];
        $snapshot = [];
        $facts = [
            'subscription_inactive' => false,
            'food_order' => null,
            'booking' => null,
            'urgent_sale' => null,
            'escrow' => null,
        ];

        if ($role === 'prestataire') {
            if (!$user->hasActiveSubscription()) {
                $facts['subscription_inactive'] = true;

                $this->pushAction($actions, $starterActionIds, 'subscription_payment', 'Activer mon abonnement', $this->safeRoute('prestataire.subscription.payment'), 'primary', 'Activer ou reprendre l abonnement prestataire');
                $this->pushAction($actions, $starterActionIds, 'messages', 'Messagerie', $this->safeRoute('messaging.index'), 'secondary', 'Ouvrir vos conversations');
                $this->pushAction($actions, $starterActionIds, 'contact', 'Contact', $this->safeRoute('contact'), 'ghost', 'Contacter la plateforme');

                return [
                    'actions' => $actions,
                    'starter_actions' => array_values($actions),
                    'snapshot' => ['Abonnement prestataire inactif ou non finalise.'],
                    'facts' => $facts,
                ];
            }

            $this->pushAction($actions, $starterActionIds, 'dashboard', 'Tableau de bord', $this->safeRoute('prestataire.dashboard'), 'primary', 'Vue d ensemble prestataire');
            $this->pushAction($actions, $starterActionIds, 'food_orders', 'Commandes food', $this->safeRoute('prestataire.food-orders.index'), 'primary', 'Voir et traiter les commandes food');
            $this->pushAction($actions, $starterActionIds, 'urgent_sales', 'Ventes urgentes', $this->safeRoute('prestataire.urgent-sales.index'), 'secondary', 'Gerer vos annonces de vente urgente');
            $this->pushAction($actions, $starterActionIds, 'reservations', 'Reservations', $this->safeRoute('prestataire.reservations.index'), 'secondary', 'Suivre les reservations de ventes urgentes');
            $this->pushAction($actions, $starterActionIds, 'payments', 'Paiements', $this->safeRoute('prestataire.payments.index'), 'secondary', 'Voir les versements, revenus et retraits');
            $this->pushAction($actions, $starterActionIds, 'escrow_index', 'Paiements securises', $this->safeRoute('prestataire.escrow.index'), 'secondary', 'Suivre les transactions escrow et expeditions');
            $this->pushAction($actions, $starterActionIds, 'equipment', 'Equipements', $this->safeRoute('prestataire.equipment.index'), 'secondary', 'Gerer stock et disponibilites equipement');
            $this->pushAction($actions, $starterActionIds, 'messages', 'Messagerie', $this->safeRoute('messaging.index'), 'ghost', 'Ouvrir vos conversations');
            $this->pushAction($actions, $starterActionIds, 'help', 'Aide', $this->safeRoute('prestataire.help.index'), 'ghost', 'Consulter le centre d aide prestataire');
            $this->pushAction($actions, $starterActionIds, 'contact', 'Contact', $this->safeRoute('contact'), 'ghost', 'Contacter la plateforme');

            if ($user->prestataire) {
                if (TableExistenceCache::has('food_orders')) {
                    $order = FoodOrder::query()
                        ->where('prestataire_id', $user->prestataire->id)
                        ->latest('created_at')
                        ->first(['id', 'order_number', 'status']);

                    if ($order) {
                        $statusLabel = $this->labelFoodOrderStatus((string) $order->status);

                        $this->pushAction($actions, $starterActionIds, 'latest_food_order', 'Derniere commande', $this->safeRoute('prestataire.food-orders.show', $order->id), 'primary', 'Ouvrir la derniere commande food');
                        $snapshot[] = sprintf('Derniere commande food %s : %s.', $order->order_number ?: '#' . $order->id, $statusLabel);
                        $facts['food_order'] = [
                            'id' => $order->id,
                            'number' => $order->order_number ?: '#' . $order->id,
                            'status' => (string) $order->status,
                            'status_label' => $statusLabel,
                        ];
                    }
                }

                if (TableExistenceCache::has('bookings')) {
                    $booking = Booking::query()
                        ->where('prestataire_id', $user->prestataire->id)
                        ->latest('created_at')
                        ->first(['id', 'booking_number', 'status']);

                    if ($booking) {
                        $statusLabel = $this->labelBookingStatus((string) $booking->status);

                        $this->pushAction($actions, $starterActionIds, 'latest_booking', 'Derniere reservation', $this->safeRoute('prestataire.bookings.show', $booking->id), 'secondary', 'Ouvrir la derniere reservation');
                        $snapshot[] = sprintf('Derniere reservation %s : %s.', $booking->booking_number ?: '#' . $booking->id, $statusLabel);
                        $facts['booking'] = [
                            'id' => $booking->id,
                            'number' => $booking->booking_number ?: '#' . $booking->id,
                            'status' => (string) $booking->status,
                            'status_label' => $statusLabel,
                        ];
                    }
                }

                if (TableExistenceCache::has('urgent_sales')) {
                    $urgentSale = UrgentSale::query()
                        ->where('prestataire_id', $user->prestataire->id)
                        ->latest('created_at')
                        ->first(['id', 'title', 'status']);

                    if ($urgentSale) {
                        $statusLabel = $this->labelUrgentSaleStatus((string) $urgentSale->status);

                        $this->pushAction($actions, $starterActionIds, 'latest_urgent_sale', 'Derniere annonce', $this->safeRoute('prestataire.urgent-sales.show', $urgentSale->id), 'secondary', 'Ouvrir votre derniere annonce');
                        $snapshot[] = sprintf('Derniere vente urgente "%s" : %s.', Str::limit((string) $urgentSale->title, 50), $statusLabel);
                        $facts['urgent_sale'] = [
                            'id' => $urgentSale->id,
                            'title' => (string) $urgentSale->title,
                            'status' => (string) $urgentSale->status,
                            'status_label' => $statusLabel,
                        ];
                    }
                }
            }

            if (TableExistenceCache::has('escrow_transactions')) {
                $escrow = DB::table('escrow_transactions')
                    ->where('prestataire_id', $user->id)
                    ->orderByDesc('created_at')
                    ->first(['id', 'status']);

                if ($escrow) {
                    $statusLabel = $this->labelEscrowStatus((string) $escrow->status);

                    $this->pushAction($actions, $starterActionIds, 'latest_escrow', 'Dernier escrow', $this->safeRoute('prestataire.escrow.show', $escrow->id), 'secondary', 'Ouvrir la derniere transaction securisee');
                    $snapshot[] = sprintf('Dernier escrow #%d : %s.', $escrow->id, $statusLabel);
                    $facts['escrow'] = [
                        'id' => $escrow->id,
                        'status' => (string) $escrow->status,
                        'status_label' => $statusLabel,
                    ];
                }
            }
        } else {
            $this->pushAction($actions, $starterActionIds, 'dashboard', 'Tableau de bord', $this->safeRoute('client.dashboard'), 'primary', 'Vue d ensemble client');
            $this->pushAction($actions, $starterActionIds, 'food_orders', 'Mes commandes food', $this->safeRoute('food.orders'), 'primary', 'Voir toutes vos commandes food');
            $this->pushAction($actions, $starterActionIds, 'bookings', 'Mes reservations', $this->safeRoute('client.bookings.index'), 'secondary', 'Voir vos reservations de services');
            $this->pushAction($actions, $starterActionIds, 'escrow_index', 'Paiements securises', $this->safeRoute('client.escrow.index'), 'secondary', 'Suivre vos transactions escrow');
            $this->pushAction($actions, $starterActionIds, 'payments', 'Paiements', $this->safeRoute('client.payments.index'), 'secondary', 'Voir vos paiements et transactions');
            $this->pushAction($actions, $starterActionIds, 'invoices', 'Factures', $this->safeRoute('client.invoices.index'), 'secondary', 'Retrouver vos factures');
            $this->pushAction($actions, $starterActionIds, 'delivery_orders', 'Livraisons', $this->safeRoute('client.delivery.orders'), 'secondary', 'Voir vos livraisons');
            $this->pushAction($actions, $starterActionIds, 'messages', 'Messagerie', $this->safeRoute('messaging.index'), 'ghost', 'Contacter un vendeur ou un prestataire');
            $this->pushAction($actions, $starterActionIds, 'help', 'Aide', $this->safeRoute('client.help.index'), 'ghost', 'Consulter le centre d aide client');
            $this->pushAction($actions, $starterActionIds, 'contact', 'Contact', $this->safeRoute('contact'), 'ghost', 'Contacter la plateforme');
            $this->pushAction($actions, $starterActionIds, 'my_urgent_sales', 'Mes annonces', $this->safeRoute('client.my-urgent-sales.index'), 'ghost', 'Gerer vos annonces client');

            if (TableExistenceCache::has('food_orders')) {
                $order = FoodOrder::query()
                    ->where('client_id', $user->id)
                    ->latest('created_at')
                    ->first(['id', 'order_number', 'status']);

                if ($order) {
                    $statusLabel = $this->labelFoodOrderStatus((string) $order->status);

                    $this->pushAction($actions, $starterActionIds, 'latest_food_order', 'Derniere commande', $this->safeRoute('food.orders.show', $order->id), 'primary', 'Ouvrir votre derniere commande food');
                    $this->pushAction($actions, $starterActionIds, 'latest_food_tracking', 'Suivre ma commande', $this->safeRoute('food.orders.track', $order->id), 'primary', 'Suivre la livraison de votre derniere commande food');
                    $snapshot[] = sprintf('Derniere commande food %s : %s.', $order->order_number ?: '#' . $order->id, $statusLabel);
                    $facts['food_order'] = [
                        'id' => $order->id,
                        'number' => $order->order_number ?: '#' . $order->id,
                        'status' => (string) $order->status,
                        'status_label' => $statusLabel,
                    ];
                }
            }

            if ($user->client && TableExistenceCache::has('bookings')) {
                $booking = Booking::query()
                    ->where('client_id', $user->client->id)
                    ->latest('created_at')
                    ->first(['id', 'booking_number', 'status']);

                if ($booking) {
                    $statusLabel = $this->labelBookingStatus((string) $booking->status);

                    $this->pushAction($actions, $starterActionIds, 'latest_booking', 'Derniere reservation', $this->safeRoute('client.bookings.show', $booking->id), 'secondary', 'Ouvrir votre derniere reservation');
                    $snapshot[] = sprintf('Derniere reservation %s : %s.', $booking->booking_number ?: '#' . $booking->id, $statusLabel);
                    $facts['booking'] = [
                        'id' => $booking->id,
                        'number' => $booking->booking_number ?: '#' . $booking->id,
                        'status' => (string) $booking->status,
                        'status_label' => $statusLabel,
                    ];
                }
            }

            if (TableExistenceCache::has('escrow_transactions')) {
                $escrow = DB::table('escrow_transactions')
                    ->where('client_id', $user->id)
                    ->orderByDesc('created_at')
                    ->first(['id', 'status']);

                if ($escrow) {
                    $statusLabel = $this->labelEscrowStatus((string) $escrow->status);

                    $this->pushAction($actions, $starterActionIds, 'latest_escrow', 'Dernier paiement securise', $this->safeRoute('client.escrow.show', $escrow->id), 'secondary', 'Ouvrir votre derniere transaction securisee');
                    $snapshot[] = sprintf('Dernier escrow #%d : %s.', $escrow->id, $statusLabel);
                    $facts['escrow'] = [
                        'id' => $escrow->id,
                        'status' => (string) $escrow->status,
                        'status_label' => $statusLabel,
                    ];
                }
            }

            if (TableExistenceCache::has('urgent_sales')) {
                $urgentSale = UrgentSale::query()
                    ->where('user_id', $user->id)
                    ->latest('created_at')
                    ->first(['id', 'title', 'status']);

                if ($urgentSale) {
                    $statusLabel = $this->labelUrgentSaleStatus((string) $urgentSale->status);

                    $this->pushAction($actions, $starterActionIds, 'latest_my_urgent_sale', 'Derniere annonce', $this->safeRoute('client.my-urgent-sales.show', $urgentSale->id), 'ghost', 'Ouvrir votre derniere annonce client');
                    $snapshot[] = sprintf('Derniere annonce "%s" : %s.', Str::limit((string) $urgentSale->title, 50), $statusLabel);
                    $facts['urgent_sale'] = [
                        'id' => $urgentSale->id,
                        'title' => (string) $urgentSale->title,
                        'status' => (string) $urgentSale->status,
                        'status_label' => $statusLabel,
                    ];
                }
            }
        }

        if ($snapshot === []) {
            $snapshot[] = 'Aucun element recent significatif n a ete trouve.';
        }

        return [
            'actions' => $actions,
            'starter_actions' => array_values(array_filter(array_map(
                static fn ($actionId) => $actions[$actionId] ?? null,
                $starterActionIds
            ))),
            'snapshot' => $snapshot,
            'facts' => $facts,
        ];
    }

    private function buildLocalResponse(string $role, string $message, array $context, ?string $previousIntent = null, ?string $currentPath = null): array
    {
        $normalized = Str::lower(Str::ascii($message));
        $intentData = $this->resolveIntent($normalized, $role, $previousIntent);
        $intent = $intentData['intent'];

        $payload = match ($intent) {
            'greeting' => $this->responseForGreeting($role, $context, $currentPath),
            'order_status' => $this->responseForOrder($role, $context, $currentPath),
            'escrow' => $this->responseForEscrow($role, $context, $currentPath),
            'messaging' => $this->responseForMessaging($role, $context, $currentPath),
            'issue' => $this->responseForIssue($role, $context, $currentPath),
            'invoice' => $this->responseForInvoice($role, $context, $currentPath),
            'booking' => $this->responseForBooking($role, $context, $currentPath),
            'catalog' => $this->responseForCatalog($context, $currentPath),
            'my_sales' => $this->responseForMySales($context, $currentPath),
            'earnings' => $this->responseForEarnings($context, $currentPath),
            'dashboard' => $this->responseForDashboard($context, $currentPath),
            'subscription' => $this->responseForSubscription($context),
            'support' => $this->responseForSupport($role, $context, $currentPath),
            'thanks' => $this->responseForThanks($role, $context),
            'clarify' => $this->responseForClarification($role, $context, $intentData['candidates'] ?? []),
            default => $this->responseForGeneral($role, $context, $currentPath),
        };

        if (in_array($intent, ['general', 'clarify', 'support'], true)) {
            $sitePayload = $this->siteNavigationResponse($role, $normalized);

            if ($sitePayload) {
                $payload = $sitePayload;
            }
        }

        return [
            'message' => $payload['message'],
            'actions' => array_values(array_slice($payload['actions'], 0, 1)),
            'suggestions' => $payload['actions'] === []
                ? array_values(array_slice($payload['suggestions'], 0, 3))
                : [],
            'previous_response_id' => in_array($intent, ['greeting', 'thanks', 'general', 'clarify'], true) ? null : $intent,
            'source' => 'local',
        ];
    }

    private function resolveIntent(string $normalized, string $role, ?string $previousIntent): array
    {
        $text = trim((string) preg_replace('/\s+/', ' ', $normalized));
        $scores = [];

        foreach ($this->intentDefinitions($role) as $intent => $definition) {
            $score = 0;

            foreach ($definition['phrases'] ?? [] as $phrase => $weight) {
                if ($this->containsTerm($text, (string) $phrase)) {
                    $score += (int) $weight;
                }
            }

            foreach ($definition['keywords'] ?? [] as $keyword => $weight) {
                if ($this->containsTerm($text, (string) $keyword)) {
                    $score += (int) $weight;
                }
            }

            if ($score > 0) {
                $scores[$intent] = $score;
            }
        }

        arsort($scores);

        $rankedIntents = array_keys($scores);
        $bestIntent = $rankedIntents[0] ?? null;
        $bestScore = $bestIntent ? (int) $scores[$bestIntent] : 0;
        $secondIntent = $rankedIntents[1] ?? null;
        $secondScore = $secondIntent ? (int) $scores[$secondIntent] : 0;

        if (!$bestIntent) {
            if ($previousIntent && $this->looksLikeFollowUp($text)) {
                return [
                    'intent' => $previousIntent,
                    'candidates' => [$previousIntent],
                ];
            }

            return [
                'intent' => 'clarify',
                'candidates' => $this->defaultClarificationIntents($role),
            ];
        }

        $minimumScore = $secondScore > 0 ? 3 : 2;

        if ($bestScore < $minimumScore || ($secondScore > 0 && ($bestScore - $secondScore) <= 1)) {
            return [
                'intent' => 'clarify',
                'candidates' => array_slice($rankedIntents, 0, 3),
            ];
        }

        return [
            'intent' => $bestIntent,
            'candidates' => [$bestIntent],
        ];
    }

    private function responseForGreeting(string $role, array $context, ?string $currentPath): array
    {
        return [
            'message' => $this->formatReply([
                'Ecrivez votre besoin simplement.',
                $role === 'prestataire'
                    ? 'Exemples : "ou voir ma commande ?" ou "quand serai-je paye ?".'
                    : 'Exemples : "ou en est ma commande ?" ou "comment contacter le vendeur ?".',
            ]),
            'actions' => [],
            'suggestions' => $this->starterQuestions($role, $context),
        ];
    }

    private function responseForOrder(string $role, array $context, ?string $currentPath): array
    {
        $order = $context['facts']['food_order'] ?? null;
        $booking = $context['facts']['booking'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['orders', 'delivery']),
                $order
                    ? sprintf('Votre derniere commande food %s est actuellement %s.', $order['number'], $order['status_label'])
                    : ($role === 'prestataire'
                        ? 'Je n ai pas trouve de commande food recente sur votre espace prestataire.'
                        : 'Je n ai pas trouve de commande food recente sur votre compte.'),
                $booking
                    ? sprintf('Si vous parlez d une reservation, la derniere %s est %s.', $booking['number'], $booking['status_label'])
                    : null,
                $role === 'prestataire'
                    ? 'Ouvrez la commande pour la traiter.'
                    : 'Ouvrez le suivi pour voir le detail.',
            ]),
            'actions' => $this->takeActions($context['actions'], $role === 'prestataire'
                ? ['latest_food_order', 'food_orders', 'latest_booking', 'messages']
                : ['latest_food_tracking', 'latest_food_order', 'food_orders', 'messages']),
            'suggestions' => $role === 'prestataire'
                ? ['Traiter une commande', 'Voir mes reservations']
                : ['Je n ai rien recu', 'Contacter le vendeur'],
        ];
    }

    private function responseForEscrow(string $role, array $context, ?string $currentPath): array
    {
        $escrow = $context['facts']['escrow'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['escrow', 'payments']),
                $escrow
                    ? sprintf('Votre dernier paiement securise #%d est %s.', $escrow['id'], $escrow['status_label'])
                    : ($role === 'prestataire'
                        ? 'Je n ai pas trouve de transaction escrow recente cote prestataire.'
                        : 'Je n ai pas trouve de paiement securise recent sur votre compte.'),
                $this->escrowHelpText($escrow, $role),
                $role === 'prestataire'
                    ? 'Ouvrez aussi Paiements si vous attendez un versement.'
                    : 'Ouvrez le detail du paiement pour voir l etape exacte.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['latest_escrow', 'escrow_index', 'payments', 'help']),
            'suggestions' => $role === 'prestataire'
                ? ['Quand serai-je paye ?', 'Voir mes paiements']
                : ['Quand l argent sera libere ?', 'Je veux un remboursement'],
        ];
    }

    private function responseForMessaging(string $role, array $context, ?string $currentPath): array
    {
        $order = $context['facts']['food_order'] ?? null;
        $escrow = $context['facts']['escrow'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['messages']),
                'Pour parler avec un vendeur, un client ou un prestataire, ouvrez la messagerie.',
                $order
                    ? sprintf('Si cela concerne la commande %s, ouvrez aussi la commande.', $order['number'])
                    : null,
                $escrow
                    ? sprintf('Si cela concerne le paiement securise #%d, ouvrez aussi ce dossier.', $escrow['id'])
                    : null,
            ]),
            'actions' => $this->takeActions($context['actions'], ['messages', 'latest_food_order', 'latest_escrow', 'help']),
            'suggestions' => $role === 'prestataire'
                ? ['Ouvrir ma messagerie', 'Voir ma commande']
                : ['Ouvrir la messagerie', 'Voir ma commande'],
        ];
    }

    private function responseForIssue(string $role, array $context, ?string $currentPath): array
    {
        $order = $context['facts']['food_order'] ?? null;
        $escrow = $context['facts']['escrow'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['orders', 'escrow', 'help']),
                'Ouvrez d abord le dossier concerne.',
                $order
                    ? sprintf('Si le souci concerne la commande %s, ouvrez-la puis ajoutez vos preuves.', $order['number'])
                    : null,
                $escrow
                    ? sprintf('Si le probleme touche le paiement securise #%d, ouvrez-le pour signaler ou suivre le litige.', $escrow['id'])
                    : 'Si le blocage touche le paiement, ouvrez Paiements securises.',
            ]),
            'actions' => $this->takeActions($context['actions'], $role === 'prestataire'
                ? ['latest_escrow', 'messages', 'help', 'contact']
                : ['latest_escrow', 'latest_food_order', 'messages', 'help']),
            'suggestions' => $role === 'prestataire'
                ? ['Paiement bloque', 'Le client ne repond pas']
                : ['Je n ai rien recu', 'Je veux un remboursement'],
        ];
    }

    private function responseForInvoice(string $role, array $context, ?string $currentPath): array
    {
        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['invoices', 'payments']),
                $role === 'prestataire'
                    ? 'Vos justificatifs se trouvent surtout dans Paiements.'
                    : 'Vos factures et recus se trouvent dans Factures.',
                'Ouvrez la bonne page pour telecharger le document.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['invoices', 'payments', 'dashboard']),
            'suggestions' => $role === 'prestataire'
                ? ['Voir mes paiements', 'Contacter le support']
                : ['Telecharger ma facture', 'Voir mes paiements'],
        ];
    }

    private function responseForBooking(string $role, array $context, ?string $currentPath): array
    {
        $booking = $context['facts']['booking'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['bookings']),
                $booking
                    ? sprintf('Votre derniere reservation %s est %s.', $booking['number'], $booking['status_label'])
                    : ($role === 'prestataire'
                        ? 'Je n ai pas trouve de reservation recente cote prestataire.'
                        : 'Je n ai pas trouve de reservation recente sur votre compte.'),
                $role === 'prestataire'
                    ? 'Ouvrez la reservation pour voir le detail.'
                    : 'Ouvrez la reservation pour voir les prochaines etapes.',
            ]),
            'actions' => $this->takeActions($context['actions'], $role === 'prestataire'
                ? ['latest_booking', 'reservations', 'messages', 'dashboard']
                : ['latest_booking', 'bookings', 'messages', 'dashboard']),
            'suggestions' => $role === 'prestataire'
                ? ['Voir mes reservations', 'Contacter le client']
                : ['Voir ma reservation', 'Contacter le prestataire'],
        ];
    }

    private function responseForCatalog(array $context, ?string $currentPath): array
    {
        $urgentSale = $context['facts']['urgent_sale'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['urgent_sales']),
                $urgentSale
                    ? sprintf('Votre derniere vente urgente "%s" est %s.', Str::limit($urgentSale['title'], 60), $urgentSale['status_label'])
                    : 'Je n ai pas trouve d annonce recente sur votre espace prestataire.',
                'Ouvrez Ventes urgentes ou Equipements pour la gerer.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['latest_urgent_sale', 'urgent_sales', 'reservations', 'equipment']),
            'suggestions' => ['Gerer mon stock', 'Voir mes reservations'],
        ];
    }

    private function responseForMySales(array $context, ?string $currentPath): array
    {
        $urgentSale = $context['facts']['urgent_sale'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['urgent_sales']),
                $urgentSale
                    ? sprintf('Votre derniere annonce "%s" est %s.', Str::limit($urgentSale['title'], 60), $urgentSale['status_label'])
                    : 'Je n ai pas trouve d annonce recente sur votre espace client.',
                'Ouvrez Mes annonces pour voir le detail ou modifier l annonce.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['latest_my_urgent_sale', 'my_urgent_sales', 'dashboard']),
            'suggestions' => ['Ouvrir mes annonces', 'Modifier mon annonce'],
        ];
    }

    private function responseForEarnings(array $context, ?string $currentPath): array
    {
        $escrow = $context['facts']['escrow'] ?? null;

        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['payments', 'escrow']),
                $escrow
                    ? sprintf('Votre dernier dossier securise #%d est %s.', $escrow['id'], $escrow['status_label'])
                    : 'Je n ai pas trouve de dossier securise recent a relier a un versement.',
                'Pour vos gains, versements et retraits, ouvrez Paiements.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['payments', 'latest_escrow', 'dashboard', 'help']),
            'suggestions' => ['Quand serai-je paye ?', 'Paiement bloque'],
        ];
    }

    private function responseForDashboard(array $context, ?string $currentPath): array
    {
        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['dashboard']),
                'Le tableau de bord est le bon point de depart.',
                $this->briefAccountSummary($context),
                'Ensuite, dites-moi simplement : commandes, paiements ou ventes urgentes.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['dashboard', 'food_orders', 'urgent_sales', 'payments']),
            'suggestions' => ['Voir mes commandes', 'Voir mes paiements'],
        ];
    }

    private function responseForSubscription(array $context): array
    {
        $inactive = (bool) ($context['facts']['subscription_inactive'] ?? false);

        return [
            'message' => $this->formatReply([
                $inactive
                    ? 'Votre abonnement prestataire semble inactif ou non finalise.'
                    : 'Si l acces a vos outils est bloque, repassez par la page d abonnement prestataire.',
                'Ouvrez la page d abonnement. Si besoin, contactez la plateforme.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['subscription_payment', 'contact', 'messages']),
            'suggestions' => ['Activer mon abonnement', 'Contacter la plateforme'],
        ];
    }

    private function responseForSupport(string $role, array $context, ?string $currentPath): array
    {
        return [
            'message' => $this->formatReply([
                $this->currentAreaHint($currentPath, ['help']),
                $role === 'prestataire'
                    ? 'Pour un blocage ou un litige, ouvrez l aide prestataire.'
                    : 'Pour un blocage ou une question compte, ouvrez l aide client.',
                'Si cela ne suffit pas, utilisez le contact plateforme.',
            ]),
            'actions' => $this->takeActions($context['actions'], ['help', 'contact', 'messages']),
            'suggestions' => $role === 'prestataire'
                ? ['Ouvrir l aide', 'Contacter la plateforme']
                : ['Ouvrir l aide', 'Je n ai rien recu'],
        ];
    }

    private function responseForThanks(string $role, array $context): array
    {
        return [
            'message' => $this->formatReply([
                'Je reste disponible.',
                $role === 'prestataire'
                    ? 'Vous pouvez demander : commande, paiement, message ou annonce.'
                    : 'Vous pouvez demander : commande, paiement, message ou facture.',
            ]),
            'actions' => array_slice($context['starter_actions'], 0, 2),
            'suggestions' => $this->starterQuestions($role, $context),
        ];
    }

    private function responseForClarification(string $role, array $context, array $candidates): array
    {
        $labels = array_values(array_filter(array_map(
            fn ($intent) => $this->clarificationLabel((string) $intent, $role),
            $candidates
        )));

        $message = $labels !== []
            ? 'Je ne suis pas certain du sujet. Dites-moi si cela concerne ' . $this->joinLabels($labels) . '.'
            : ($role === 'prestataire'
                ? 'Je n ai pas assez d elements. Dites-moi si cela concerne une commande, un paiement, un message ou une annonce.'
                : 'Je n ai pas assez d elements. Dites-moi si cela concerne une commande, un paiement, un message ou une reservation.');

        return [
            'message' => $message,
            'actions' => [],
            'suggestions' => $this->clarificationSuggestions($role, $context, $candidates),
        ];
    }

    private function responseForGeneral(string $role, array $context, ?string $currentPath): array
    {
        return [
            'message' => $this->formatReply([
                'Je peux vous aider si vous me dites le sujet exact.',
                $role === 'prestataire'
                    ? 'Exemples : "ou voir ma commande ?" ou "quand serai-je paye ?".'
                    : 'Exemples : "ou en est ma commande ?" ou "je veux contacter le vendeur".',
            ]),
            'actions' => [],
            'suggestions' => $this->starterQuestions($role, $context),
        ];
    }

    private function starterQuestions(string $role, array $context): array
    {
        $questions = [];

        if ($role === 'prestataire') {
            if ($context['facts']['food_order']) {
                $questions[] = 'Ou en est ma commande ?';
            }

            if ($context['facts']['escrow']) {
                $questions[] = 'Quand serai-je paye ?';
            }

            if ($context['facts']['urgent_sale']) {
                $questions[] = 'Ou gerer mon annonce ?';
            }

            $questions[] = 'Ou voir mes messages ?';
            $questions[] = 'Comment traiter un litige ?';
        } else {
            if ($context['facts']['food_order']) {
                $questions[] = 'Ou en est ma commande ?';
            }

            if ($context['facts']['escrow']) {
                $questions[] = 'Quand mon paiement securise sera libere ?';
            }

            if ($context['facts']['booking']) {
                $questions[] = 'Ou voir ma reservation ?';
            }

            $questions[] = 'Comment contacter le vendeur ?';
            $questions[] = 'Je n ai rien recu';
        }

        return array_slice(array_values(array_unique($questions)), 0, 3);
    }

    private function briefAccountSummary(array $context, int $limit = 1): ?string
    {
        $snapshot = array_values(array_filter(
            $context['snapshot'] ?? [],
            static fn ($item) => !str_contains($item, 'Aucun element recent significatif')
        ));

        if ($snapshot === []) {
            return null;
        }

        return 'Info utile : ' . implode(' ', array_slice($snapshot, 0, $limit));
    }

    private function formatReply(array $paragraphs): string
    {
        $cleaned = array_values(array_filter(array_map(static fn ($paragraph) => is_string($paragraph) ? trim($paragraph) : null, $paragraphs)));

        return implode("\n\n", $cleaned);
    }

    private function currentAreaHint(?string $currentPath, array $allowedAreas): ?string
    {
        return null;
    }

    private function resolveCurrentArea(?string $currentPath): ?array
    {
        $path = Str::lower(Str::ascii((string) $currentPath));

        if ($path === '') {
            return null;
        }

        $map = [
            '/escrow' => ['slug' => 'escrow', 'label' => 'la page des paiements securises'],
            '/payments' => ['slug' => 'payments', 'label' => 'la page des paiements'],
            '/food-orders' => ['slug' => 'orders', 'label' => 'la page des commandes'],
            '/orders' => ['slug' => 'orders', 'label' => 'la page des commandes'],
            '/bookings' => ['slug' => 'bookings', 'label' => 'la page des reservations'],
            '/messaging' => ['slug' => 'messages', 'label' => 'la messagerie'],
            '/messages' => ['slug' => 'messages', 'label' => 'la messagerie'],
            '/urgent-sales' => ['slug' => 'urgent_sales', 'label' => 'la page des ventes urgentes'],
            '/delivery' => ['slug' => 'delivery', 'label' => 'la page des livraisons'],
            '/invoices' => ['slug' => 'invoices', 'label' => 'la page des factures'],
            '/help' => ['slug' => 'help', 'label' => 'le centre d aide'],
            '/dashboard' => ['slug' => 'dashboard', 'label' => 'le tableau de bord'],
        ];

        foreach ($map as $pattern => $area) {
            if (str_contains($path, $pattern)) {
                return $area;
            }
        }

        return null;
    }

    private function escrowHelpText(?array $escrow, string $role): string
    {
        if (!$escrow) {
            return $role === 'prestataire'
                ? 'Ouvrez Paiements securises ou Paiements.'
                : 'Ouvrez Paiements securises puis le detail du dossier.';
        }

        return match ($escrow['status'] ?? '') {
            'pending' => $role === 'prestataire'
                ? 'Le montant est encore retenu.'
                : 'Le montant est encore retenu.',
            'partial' => 'Une partie du montant a deja ete traitee.',
            'released' => 'Le paiement a deja ete libere.',
            'refunded' => 'Le dossier est rembourse.',
            'partially_refunded' => 'Le dossier a deja un remboursement partiel.',
            'disputed', 'dispute_review' => 'Le dossier est en litige.',
            'cancelled' => 'La transaction a ete annulee.',
            default => 'Ouvrez le detail du paiement securise.',
        };
    }

    private function looksLikeFollowUp(string $normalized): bool
    {
        if ($normalized === '') {
            return false;
        }

        if (strlen($normalized) <= 24 && $this->mentionsAny($normalized, ['ca', 'cela', 'ensuite', 'apres', 'du coup'])) {
            return true;
        }

        return $this->mentionsAny($normalized, ['et ensuite', 'et apres', 'je fais quoi', 'du coup', 'ou exactement', 'et si', 'ca', 'cela', 'la suite', 'pour ca', 'pour cela']);
    }

    private function resolveRole(User $user): string
    {
        $role = (string) ($user->role ?? '');

        if (in_array($role, ['client', 'prestataire'], true)) {
            return $role;
        }

        return 'other';
    }

    private function pushAction(array &$actions, array &$starterActionIds, string $id, string $label, ?string $url, string $style, string $description): void
    {
        if (!$url || isset($actions[$id])) {
            return;
        }

        $actions[$id] = [
            'id' => $id,
            'label' => $label,
            'url' => $url,
            'style' => $style,
            'description' => $description,
        ];

        if (count($starterActionIds) < 6) {
            $starterActionIds[] = $id;
        }
    }

    private function safeRoute(string $name, mixed $parameters = []): ?string
    {
        try {
            return route($name, $parameters);
        } catch (\Throwable) {
            return null;
        }
    }

    private function mentionsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($this->containsTerm($haystack, (string) $needle)) {
                return true;
            }
        }

        return false;
    }

    private function containsTerm(string $haystack, string $needle): bool
    {
        $normalizedNeedle = trim(Str::lower(Str::ascii($needle)));

        if ($normalizedNeedle === '') {
            return false;
        }

        if (str_contains($normalizedNeedle, ' ')) {
            return str_contains($haystack, $normalizedNeedle);
        }

        return preg_match('/(^|[^a-z0-9])' . preg_quote($normalizedNeedle, '/') . '([^a-z0-9]|$)/', $haystack) === 1;
    }

    private function intentDefinitions(string $role): array
    {
        $definitions = [
            'thanks' => [
                'phrases' => ['merci beaucoup' => 5, 'c est bon' => 4],
                'keywords' => ['merci' => 4, 'nickel' => 4, 'parfait' => 4, 'top' => 3],
            ],
            'greeting' => [
                'phrases' => ['bonjour' => 4, 'bonsoir' => 4],
                'keywords' => ['salut' => 4, 'hello' => 4, 'cc' => 3],
            ],
            'order_status' => [
                'phrases' => [
                    'ou en est ma commande' => 6,
                    'ou voir ma commande' => 6,
                    'suivre ma commande' => 6,
                    'ou est mon colis' => 6,
                    'statut de ma commande' => 5,
                    'ou en est mon colis' => 6,
                ],
                'keywords' => [
                    'commande' => 2,
                    'colis' => 2,
                    'livraison' => 2,
                    'suivi' => 2,
                    'tracking' => 2,
                    'expedition' => 1,
                    'livre' => 1,
                    'livree' => 1,
                ],
            ],
            'escrow' => [
                'phrases' => [
                    'paiement securise' => 6,
                    'argent bloque' => 5,
                    'paiement bloque' => 5,
                    'quand mon paiement sera libere' => 6,
                    'quand l argent sera libere' => 6,
                ],
                'keywords' => [
                    'escrow' => 4,
                    'paiement' => 2,
                    'argent' => 2,
                    'bloque' => 2,
                    'bloquee' => 2,
                    'libere' => 2,
                    'liberee' => 2,
                ],
            ],
            'messaging' => [
                'phrases' => [
                    'contacter le vendeur' => 6,
                    'contacter le client' => 6,
                    'contacter le prestataire' => 6,
                    'ouvrir ma messagerie' => 6,
                    'envoyer un message' => 5,
                ],
                'keywords' => [
                    'message' => 2,
                    'messagerie' => 3,
                    'contacter' => 3,
                    'parler' => 2,
                    'ecrire' => 2,
                    'repondre' => 2,
                    'vendeur' => 2,
                    'client' => 2,
                    'prestataire' => 2,
                ],
            ],
            'issue' => [
                'phrases' => [
                    'je n ai rien recu' => 6,
                    'ouvrir un litige' => 6,
                    'je veux un remboursement' => 6,
                    'produit non conforme' => 6,
                    'article casse' => 5,
                ],
                'keywords' => [
                    'probleme' => 2,
                    'litige' => 3,
                    'remboursement' => 3,
                    'rembourse' => 2,
                    'reclamation' => 2,
                    'abime' => 2,
                    'casse' => 2,
                    'retard' => 1,
                ],
            ],
            'invoice' => [
                'phrases' => ['ou est ma facture' => 6, 'telecharger ma facture' => 6, 'voir mon recu' => 5],
                'keywords' => ['facture' => 3, 'recu' => 3, 'invoice' => 3, 'justificatif' => 2],
            ],
            'booking' => [
                'phrases' => ['ou voir ma reservation' => 6, 'ou est ma reservation' => 6, 'statut reservation' => 5],
                'keywords' => ['reservation' => 3, 'booking' => 3, 'rdv' => 2, 'rendez' => 2, 'creneau' => 2],
            ],
            'dashboard' => [
                'phrases' => ['tableau de bord' => 6, 'mon dashboard' => 5],
                'keywords' => ['dashboard' => 4, 'outils' => 2],
            ],
            'support' => [
                'phrases' => ['contacter le support' => 6, 'centre d aide' => 5, 'aide plateforme' => 5],
                'keywords' => ['support' => 3, 'aide' => 2, 'help' => 2, 'plateforme' => 2],
            ],
        ];

        if ($role === 'prestataire') {
            $definitions['earnings'] = [
                'phrases' => [
                    'quand serai je paye' => 7,
                    'ou voir mes gains' => 6,
                    'ou voir mes revenus' => 6,
                    'faire un retrait' => 6,
                ],
                'keywords' => [
                    'gains' => 4,
                    'revenu' => 4,
                    'revenus' => 4,
                    'versement' => 4,
                    'retrait' => 4,
                    'paye' => 2,
                    'payee' => 2,
                ],
            ];

            $definitions['catalog'] = [
                'phrases' => [
                    'ou gerer mon annonce' => 6,
                    'gerer mon stock' => 6,
                    'modifier mon produit' => 5,
                    'mes ventes urgentes' => 6,
                ],
                'keywords' => [
                    'stock' => 3,
                    'annonce' => 3,
                    'produit' => 2,
                    'publier' => 2,
                    'equipement' => 3,
                    'catalogue' => 2,
                    'vente urgente' => 4,
                ],
            ];

            $definitions['subscription'] = [
                'phrases' => ['activer mon abonnement' => 6, 'payer mon abonnement' => 6],
                'keywords' => ['abonnement' => 4, 'subscription' => 4],
            ];
        }

        if ($role === 'client') {
            $definitions['my_sales'] = [
                'phrases' => ['mes annonces' => 6, 'modifier mon annonce' => 6, 'publier mon produit' => 6],
                'keywords' => ['annonce' => 3, 'publier' => 2, 'produit' => 2, 'vente urgente' => 4],
            ];
        }

        return $definitions;
    }

    private function defaultClarificationIntents(string $role): array
    {
        return $role === 'prestataire'
            ? ['order_status', 'earnings', 'messaging', 'catalog']
            : ['order_status', 'escrow', 'messaging', 'booking'];
    }

    private function clarificationLabel(string $intent, string $role): ?string
    {
        return match ($intent) {
            'order_status' => 'une commande',
            'escrow' => 'un paiement',
            'messaging' => 'un message',
            'issue' => 'un probleme de commande',
            'invoice' => 'une facture',
            'booking' => 'une reservation',
            'catalog' => 'une annonce ou un stock',
            'my_sales' => 'une annonce',
            'earnings' => 'un versement',
            'dashboard' => 'vos outils',
            'subscription' => 'votre abonnement',
            'support' => 'le support',
            default => $role === 'prestataire' ? 'votre espace prestataire' : 'votre compte',
        };
    }

    private function clarificationSuggestions(string $role, array $context, array $candidates): array
    {
        $suggestions = [];

        foreach ($candidates as $intent) {
            $suggestion = match ($intent) {
                'order_status' => 'Ou en est ma commande ?',
                'escrow' => 'Quand mon paiement sera libere ?',
                'messaging' => $role === 'prestataire' ? 'Ou voir mes messages ?' : 'Comment contacter le vendeur ?',
                'issue' => 'Je n ai rien recu',
                'invoice' => 'Ou est ma facture ?',
                'booking' => 'Ou voir ma reservation ?',
                'catalog' => 'Ou gerer mon annonce ?',
                'my_sales' => 'Ou voir mes annonces ?',
                'earnings' => 'Quand serai-je paye ?',
                'dashboard' => 'Ou est mon tableau de bord ?',
                'subscription' => 'Activer mon abonnement',
                'support' => 'Contacter le support',
                default => null,
            };

            if ($suggestion) {
                $suggestions[] = $suggestion;
            }
        }

        if ($suggestions === []) {
            $suggestions = $this->starterQuestions($role, $context);
        }

        return array_slice(array_values(array_unique($suggestions)), 0, 3);
    }

    private function joinLabels(array $labels): string
    {
        $labels = array_values(array_unique(array_filter($labels)));

        if ($labels === []) {
            return '';
        }

        if (count($labels) === 1) {
            return $labels[0];
        }

        $last = array_pop($labels);

        return implode(', ', $labels) . ' ou ' . $last;
    }

    private function siteNavigationResponse(string $role, string $message): ?array
    {
        $matches = $this->searchKnowledgePages($role, $message);

        if ($matches === [] || (($matches[0]['score'] ?? 0) < 2)) {
            return null;
        }

        $best = $matches[0];
        $alternatives = array_slice($matches, 1, 2);
        $messageLines = [
            sprintf('La page la plus proche de votre demande est %s.', $best['label']),
            $best['summary'],
        ];

        if ($alternatives !== []) {
            $messageLines[] = 'Sinon vous pouvez aussi ouvrir ' . $this->joinLabels(array_map(
                static fn ($page) => $page['label'],
                $alternatives
            )) . '.';
        }

        return [
            'message' => $this->formatReply($messageLines),
            'actions' => [[
                'id' => 'site_' . $best['id'],
                'label' => $best['label'],
                'url' => $best['url'],
                'style' => 'primary',
                'description' => $best['summary'],
            ]],
            'suggestions' => [],
        ];
    }

    private function searchKnowledgePages(string $role, string $message): array
    {
        $normalized = trim((string) preg_replace('/\s+/', ' ', Str::lower(Str::ascii($message))));

        if ($normalized === '') {
            return [];
        }

        $matches = [];

        foreach ($this->siteKnowledgePages($role) as $page) {
            $score = 0;

            foreach ($page['keywords'] as $keyword) {
                if (!$this->containsTerm($normalized, $keyword)) {
                    continue;
                }

                $score += str_contains($keyword, ' ') ? 3 : 1;
            }

            if ($this->containsTerm($normalized, Str::lower(Str::ascii($page['label'])))) {
                $score += 3;
            }

            if ($score > 0) {
                $matches[] = $page + ['score' => $score];
            }
        }

        usort($matches, static function (array $left, array $right): int {
            if ($left['score'] === $right['score']) {
                if (($left['priority'] ?? 99) === ($right['priority'] ?? 99)) {
                    return strcmp($left['label'], $right['label']);
                }

                return ($left['priority'] ?? 99) <=> ($right['priority'] ?? 99);
            }

            return $right['score'] <=> $left['score'];
        });

        return array_slice($matches, 0, 3);
    }

    private function siteKnowledgePages(string $role): array
    {
        static $cache = [];

        if (isset($cache[$role])) {
            return $cache[$role];
        }

        $pages = [];

        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();

            if (!$this->shouldIndexKnowledgeRoute($name, $route->methods(), $role)) {
                continue;
            }

            $url = $this->safeRoute($name);

            if (!$url) {
                continue;
            }

            $page = $this->makeKnowledgePage($name, (string) $route->uri(), $url);

            if ($page) {
                $pages[] = $page;
            }
        }

        return $cache[$role] = $pages;
    }

    private function shouldIndexKnowledgeRoute(string $name, array $methods, string $role): bool
    {
        if ($name === '' || !in_array('GET', $methods, true)) {
            return false;
        }

        $normalized = Str::lower(Str::ascii($name));

        if (
            str_starts_with($normalized, 'admin.')
            || str_starts_with($normalized, 'auth.')
            || str_starts_with($normalized, 'api.')
            || str_starts_with($normalized, 'password.')
            || str_starts_with($normalized, 'verification.')
            || str_starts_with($normalized, 'debugbar.')
            || str_starts_with($normalized, 'administrateur.')
        ) {
            return false;
        }

        if (
            str_contains($normalized, 'guidance-assistant')
            || str_contains($normalized, '.api.')
            || str_contains($normalized, '.store')
            || str_contains($normalized, '.update')
            || str_contains($normalized, '.destroy')
            || str_contains($normalized, '.delete')
            || str_contains($normalized, '.reply')
            || str_contains($normalized, '.accept')
            || str_contains($normalized, '.reject')
            || str_contains($normalized, '.confirm')
            || str_contains($normalized, '.cancel')
            || str_contains($normalized, '.refuse')
            || str_contains($normalized, '.approve')
            || str_contains($normalized, '.toggle')
            || str_contains($normalized, '.export')
            || str_contains($normalized, '.analytics')
            || str_contains($normalized, '.mark')
            || str_contains($normalized, '.cleanup')
            || str_contains($normalized, '.bulk')
            || str_contains($normalized, '.respond')
            || str_contains($normalized, '.report')
            || str_contains($normalized, '.process')
            || str_contains($normalized, '.intent')
            || str_contains($normalized, '.ajax')
            || str_contains($normalized, '.unread-count')
        ) {
            return false;
        }

        return $this->routeAllowedForRole($normalized, $role);
    }

    private function routeAllowedForRole(string $routeName, string $role): bool
    {
        if ($role === 'client' && str_starts_with($routeName, 'prestataire.')) {
            return false;
        }

        if ($role === 'prestataire' && str_starts_with($routeName, 'client.')) {
            return in_array($routeName, ['client.prestataires.index', 'client.prestataires.show'], true);
        }

        if (str_starts_with($routeName, 'login') || str_starts_with($routeName, 'register')) {
            return false;
        }

        return true;
    }

    private function makeKnowledgePage(string $routeName, string $uri, string $url): ?array
    {
        $module = $this->knowledgeModuleMeta($routeName, $uri);

        if (!$module) {
            return null;
        }

        $action = $this->knowledgeActionMeta($routeName);
        $label = match ($action['type']) {
            'create' => 'Creer ' . Str::lower($module['label']),
            'edit' => 'Modifier ' . Str::lower($module['label']),
            'show' => 'Voir ' . Str::lower($module['label']),
            'payment' => 'Paiement ' . Str::lower($module['label']),
            default => $module['label'],
        };

        if ($action['step']) {
            $label .= ' - etape ' . $action['step'];
        }

        $summary = match ($action['type']) {
            'create' => 'Page pour creer ou publier.',
            'edit' => 'Page pour modifier ce module.',
            'show' => 'Page de detail et de suivi.',
            'payment' => 'Page pour payer ou suivre ce paiement.',
            default => 'Page pour consulter ou gerer ce module.',
        };

        $keywords = array_merge(
            [$label, $module['label']],
            $module['keywords'],
            $action['keywords'],
            $this->knowledgeRouteTokens($routeName, $uri)
        );

        return [
            'id' => Str::slug($routeName, '_'),
            'label' => $label,
            'summary' => $summary,
            'url' => $url,
            'priority' => $action['priority'],
            'keywords' => array_values(array_unique(array_map(
                static fn ($keyword) => trim(Str::lower(Str::ascii((string) $keyword))),
                array_filter($keywords)
            ))),
        ];
    }

    private function knowledgeModuleMeta(string $routeName, string $uri): ?array
    {
        $haystack = Str::lower(Str::ascii($routeName . ' ' . $uri));
        $modules = $this->knowledgeModules();
        uksort($modules, static fn ($left, $right) => strlen($right) <=> strlen($left));

        foreach ($modules as $pattern => $meta) {
            if (str_contains($haystack, $pattern)) {
                return $meta;
            }
        }

        return null;
    }

    private function knowledgeModules(): array
    {
        return [
            'notification-settings' => ['label' => 'Parametres de notification', 'keywords' => ['notification', 'notifications', 'parametres notification', 'alerte']],
            'equipment-rental-requests' => ['label' => 'Demandes de location equipement', 'keywords' => ['location equipement', 'demande location', 'equipement request']],
            'equipment-rentals' => ['label' => 'Locations equipement', 'keywords' => ['location equipement', 'equipement', 'louer equipement']],
            'my-urgent-sales' => ['label' => 'Mes annonces', 'keywords' => ['annonce', 'annonces', 'mes annonces', 'publier', 'vente urgente']],
            'food-products' => ['label' => 'Produits food', 'keywords' => ['produit food', 'menu', 'plat', 'produit', 'catalogue food']],
            'food-orders' => ['label' => 'Commandes food', 'keywords' => ['commande', 'commandes', 'food', 'suivi commande', 'livraison']],
            'address-book' => ['label' => 'Carnet d adresses', 'keywords' => ['adresse', 'adresses', 'carnet adresse', 'livraison adresse']],
            'become-provider' => ['label' => 'Devenir prestataire', 'keywords' => ['devenir prestataire', 'inscription prestataire', 'provider']],
            'urgent-sales' => ['label' => 'Ventes urgentes', 'keywords' => ['vente urgente', 'annonce', 'urgent', 'produit urgent']],
            'prestataires' => ['label' => 'Prestataires', 'keywords' => ['prestataire', 'vendeur', 'professionnel', 'boutique']],
            'subscriptions' => ['label' => 'Abonnement', 'keywords' => ['abonnement', 'subscription', 'payer abonnement']],
            'availability' => ['label' => 'Disponibilites', 'keywords' => ['disponibilite', 'disponibilites', 'calendrier', 'creneau']],
            'verification' => ['label' => 'Verification', 'keywords' => ['verification', 'verifier', 'documents', 'validation']],
            'notifications' => ['label' => 'Notifications', 'keywords' => ['notification', 'notifications', 'alertes']],
            'messaging' => ['label' => 'Messagerie', 'keywords' => ['message', 'messagerie', 'conversation', 'contacter']],
            'messages' => ['label' => 'Messagerie', 'keywords' => ['message', 'messages', 'conversation', 'contacter']],
            'payments' => ['label' => 'Paiements', 'keywords' => ['paiement', 'paiements', 'transaction', 'argent']],
            'invoices' => ['label' => 'Factures', 'keywords' => ['facture', 'factures', 'recu', 'justificatif']],
            'bookings' => ['label' => 'Reservations', 'keywords' => ['reservation', 'reservations', 'booking', 'rdv']],
            'delivery' => ['label' => 'Livraisons', 'keywords' => ['livraison', 'livraisons', 'suivi livraison', 'driver']],
            'profile' => ['label' => 'Profil', 'keywords' => ['profil', 'compte', 'mon profil', 'modifier profil']],
            'dashboard' => ['label' => 'Tableau de bord', 'keywords' => ['dashboard', 'tableau de bord', 'accueil', 'outils']],
            'inventory' => ['label' => 'Inventaire', 'keywords' => ['inventaire', 'stock', 'inventaire produit']],
            'equipment' => ['label' => 'Equipements', 'keywords' => ['equipement', 'equipements', 'materiel', 'stock']],
            'services' => ['label' => 'Services', 'keywords' => ['service', 'services', 'prestation', 'publier service']],
            'search' => ['label' => 'Recherche', 'keywords' => ['recherche', 'chercher', 'trouver', 'prestataire']],
            'agenda' => ['label' => 'Agenda', 'keywords' => ['agenda', 'planning', 'calendrier']],
            'quotes' => ['label' => 'Devis', 'keywords' => ['devis', 'quote', 'tarif']],
            'tenders' => ['label' => 'Appels d offres', 'keywords' => ['appel offre', 'appel d offre', 'tender', 'projet']],
            'auctions' => ['label' => 'Encheres', 'keywords' => ['enchere', 'encheres', 'auction']],
            'follows' => ['label' => 'Suivis prestataires', 'keywords' => ['favori', 'favoris', 'follow', 'suivre prestataire']],
            'videos' => ['label' => 'Videos', 'keywords' => ['video', 'videos']],
            'drivers' => ['label' => 'Livreurs', 'keywords' => ['livreur', 'livreurs', 'driver']],
            'missions' => ['label' => 'Missions', 'keywords' => ['mission', 'missions']],
            'legal-pages' => ['label' => 'Pages legales', 'keywords' => ['legal', 'pages legales', 'mentions legales']],
            'legal' => ['label' => 'Conditions du site', 'keywords' => ['condition', 'conditions', 'cgu', 'cgv', 'confidentialite', 'mentions legales', 'legal']],
            'contact' => ['label' => 'Contact', 'keywords' => ['contact', 'contacter', 'support', 'assistance', 'plateforme']],
            'help' => ['label' => 'Aide', 'keywords' => ['aide', 'help', 'support', 'faq']],
            'reviews' => ['label' => 'Avis', 'keywords' => ['avis', 'review', 'notation']],
            'escrow' => ['label' => 'Paiements securises', 'keywords' => ['escrow', 'paiement securise', 'argent bloque', 'litige']],
        ];
    }

    private function knowledgeActionMeta(string $routeName): array
    {
        $normalized = Str::lower(Str::ascii($routeName));
        $step = null;

        if (preg_match('/\\.step(\\d+)/', $normalized, $matches) === 1) {
            $step = $matches[1];
        }

        if (str_contains($normalized, '.create')) {
            return ['type' => 'create', 'keywords' => ['creer', 'ajouter', 'publier', 'nouveau'], 'step' => $step, 'priority' => 4];
        }

        if (str_contains($normalized, '.edit')) {
            return ['type' => 'edit', 'keywords' => ['modifier', 'editer', 'changer', 'mise a jour'], 'step' => $step, 'priority' => 3];
        }

        if (str_contains($normalized, '.show')) {
            return ['type' => 'show', 'keywords' => ['voir', 'detail', 'details', 'suivi'], 'step' => $step, 'priority' => 2];
        }

        if (str_contains($normalized, '.payment')) {
            return ['type' => 'payment', 'keywords' => ['payer', 'paiement', 'regler'], 'step' => $step, 'priority' => 3];
        }

        return ['type' => 'index', 'keywords' => ['ouvrir', 'voir', 'page', 'liste', 'gerer'], 'step' => $step, 'priority' => 1];
    }

    private function knowledgeRouteTokens(string $routeName, string $uri): array
    {
        $tokens = preg_split('/[.\\/_-]+/', Str::lower(Str::ascii($routeName . ' ' . $uri))) ?: [];

        return array_values(array_filter($tokens, static function ($token) {
            return $token !== ''
                && !in_array($token, [
                    'client',
                    'prestataire',
                    'index',
                    'show',
                    'create',
                    'edit',
                    'step1',
                    'step2',
                    'step3',
                    'step4',
                    'step',
                    'api',
                    'web',
                ], true)
                && strlen($token) >= 3;
        }));
    }

    private function takeActions(array $actions, array $ids): array
    {
        $selected = [];

        foreach ($ids as $id) {
            if (isset($actions[$id])) {
                $selected[] = $actions[$id];
            }
        }

        return array_slice($selected, 0, 4);
    }

    private function labelFoodOrderStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'en attente',
            'accepted' => 'acceptee',
            'preparing' => 'en preparation',
            'ready' => 'prete',
            'picked_up' => 'recuperee',
            'in_transit' => 'en livraison',
            'delivered' => 'livree',
            'completed' => 'terminee',
            'cancelled' => 'annulee',
            default => $status !== '' ? $status : 'statut inconnu',
        };
    }

    private function labelEscrowStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'en attente',
            'partial' => 'partiel',
            'released' => 'libere',
            'refunded' => 'rembourse',
            'partially_refunded' => 'remboursement partiel',
            'disputed' => 'en litige',
            'dispute_review' => 'dossier litige',
            'cancelled' => 'annule',
            default => $status !== '' ? $status : 'statut inconnu',
        };
    }

    private function labelBookingStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'en attente',
            'confirmed' => 'confirmee',
            'completed' => 'terminee',
            'cancelled' => 'annulee',
            'rejected' => 'refusee',
            default => $status !== '' ? $status : 'statut inconnu',
        };
    }

    private function labelUrgentSaleStatus(string $status): string
    {
        return match ($status) {
            'active' => 'active',
            'sold' => 'vendue',
            'withdrawn' => 'retiree',
            'reported' => 'signalee',
            'blocked' => 'bloquee',
            default => $status !== '' ? $status : 'statut inconnu',
        };
    }
}
