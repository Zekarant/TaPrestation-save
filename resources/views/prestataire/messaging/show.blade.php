@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-6">
    <header>
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center">
                    <a href="{{ route('messaging.index') }}" class="mr-3 text-gray-500 hover:text-gray-700">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="flex items-center">
                        @if($otherUser->client && $otherUser->client->avatar)
                            <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-full mr-2 sm:mr-3" src="{{ Storage::url($otherUser->client->avatar) }}" alt="{{ $otherUser->name }}">
                        @else
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-gray-300 flex items-center justify-center mr-2 sm:mr-3">
                                <span class="text-xs sm:text-sm font-medium text-gray-700">{{ substr($otherUser->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">{{ $otherUser->name }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600">Client</p>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2 sm:space-x-3">
                    @if($otherUser->client)
                        <div class="text-right text-xs sm:text-sm text-gray-600">
                            <p>Membre depuis {{ $otherUser->created_at->format('M Y') }}</p>
                            @if($otherUser->client->location)
                                <p><i class="fas fa-map-marker-alt mr-1"></i>{{ $otherUser->client->location }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="px-2 py-3 sm:px-4 sm:py-4">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <!-- Messages -->
                    <div class="h-64 sm:h-80 md:h-96 overflow-y-auto p-3 sm:p-4 space-y-3" id="messages-container">
                        @forelse($messages as $message)
                            <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[80%] sm:max-w-xs lg:max-w-md px-3 py-2 rounded-lg {{ $message->sender_id == auth()->id() ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-900' }}">
                                    @if($message->clientRequest)
                                        <div class="text-xs {{ $message->sender_id == auth()->id() ? 'text-indigo-200' : 'text-gray-600' }} mb-1">
                                            <i class="fas fa-file-alt mr-1"></i>
                                            Concernant: {{ $message->clientRequest->title }}
                                            <!-- Budget supprimé pour des raisons de confidentialité -->
                                        </div>
                                    @endif
                                    <p class="text-sm">{{ $message->content }}</p>
                                    <p class="text-xs {{ $message->sender_id == auth()->id() ? 'text-indigo-200' : 'text-gray-500' }} mt-1">
                                        {{ $message->created_at->format('d/m/Y H:i') }}
                                        @if($message->sender_id == auth()->id() && $message->read_at)
                                            <span class="ml-1">Lu</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 sm:py-8">
                                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <h3 class="mt-1 sm:mt-2 text-sm font-medium text-gray-900">Aucun message</h3>
                                <p class="mt-1 text-xs sm:text-sm text-gray-500">Commencez la conversation en envoyant un message.</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Actions rapides -->
                    @if($clientRequests->count() > 0)
                        <div class="border-t border-gray-200 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs sm:text-sm font-medium text-gray-900">Demandes de ce client</h4>
                                <span class="text-xs text-gray-500">{{ $clientRequests->count() }} demande(s)</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1 sm:gap-2">
                                @foreach($clientRequests->take(3) as $request)
                                    <a href="{{ route('prestataire.requests.show', $request->id) }}" class="inline-flex items-center px-2 py-1 sm:px-2.5 sm:py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500">
                                        {{ Str::limit($request->title, 20) }}
                                        <!-- Budget supprimé pour des raisons de confidentialité -->
                                    </a>
                                @endforeach
                                @if($clientRequests->count() > 3)
                                    <span class="inline-flex items-center px-2 py-1 sm:px-2.5 sm:py-1.5 text-xs text-gray-500">
                                        +{{ $clientRequests->count() - 3 }} autres
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Formulaire d'envoi de message -->
                    <div class="border-t border-gray-200 p-3 sm:p-4">
                        @if ($errors->any())
                            <div class="mb-3 sm:mb-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2 sm:px-4 sm:py-3 rounded relative text-xs sm:text-sm">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if (session('success'))
                            <div class="mb-3 sm:mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 sm:px-4 sm:py-3 rounded relative text-xs sm:text-sm">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        <form action="{{ route('messaging.store', $otherUser->id) }}" method="POST">
                            @csrf
                            
                            <!-- Sélection de la demande (optionnel) -->
                            @if($clientRequests->count() > 0)
                                <div class="mb-2 sm:mb-3">
                                    <label for="client_request_id" class="block text-xs sm:text-sm font-medium text-gray-700">Répondre à une demande spécifique (optionnel)</label>
                                    <select id="client_request_id" name="client_request_id" class="mt-1 block w-full py-1.5 px-2 sm:py-2 sm:px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm">
                                        <option value="">Message général</option>
                                        @foreach($clientRequests as $request)
                                            <option value="{{ $request->id }}">{{ $request->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            
                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                <div class="flex-1">
                                    <label for="content" class="sr-only">Message</label>
                                    <textarea id="content" name="content" rows="2" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full text-xs sm:text-sm border-gray-300 rounded-md" placeholder="Tapez votre message..." required>{{ old('content') }}</textarea>
                                </div>
                                <div class="flex-shrink-0 flex flex-col sm:flex-row gap-2">
                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-1 sm:mr-2 h-4 w-4 sm:h-5 sm:w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        Envoyer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Auto-scroll vers le bas des messages
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
});

// Modèles de messages
function insertTemplate(type) {
    const textarea = document.getElementById('content');
    let template = '';
    

    
    textarea.value = template;
    textarea.focus();
}
</script>
@endsection