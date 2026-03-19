<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Client can view their own bookings, Prestataire can view bookings for their services.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Admin can view all
        if ($user->role === 'administrateur') {
            return true;
        }

        // Client can view their own bookings
        if ($user->role === 'client' && $user->client && (int) $user->client->id === (int) $booking->client_id) {
            return true;
        }

        // Prestataire can view bookings for their services
        if ($user->role === 'prestataire' && $user->prestataire && (int) $user->prestataire->id === (int) $booking->prestataire_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'client';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking): bool
    {
        if ($user->role === 'administrateur') {
            return true;
        }

        // Prestataire can update their bookings (confirm, complete, etc.)
        if ($user->role === 'prestataire' && $user->prestataire && (int) $user->prestataire->id === (int) $booking->prestataire_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->role === 'administrateur';
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->role === 'administrateur') {
            return true;
        }

        // Client can cancel their own bookings
        if ($user->role === 'client' && $user->client && (int) $user->client->id === (int) $booking->client_id) {
            return in_array($booking->status, ['pending', 'confirmed']);
        }

        // Prestataire can cancel/refuse bookings
        if ($user->role === 'prestataire' && $user->prestataire && (int) $user->prestataire->id === (int) $booking->prestataire_id) {
            return in_array($booking->status, ['pending', 'confirmed']);
        }

        return false;
    }

    /**
     * Determine whether the user can pay for the booking.
     */
    public function pay(User $user, Booking $booking): bool
    {
        // Only the client owner can access payment actions.
        // Payment state constraints are enforced in controllers to avoid policy side effects.
        return $user->role === 'client'
            && $user->client
            && (int) $user->client->id === (int) $booking->client_id;
    }
}
