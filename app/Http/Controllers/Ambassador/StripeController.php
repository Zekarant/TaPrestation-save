<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    public function index()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        return view('ambassador.stripe.index', compact('ambassador'));
    }

    public function createAccount(Request $request)
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        if ($ambassador->stripe_account_id) {
            return back()->with('warning', 'Vous avez déjà un compte Stripe Connect.');
        }

        try {
            \Stripe\Stripe::setApiKey(config('stripe.secret'));

            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'FR',
                'email' => Auth::user()->email,
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
                'metadata' => [
                    'ambassador_id' => $ambassador->id,
                    'user_id' => Auth::id(),
                ],
            ]);

            $ambassador->update([
                'stripe_account_id' => $account->id,
                'stripe_account_status' => 'pending',
            ]);

            // Create onboarding link
            $accountLink = \Stripe\AccountLink::create([
                'account' => $account->id,
                'refresh_url' => route('ambassador.stripe.index'),
                'return_url' => route('ambassador.stripe.callback'),
                'type' => 'account_onboarding',
            ]);

            return redirect($accountLink->url);
        } catch (\Exception $e) {
            Log::error('Ambassador Stripe Connect error', ['error' => $e->getMessage()]);
            return back()->withErrors(['stripe' => 'Erreur Stripe : ' . $e->getMessage()]);
        }
    }

    public function callback()
    {
        $ambassador = Ambassador::where('user_id', Auth::id())->firstOrFail();

        if ($ambassador->stripe_account_id) {
            try {
                \Stripe\Stripe::setApiKey(config('stripe.secret'));
                $account = \Stripe\Account::retrieve($ambassador->stripe_account_id);

                $status = 'pending';
                if ($account->charges_enabled && $account->payouts_enabled) {
                    $status = 'verified';
                } elseif ($account->requirements?->currently_due) {
                    $status = 'restricted';
                }

                $ambassador->update(['stripe_account_status' => $status]);
            } catch (\Exception $e) {
                Log::error('Ambassador Stripe callback error', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('ambassador.stripe.index')
            ->with('success', 'Compte Stripe mis à jour.');
    }
}
