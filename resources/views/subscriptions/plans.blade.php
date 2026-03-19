@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 pb-28 sm:pb-8">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-4xl font-bold text-gray-900">Nos Abonnements</h1>
        <p class="text-gray-600 mt-2">Choisissez le plan idéal pour votre activité</p>
    </div>

    <!-- Current Subscription Info -->
    @if ($currentSubscription = auth()->user()->subscriptions()->where('status', 'active')->first())
    <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-900">Abonnement actuel</h3>
                <p class="text-blue-700">{{ $currentSubscription->subscriptionPlan->name }} Plan</p>
                <p class="text-sm text-blue-600 mt-1">Renews on {{ $currentSubscription->current_period_end->format('d M Y') }}</p>
            </div>
            <a href="{{ route('subscription.my') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                Manage Subscription →
            </a>
        </div>
    </div>
    @endif

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach ($plans as $plan)
        <div class="border rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition-shadow {{ $plan->id === $currentSubscription?->subscription_plan_id ? 'ring-2 ring-blue-500' : '' }}">
            <!-- Plan Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                <h3 class="text-2xl font-bold">{{ $plan->name }}</h3>
                <p class="text-blue-100 mt-1 text-sm">{{ $plan->description }}</p>
            </div>

            <!-- Price -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-baseline">
                    <span class="text-4xl font-bold text-gray-900">€{{ number_format($plan->price, 2) }}</span>
                    <span class="text-gray-600 ml-2">/month</span>
                </div>
            </div>

            <!-- Features List -->
            <div class="px-6 py-4">
                <ul class="space-y-3">
                    <!-- Booking Limit -->
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-700">
                            {{ $plan->booking_limit ? $plan->booking_limit . ' bookings/month' : 'Unlimited bookings' }}
                        </span>
                    </li>

                    <!-- Analytics -->
                    @if ($plan->includes_analytics)
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-700">Advanced Analytics</span>
                    </li>
                    @endif

                    <!-- Priority Support -->
                    @if ($plan->includes_priority_support)
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-700">Priority Support</span>
                    </li>
                    @endif

                    <!-- Commission Rate -->
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-700">{{ $plan->commission_rate }}% commission</span>
                    </li>

                    <!-- Additional Features -->
                    @if ($plan->features)
                    @foreach (json_decode($plan->features, true) as $feature)
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-700 text-sm">{{ $feature }}</span>
                    </li>
                    @endforeach
                    @endif
                </ul>
            </div>

            <!-- CTA Button -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                @if ($plan->id === $currentSubscription?->subscription_plan_id)
                <button class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-default font-semibold">
                    ✓ Current Plan
                </button>
                @else
                <form action="{{ route('subscription.subscribe', $plan) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition-colors">
                        Subscribe Now
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- FAQ Section -->
    <div class="mt-16 bg-gray-50 rounded-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Questions fréquentes</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Puis-je changer de plan ?</h3>
                <p class="text-gray-600">Oui, vous pouvez passer à un plan supérieur ou inférieur à tout moment. Les changements seront appliqués lors de votre prochain cycle de facturation.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Quels moyens de paiement acceptez-vous ?</h3>
                <p class="text-gray-600">Nous acceptons toutes les cartes bancaires principales via Stripe. Votre paiement est sécurisé et chiffré.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Puis-je annuler à tout moment ?</h3>
                <p class="text-gray-600">Oui, vous pouvez annuler votre abonnement à tout moment. Votre accès reste actif jusqu'à la fin de votre période de facturation.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Proposez-vous des remboursements ?</h3>
                <p class="text-gray-600">Nous offrons une garantie satisfait ou remboursé de 7 jours si vous n'êtes pas satisfait de votre abonnement.</p>
            </div>
        </div>
    </div>
</div>
@endsection
