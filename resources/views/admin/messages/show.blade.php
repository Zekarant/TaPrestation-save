@extends('layouts.admin-modern')

@section('title', 'Détails du Message - Administration')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <!-- Header -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8">
                <a href="{{ route('administrateur.messages.index') }}" class="inline-flex items-center bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 px-4 rounded-lg transition duration-200 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Message Details - Colonne principale -->
            <div class="lg:col-span-2">
                <!-- Message Info Card -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 mb-6">
                    <div class="px-4 sm:px-6 py-4 border-b border-blue-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h2 class="text-xl font-bold text-blue-900">Message #{{ $message->id }}</h2>
                        <div class="flex gap-2">
                            @if($message->status === 'pending')
                                <form action="{{ route('administrateur.messages.moderate', $message->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                        <i class="fas fa-check mr-2"></i> Approuver
                                    </button>
                                </form>
                                <form action="{{ route('administrateur.messages.moderate', $message->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="hide">
                                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                        <i class="fas fa-eye-slash mr-2"></i> Masquer
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('administrateur.messages.destroy', $message->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                    <i class="fas fa-trash mr-2"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <!-- Status Badge -->
                        <div class="mb-4">
                            @if($message->status === 'approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Approuvé
                                </span>
                            @elseif($message->status === 'hidden')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-eye-slash mr-1"></i> Masqué
                                </span>
                            @elseif($message->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-clock mr-1"></i> En attente
                                </span>
                            @endif
                            
                            @if($message->is_reported)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 ml-2">
                                    <i class="fas fa-flag mr-1"></i> Signalé
                                </span>
                            @endif
                        </div>

                        <!-- Message Content -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Contenu du message :</h4>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $message->content }}</p>
                        </div>

                        <!-- Message Metadata -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700">Date d'envoi :</span>
                                <span class="text-gray-600">{{ $message->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700">Statut de lecture :</span>
                                @if($message->read_at)
                                    <span class="text-green-600">Lu le {{ $message->read_at->format('d/m/Y à H:i') }}</span>
                                @else
                                    <span class="text-red-600">Non lu</span>
                                @endif
                            </div>
                            @if($message->moderated_at)
                            <div>
                                <span class="font-semibold text-gray-700">Modéré le :</span>
                                <span class="text-gray-600">{{ $message->moderated_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            @endif
                            @if($message->moderation_reason)
                            <div class="sm:col-span-2">
                                <span class="font-semibold text-gray-700">Raison de modération :</span>
                                <span class="text-gray-600">{{ $message->moderation_reason }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Conversation Thread -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200">
                    <div class="px-4 sm:px-6 py-4 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900">Conversation complète</h3>
                        <p class="text-sm text-gray-600">{{ $conversation->count() }} message(s) dans cette conversation</p>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            @foreach($conversation as $msg)
                                <div class="flex {{ $msg->sender_id === $message->sender_id ? 'justify-start' : 'justify-end' }}">
                                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $msg->sender_id === $message->sender_id ? 'bg-gray-100' : 'bg-blue-100' }}">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-semibold {{ $msg->sender_id === $message->sender_id ? 'text-gray-700' : 'text-blue-700' }}">
                                                {{ $msg->sender->name }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $msg->created_at->format('H:i') }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-900">{{ $msg->content }}</p>
                                        @if($msg->id === $message->id)
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-eye mr-1"></i> Message actuel
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Informations des utilisateurs -->
            <div class="lg:col-span-1">
                <!-- Sender Info -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 mb-6">
                    <div class="px-4 sm:px-6 py-4 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900">Expéditeur</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        @if($message->sender)
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(substr($message->sender->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $message->sender->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $message->sender->email }}</p>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                        {{ ucfirst($message->sender->role) }}
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="font-semibold text-gray-700">Inscrit le :</span>
                                    <span class="text-gray-600">{{ $message->sender->created_at->format('d/m/Y') }}</span>
                                </div>
                                @if($message->sender->last_seen_at)
                                <div>
                                    <span class="font-semibold text-gray-700">Dernière connexion :</span>
                                    <span class="text-gray-600">{{ $message->sender->last_seen_at->diffForHumans() }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('administrateur.users.show', $message->sender->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center w-full">
                                    <i class="fas fa-user mr-2"></i> Voir le profil
                                </a>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">Expéditeur non disponible ou supprimé.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Receiver Info -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200">
                    <div class="px-4 sm:px-6 py-4 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900">Destinataire</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        @if($message->receiver)
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(substr($message->receiver->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $message->receiver->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $message->receiver->email }}</p>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                        {{ ucfirst($message->receiver->role) }}
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="font-semibold text-gray-700">Inscrit le :</span>
                                    <span class="text-gray-600">{{ $message->receiver->created_at->format('d/m/Y') }}</span>
                                </div>
                                @if($message->receiver->last_seen_at)
                                <div>
                                    <span class="font-semibold text-gray-700">Dernière connexion :</span>
                                    <span class="text-gray-600">{{ $message->receiver->last_seen_at->diffForHumans() }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('administrateur.users.show', $message->receiver->id) }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center w-full">
                                    <i class="fas fa-user mr-2"></i> Voir le profil
                                </a>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">Destinataire non disponible ou supprimé.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection