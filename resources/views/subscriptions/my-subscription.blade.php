@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6 lg:py-8">
    <div class="mb-6 sm:mb-8">
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">My Subscription</h1>
    </div>

    @if ($subscription)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Subscription Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-8 border-l-4 border-blue-500">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $subscription->subscriptionPlan->name }} Plan</h2>
                        <p class="text-gray-600 mt-1">€{{ number_format($subscription->current_amount, 2) }}/month</p>
                    </div>
                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </div>

                <!-- Billing Information -->
                <div class="space-y-4 border-t border-gray-200 pt-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Current Period</span>
                        <span class="font-semibold text-gray-900">
                            {{ $subscription->current_period_start->format('d M Y') }} — {{ $subscription->current_period_end->format('d M Y') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Prochaine date de facturation</span>
                        <span class="font-semibold text-gray-900">
                            {{ $subscription->current_period_end->format('d M Y') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Auto-Renewal</span>
                        <span class="font-semibold {{ $subscription->auto_renew ? 'text-green-600' : 'text-red-600' }}">
                            {{ $subscription->auto_renew ? '✓ Enabled' : '✗ Disabled' }}
                        </span>
                    </div>

                    @if ($subscription->cancelled_at)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Cancellation Date</span>
                        <span class="font-semibold text-gray-900">{{ $subscription->cancelled_at->format('d M Y') }}</span>
                    </div>
                    @endif
                </div>

                <!-- Plan Features -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Plan Features</h3>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if ($subscription->subscriptionPlan->booking_limit)
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            {{ $subscription->subscriptionPlan->booking_limit }} bookings/month
                        </li>
                        @else
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Unlimited bookings
                        </li>
                        @endif

                        @if ($subscription->subscriptionPlan->includes_analytics)
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Advanced Analytics
                        </li>
                        @endif

                        @if ($subscription->subscriptionPlan->includes_priority_support)
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Priority Support
                        </li>
                        @endif

                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            {{ $subscription->subscriptionPlan->commission_rate }}% commission
                        </li>
                    </ul>
                </div>

                @if ($subscription->subscriptionPlan->features)
                <div class="mt-4">
                    <ul class="space-y-2">
                        @foreach (json_decode($subscription->subscriptionPlan->features, true) as $feature)
                        <li class="flex items-center text-gray-700 text-sm">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="mt-8 flex gap-3">
                    <a href="{{ route('subscription.plans') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold text-center transition-colors">
                        Change Plan
                    </a>
                    @if ($subscription->status !== 'cancelled')
                    <button onclick="confirmCancel()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold transition-colors">
                        Cancel Subscription
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: Usage Stats -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Info</h3>
                
                <div class="space-y-4">
                    <div class="text-center py-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                        <p class="text-sm text-gray-600">Days Remaining</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">
                            {{ $subscription->current_period_end->diffInDays(now()) }}
                        </p>
                    </div>

                    <div class="text-center py-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg">
                        <p class="text-sm text-gray-600">Bookings Used</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600">
                            {{ auth()->user()->bookings()->count() ?? 0 }}
                        </p>
                    </div>

                    @if ($subscription->subscriptionPlan->booking_limit)
                    <div class="py-4">
                        <p class="text-xs text-gray-600 mb-2">Progression de la limite mensuelle</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, (auth()->user()->bookings()->count() / $subscription->subscriptionPlan->booking_limit) * 100) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            {{ auth()->user()->bookings()->count() ?? 0 }} / {{ $subscription->subscriptionPlan->booking_limit }}
                        </p>
                    </div>
                    @endif

                    <button onclick="downloadInvoice()" class="w-full mt-6 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded-lg font-semibold transition-colors text-sm">
                        Download Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Annuler l'abonnement ?</h3>
            <p class="text-gray-600 mb-6">
                Êtes-vous sûr de vouloir annuler votre abonnement {{ $subscription->subscriptionPlan->name }} ? 
                Vous perdrez l'accès aux fonctionnalités premium à la fin de votre période de facturation en cours.
            </p>

            <form action="{{ route('subscription.cancel') }}" method="POST" class="mb-4">
                @csrf
                <textarea name="reason" placeholder="Facultatif : Dites-nous pourquoi vous annulez..." class="w-full border border-gray-300 rounded-lg p-3 mb-4 text-sm" rows="3"></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold">
                        Confirmer l'annulation
                    </button>
                    <button type="button" onclick="cancelModal.classList.add('hidden')" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
                        Garder l'abonnement
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Aucun abonnement actif</h2>
        <p class="text-gray-600 mb-6">Vous n'avez pas encore d'abonnement actif.</p>
        <a href="{{ route('subscription.plans') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-3 px-8 rounded-lg font-semibold">
            Voir les plans
        </a>
    </div>
    @endif
</div>

<script>
    const cancelModal = document.getElementById('cancelModal');
    
    function confirmCancel() {
        cancelModal.classList.remove('hidden');
    }

    function downloadInvoice() {
        alert('Téléchargement de facture bientôt disponible !');
    }
</script>
@endsection
