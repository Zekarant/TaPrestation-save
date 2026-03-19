<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthRedirectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard redirect based on user role.
     */
    public function dashboard(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        } elseif ($user->hasRole('prestataire')) {
            return redirect()->route('prestataire.dashboard');
        } elseif ($user->hasRole('administrateur')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }

    /**
     * Profile edit redirect based on user role and referer context.
     */
    public function profileEdit(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $referer = request()->headers->get('referer', '');

        if (str_contains($referer, '/prestataire/') && $user->hasRole('prestataire')) {
            return redirect()->route('prestataire.profile.edit');
        }

        if (str_contains($referer, '/client/') && $user->hasRole('client')) {
            return redirect()->route('client.profile.edit');
        }

        if ($user->hasRole('prestataire')) {
            return redirect()->route('prestataire.profile.edit');
        } elseif ($user->hasRole('client')) {
            return redirect()->route('client.profile.edit');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Profile settings redirect based on user role and referer context.
     */
    public function profileSettings(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $referer = request()->headers->get('referer', '');

        $isInPrestataireMode = str_contains($referer, '/prestataire/') || str_contains($referer, 'prestataire.');
        $isInClientMode = str_contains($referer, '/client/') || str_contains($referer, 'client.');

        if ($isInPrestataireMode && $user->hasRole('prestataire')) {
            return redirect()->route('prestataire.profile.edit');
        }

        if ($isInClientMode && $user->hasRole('client')) {
            return redirect()->route('client.profile.edit');
        }

        if ($user->hasRole('prestataire')) {
            return redirect()->route('prestataire.profile.edit');
        }

        if ($user->hasRole('client')) {
            return redirect()->route('client.profile.edit');
        }

        return redirect()->route('profile.edit');
    }

    /**
     * Universal booking show - redirects based on role.
     */
    public function bookingUniversal(Booking $booking): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->client && (int) $booking->client_id === (int) $user->client->id) {
            return redirect()->route('client.bookings.show', $booking);
        }

        if ($user->prestataire && (int) $booking->prestataire_id === (int) $user->prestataire->id) {
            return redirect()->route('prestataire.bookings.show', $booking);
        }

        if ($user->hasRole('administrateur')) {
            return redirect()->route('administrateur.bookings.show', $booking);
        }

        abort(403);
    }

    /**
     * Legacy redirect for /client-reviews/{number} URLs.
     * Resolves via booking_number, user ID, client ID, or booking ID.
     */
    public function clientReviewRedirect(string $number): RedirectResponse
    {
        $booking = Booking::where('booking_number', $number)->first();
        if ($booking?->client?->user_id) {
            return redirect()->route('client.reviews', ['user' => $booking->client->user_id]);
        }

        if (is_numeric($number)) {
            $user = User::find($number);
            if ($user) {
                return redirect()->route('client.reviews', ['user' => $number]);
            }

            $client = Client::find($number);
            if ($client?->user_id) {
                return redirect()->route('client.reviews', ['user' => $client->user_id]);
            }

            $booking = Booking::find($number);
            if ($booking?->client?->user_id) {
                return redirect()->route('client.reviews', ['user' => $booking->client->user_id]);
            }
        }

        abort(404);
    }
}
