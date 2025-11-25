@extends('layouts.admin-modern')

@section('title', 'Détails de la Notification - Administration')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                            Détails de la Notification
                        </h1>
                        <p class="text-base sm:text-lg text-blue-700">
                            Consultez les informations détaillées de cette notification.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        <a href="{{ route('administrateur.notifications.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Retour à la liste
                        </a>
                        @if(!$notification->read_at)
                        <form action="{{ route('administrateur.notifications.markAsRead', $notification->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                                <i class="fas fa-check mr-2"></i>
                                Marquer comme lue
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('administrateur.notifications.destroy', $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                                <i class="fas fa-trash mr-2"></i>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 pb-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Détails de la notification -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-bell mr-3"></i>
                            Contenu de la Notification
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                        @endphp
                        
                        <!-- Titre -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $data['title'] ?? 'Notification sans titre' }}
                                </h3>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <p class="text-gray-800 leading-relaxed">
                                    {{ $data['message'] ?? 'Aucun message disponible' }}
                                </p>
                            </div>
                        </div>

                        <!-- URL d'action (si disponible) -->
                        @if(isset($data['url']) && $data['url'])
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lien d'action</label>
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <a href="{{ $data['url'] }}" class="text-blue-600 hover:text-blue-800 underline break-all">
                                    {{ $data['url'] }}
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Données brutes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Données complètes</label>
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <pre class="text-sm text-gray-700 whitespace-pre-wrap overflow-x-auto">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations sur la notification -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-info-circle mr-3"></i>
                            Informations
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Statut -->
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Statut</span>
                            @if($notification->read_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Lue
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Non lue
                                </span>
                            @endif
                        </div>

                        <!-- Type -->
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Type</span>
                            <span class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">
                                {{ class_basename($notification->type) }}
                            </span>
                        </div>

                        <!-- Destinataire -->
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Destinataire</span>
                            <div class="text-right">
                                @if($notification->notifiable)
                                    <div class="text-sm font-medium text-gray-900">{{ $notification->notifiable->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $notification->notifiable->email }}</div>
                                @else
                                    <span class="text-sm text-gray-500">Utilisateur supprimé</span>
                                @endif
                            </div>
                        </div>

                        <!-- Date de création -->
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Créée le</span>
                            <div class="text-right">
                                <div class="text-sm text-gray-900">{{ $notification->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $notification->created_at->format('H:i:s') }}</div>
                            </div>
                        </div>

                        <!-- Date de lecture -->
                        @if($notification->read_at)
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Lue le</span>
                            <div class="text-right">
                                <div class="text-sm text-gray-900">{{ $notification->read_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $notification->read_at->format('H:i:s') }}</div>
                            </div>
                        </div>
                        @endif

                        <!-- ID -->
                        <div class="flex items-center justify-between py-3">
                            <span class="text-sm font-medium text-gray-700">ID</span>
                            <span class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded">
                                {{ $notification->id }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden mt-6">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-bolt mr-3"></i>
                            Actions Rapides
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($notification->notifiable)
                        <a href="{{ route('administrateur.users.show', $notification->notifiable->id) }}" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                            <i class="fas fa-user mr-2"></i>
                            Voir le profil utilisateur
                        </a>
                        @endif
                        
                        <a href="{{ route('administrateur.notifications.index') }}?type={{ urlencode($notification->type) }}" class="w-full bg-green-50 hover:bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                            <i class="fas fa-filter mr-2"></i>
                            Voir notifications similaires
                        </a>
                        
                        @if(isset($data['url']) && $data['url'])
                        <a href="{{ $data['url'] }}" class="w-full bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Aller au lien d'action
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh si la notification n'est pas lue
    @if(!$notification->read_at)
    setTimeout(function() {
        location.reload();
    }, 30000); // Refresh toutes les 30 secondes
    @endif
</script>
@endpush