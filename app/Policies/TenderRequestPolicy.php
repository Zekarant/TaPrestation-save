<?php

namespace App\Policies;

use App\Models\TenderRequest;
use App\Models\User;

class TenderRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TenderRequest $tenderRequest): bool
    {
        // Le propriétaire peut toujours voir son appel d'offre
        if ($user->client && $user->client->id === $tenderRequest->client_id) {
            return true;
        }
        
        // Les prestataires invités peuvent voir
        if ($user->prestataire) {
            return true;
        }
        
        // Les admins peuvent voir
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TenderRequest $tenderRequest): bool
    {
        // Seul le propriétaire peut modifier (même s'il est devenu prestataire)
        return $user->client && $user->client->id === $tenderRequest->client_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TenderRequest $tenderRequest): bool
    {
        // Seul le propriétaire peut supprimer (même s'il est devenu prestataire)
        return $user->client && $user->client->id === $tenderRequest->client_id;
    }
}
