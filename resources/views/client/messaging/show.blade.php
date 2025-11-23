@extends('layouts.app')

@section('content')
<div class="py-10">
    <header>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('messaging.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="flex items-center">
                        @if($otherUser->prestataire && $otherUser->prestataire->photo)
                            <img class="h-10 w-10 rounded-full mr-3" src="{{ Storage::url($otherUser->prestataire->photo) }}" alt="{{ $otherUser->name }}">
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                <span class="text-sm font-medium text-gray-700">{{ substr($otherUser->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-2xl font-bold leading-tight text-gray-900">{{ $otherUser->name }}</h1>
                            <p class="text-sm text-gray-600">Prestataire</p>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('prestataires.show', $otherUser->prestataire->id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Voir le profil
                    </a>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <!-- Messages -->
                    <div class="h-96 overflow-y-auto p-4 space-y-4" id="messages-container">
                        @forelse($messages as $message)
                            <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message->sender_id == auth()->id() ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-900' }}">

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
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun message</h3>
                                <p class="mt-1 text-sm text-gray-500">Commencez la conversation en envoyant un message.</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Formulaire d'envoi de message -->
                    <div class="border-t border-gray-200 p-4">
                        @if ($errors->any())
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                                <ul class="list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if (session('success'))
                            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        <form action="{{ route('messaging.store', $otherUser->id) }}" method="POST">
                            @csrf
                            
                            

                            
                            <div class="flex space-x-3">
                                <div class="flex-1">
                                    <label for="content" class="sr-only">Message</label>
                                    <textarea id="content" name="content" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Tapez votre message..." required>{{ old('content', $prefilledMessage ?? '') }}</textarea>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
</script>
@endsection