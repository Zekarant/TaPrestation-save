<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotePolicy
{
    use HandlesAuthorization;

    /**
     * Le prestataire peut voir ses propres devis
     */
    public function view(User $user, Quote $quote): bool
    {
        // Le prestataire propriétaire
        if ($user->prestataire && $user->prestataire->id === $quote->prestataire_id) {
            return true;
        }

        // Le client destinataire (si envoyé)
        if ($user->client && $user->client->id === $quote->client_id) {
            return in_array($quote->status, [
                Quote::STATUS_SENT,
                Quote::STATUS_VIEWED,
                Quote::STATUS_ACCEPTED,
                Quote::STATUS_REJECTED,
            ]);
        }

        return false;
    }

    /**
     * Le prestataire peut créer des devis
     */
    public function create(User $user): bool
    {
        return $user->prestataire !== null;
    }

    /**
     * Seul le prestataire propriétaire peut modifier
     */
    public function update(User $user, Quote $quote): bool
    {
        return $user->prestataire && $user->prestataire->id === $quote->prestataire_id;
    }

    /**
     * Seul le prestataire propriétaire peut supprimer (brouillons uniquement)
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $user->prestataire 
            && $user->prestataire->id === $quote->prestataire_id
            && $quote->status === Quote::STATUS_DRAFT;
    }

    /**
     * Le client peut accepter/refuser
     */
    public function respond(User $user, Quote $quote): bool
    {
        return $user->client 
            && $user->client->id === $quote->client_id
            && $quote->can_be_accepted;
    }
}
