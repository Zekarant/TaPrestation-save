@extends('layouts.app')

@section('content')
<style>
/* Gray color scheme and styling */
.slot-option {
    transition: all 0.2s ease-in-out;
}

.slot-option:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Enhanced button styles */
.btn-primary {
    background-color: #4b5563;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.2s ease-in-out;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    background-color: #374151;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.btn-secondary {
    background-color: #e5e7eb;
    color: #374151;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    border: none;
}

.btn-secondary:hover {
    background-color: #d1d5db;
    transform: translateY(-1px);
}

/* Enhanced action buttons */
.action-button {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.action-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Status badge enhancement */
.status-badge {
    border-radius: 9999px;
    padding: 0.25rem 0.75rem;
    font-weight: 600;
    font-size: 0.75rem;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
}

.status-accepted {
    background-color: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
}

.status-completed {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.status-rejected {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Profile card enhancement */
.profile-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.profile-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border-color: #d1d5db;
}

/* Review card enhancement */
.review-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    border-color: #d1d5db;
}

/* Gray color variations */
.bg-gray-50 {
    background-color: #f9fafb;
}

.text-gray-600 {
    color: #4b5563;
}

.bg-gray-100 {
    background-color: #f3f4f6;
}

.text-gray-700 {
    color: #374151;
}

.bg-gray-200 {
    background-color: #e5e7eb;
}

.text-gray-800 {
    color: #1f2937;
}

.bg-gray-500 {
    background-color: #6b7280;
}

.text-gray-900 {
    color: #111827;
}
</style>

<div class="bg-gray-50">
    <main>
        <div class="max-w-6xl mx-auto px-4 py-6 sm:py-8">
            <!-- En-tête du profil -->
            <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                    <div class="flex items-center">
                        @if($client->avatar)
                            <img class="h-20 w-20 rounded-full object-cover shadow-lg" src="{{ asset('storage/' . $client->avatar) }}" alt="{{ $client->user->name ?? 'Client' }}">
                        @else
                            <div class="h-20 w-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center shadow-lg">
                                <span class="text-2xl font-medium text-gray-700">{{ $client->user->name ? substr($client->user->name, 0, 1) : 'C' }}</span>
                            </div>
                        @endif
                        <div class="ml-6">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-1">{{ $client->user->name ?? 'Client' }}</h1>
                            <p class="text-base sm:text-lg text-gray-700">Client</p>
                            @if($client->location)
                                <p class="text-sm text-gray-600 mt-1 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-gray-500"></i>
                                    {{ $client->location }}
                                </p>
                            @endif
                            <p class="text-sm text-gray-600 mt-1 flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-gray-500"></i>
                                Membre depuis {{ $client->user->created_at->format('F Y') }}
                            </p>
                        </div>
                    </div>
                    @auth
                        @if(auth()->id() === $client->user_id)
                            <div class="flex justify-center sm:justify-end">
                                <a href="{{ route('client.profile.edit') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 sm:px-6 py-3 rounded-lg transition duration-200 shadow-lg hover:shadow-xl font-bold text-sm sm:text-base flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i>
                                    Modifier le profil
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Colonne principale -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Présentation -->
                    @if($client->bio)
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Présentation</h3>
                            <p class="text-gray-700 leading-relaxed">{{ $client->bio }}</p>
                        </div>
                    @endif
                    
                    <!-- Demandes récentes -->
                    @if($recentRequests->count() > 0)
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Demandes récentes</h3>
                            <div class="space-y-4">
                                @foreach($recentRequests as $request)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-md font-medium text-gray-900">{{ $request->title }}</h4>
                                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($request->description, 120) }}</p>
                                                <div class="flex items-center mt-2 space-x-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                                                        <!-- Budget supprimé pour des raisons de confidentialité -->
                                                    </span>
                                                    @if($request->category)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm">
                                                            {{ $request->category()->exists() ? $request->category->name : 'Non catégorisé' }}
                                                        </span>
                                                    @endif
                                                    <span class="text-xs text-gray-500 flex items-center">
                                                        <i class="far fa-clock mr-1 text-gray-500"></i>
                                                        {{ $request->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <span class="status-badge 
                                                    @if($request->status === 'pending') status-pending
                                                    @elseif($request->status === 'accepted') status-accepted
                                                    @elseif($request->status === 'completed') status-completed
                                                    @elseif($request->status === 'rejected') status-rejected
                                                    @else status-pending
                                                    @endif">
                                                    @if($request->status === 'pending')
                                                        <i class="fas fa-clock mr-1"></i> En attente
                                                    @elseif($request->status === 'accepted')
                                                        <i class="fas fa-check-circle mr-1"></i> Acceptée
                                                    @elseif($request->status === 'completed')
                                                        <i class="fas fa-flag-checkered mr-1"></i> Terminée
                                                    @elseif($request->status === 'rejected')
                                                        <i class="fas fa-times-circle mr-1"></i> Refusée
                                                    @else
                                                        {{ ucfirst($request->status) }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Avis reçus -->
                    @if($reviews->count() > 0)
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Avis reçus</h3>
                            <div class="space-y-4">
                                @foreach($reviews as $review)
                                    <div class="review-card border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            @if($review->prestataire->photo)
                                                <img class="h-12 w-12 rounded-full object-cover shadow-md" src="{{ asset('storage/' . $review->prestataire->photo) }}" alt="{{ $review->prestataire->user->name ?? 'Prestataire' }}">
                                            @else
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center shadow-md">
                                                    <span class="text-sm font-medium text-gray-700">{{ $review->prestataire->user->name ? substr($review->prestataire->user->name, 0, 1) : 'P' }}</span>
                                                </div>
                                            @endif
                                            <div class="ml-4 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="text-sm font-bold text-gray-900">{{ $review->prestataire->user->name ?? 'Prestataire' }}</h4>
                                                    <div class="flex items-center">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @endfor
                                                        <span class="ml-1 text-sm font-bold text-gray-700">{{ $review->rating }}/5</span>
                                                    </div>
                                                </div>
                                                <p class="text-sm text-gray-700 mt-2">{{ $review->comment }}</p>
                                                <p class="text-xs text-gray-600 mt-2 flex items-center">
                                                    <i class="far fa-clock mr-1"></i>
                                                    {{ $review->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Statistiques -->
                    <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Statistiques</h3>
                        <dl class="space-y-4">
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="p-2 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 shadow-md">
                                    <i class="fas fa-clipboard-list text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <dt class="text-sm font-semibold text-gray-700">Demandes publiées</dt>
                                    <dd class="text-xl font-bold text-gray-900">{{ $stats['total_requests'] }}</dd>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="p-2 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 shadow-md">
                                    <i class="fas fa-flag-checkered text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <dt class="text-sm font-semibold text-gray-700">Projets terminés</dt>
                                    <dd class="text-xl font-bold text-gray-900">{{ $stats['completed_requests'] }}</dd>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="p-2 rounded-full bg-gradient-to-br from-yellow-100 to-yellow-200 text-yellow-600 shadow-md">
                                    <i class="fas fa-star text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <dt class="text-sm font-semibold text-gray-700">Note moyenne</dt>
                                    <dd class="text-xl font-bold text-gray-900">
                                        @if($stats['average_rating'])
                                            {{ number_format($stats['average_rating'], 1) }}/5
                                            <div class="flex items-center mt-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-4 w-4 {{ $i <= round($stats['average_rating']) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                        @else
                                            <span class="text-gray-500 text-sm">Aucun avis</span>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="p-2 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 shadow-md">
                                    <i class="fas fa-heart text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <dt class="text-sm font-semibold text-gray-700">Prestataires suivis</dt>
                                    <dd class="text-xl font-bold text-gray-900">{{ $stats['following_count'] }}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                    
                    <!-- Contact -->
                    @auth
                        @if(auth()->user()->role === 'prestataire' && auth()->id() !== $client->user_id)
                            <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Contact</h3>
                                <div class="space-y-3">
                                    <a href="{{ route('messaging.start-conversation-from-request', $client->id) }}" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent shadow-sm text-base font-bold rounded-lg text-white bg-gray-600 hover:bg-gray-700 transition duration-200 hover:shadow-lg">
                                        <i class="fas fa-envelope mr-2"></i>
                                        Envoyer un message
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endauth
                    
                    <!-- Informations de contact -->
                    @if($client->phone || $client->location)
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Informations</h3>
                            <dl class="space-y-3">
                                @if($client->location)
                                    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                                        <div class="p-2 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 shadow-md">
                                            <i class="fas fa-map-marker-alt text-sm"></i>
                                        </div>
                                        <div class="ml-3">
                                            <dt class="text-sm font-semibold text-gray-700">Localisation</dt>
                                            <dd class="text-sm text-gray-900 mt-1">{{ $client->location }}</dd>
                                        </div>
                                    </div>
                                @endif
                                @if($client->phone && (auth()->check() && auth()->user()->role === 'prestataire'))
                                    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                                        <div class="p-2 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 shadow-md">
                                            <i class="fas fa-phone text-sm"></i>
                                        </div>
                                        <div class="ml-3">
                                            <dt class="text-sm font-semibold text-gray-700">Téléphone</dt>
                                            <dd class="text-sm text-gray-900 mt-1">{{ $client->phone }}</dd>
                                        </div>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
@endsection