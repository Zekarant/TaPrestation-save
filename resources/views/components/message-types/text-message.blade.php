@props(['message'])

<div class="message-bubble">
    <div class="message-content">
        {!! nl2br(e($message->content)) !!}
    </div>
    
    @if($message->edited_at)
        <span class="text-xs opacity-60 italic">modifié</span>
    @endif
    
    <div class="flex items-center justify-between mt-1">
        <span class="text-xs opacity-75">{{ $message->created_at->format('H:i') }}</span>
        
        @if($message->sender_id === auth()->id())
            <div class="flex items-center space-x-1">
                @if($message->read_at)
                    <i class="fas fa-check-double text-blue-400" title="Lu"></i>
                @else
                    <i class="fas fa-check text-gray-400" title="Envoyé"></i>
                @endif
            </div>
        @endif
    </div>
</div>

@if($message->sender_id === auth()->id())
    <!-- Menu contextuel pour les messages envoyés -->
    <div class="message-menu hidden absolute right-0 top-0 bg-white rounded-lg shadow-lg border z-10">
        <button type="button" class="edit-message block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100" data-message-id="{{ $message->id }}">
            <i class="fas fa-edit mr-2"></i>Modifier
        </button>
        <button type="button" class="delete-message block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50" data-message-id="{{ $message->id }}">
            <i class="fas fa-trash mr-2"></i>Supprimer
        </button>
    </div>
@endif