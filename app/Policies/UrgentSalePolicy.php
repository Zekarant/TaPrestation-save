<?php

namespace App\Policies;

use App\Models\UrgentSale;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UrgentSalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'prestataire' && $user->prestataire !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UrgentSale $urgentSale): bool
    {
        return $user->role === 'prestataire' && 
               $user->prestataire !== null && 
               $user->prestataire->id === $urgentSale->prestataire_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'prestataire' && $user->prestataire !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UrgentSale $urgentSale): bool
    {
        return $user->role === 'prestataire' && 
               $user->prestataire !== null && 
               $user->prestataire->id === $urgentSale->prestataire_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UrgentSale $urgentSale): bool
    {
        return $user->role === 'prestataire' && 
               $user->prestataire !== null && 
               $user->prestataire->id === $urgentSale->prestataire_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UrgentSale $urgentSale): bool
    {
        return $user->role === 'prestataire' && 
               $user->prestataire !== null && 
               $user->prestataire->id === $urgentSale->prestataire_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UrgentSale $urgentSale): bool
    {
        return $user->role === 'prestataire' && 
               $user->prestataire !== null && 
               $user->prestataire->id === $urgentSale->prestataire_id;
    }
}
