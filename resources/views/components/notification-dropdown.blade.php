@php
    $unreadCount = Auth::user()->notifications()->whereNull('read_at')->count();
    $recentNotifications = Auth::user()->notifications()
        ->whereNull('read_at')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
@endphp

<div class="relative" x-data="notificationDropdown()">
    <!-- Bouton de notification -->
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a50.002 50.002 0 00-2.5-2.5V8a6 6 0 10-12 0v2.5c-1 1-2.5 2.5-2.5 2.5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Badge de compteur -->
        @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="notification-badge">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
        @endif
    </button>

    <!-- Dropdown des notifications -->
    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
        <div class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Tout marquer comme lu
                    </button>
                </form>
                @endif
            </div>

            @if($recentNotifications->isEmpty())
                <div class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a50.002 50.002 0 00-2.5-2.5V8a6 6 0 10-12 0v2.5c-1 1-2.5 2.5-2.5 2.5L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Aucune nouvelle notification</p>
                </div>
            @else
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach($recentNotifications as $notification)
                        @php
                        // Determine notification type and apply appropriate icon/colors
                        $isEquipmentNotification = strpos($notification->type, 'Equipment') !== false;
                        $isServiceNotification = strpos($notification->type, 'Booking') !== false;
                        
                        if ($isEquipmentNotification) {
                            // Equipment notifications - green color and tools icon
                            $iconClass = 'fa-tools';
                            $colorClass = 'text-green-500';
                            $bgClass = 'bg-green-100';
                        } elseif ($isServiceNotification) {
                            // Service/Booking notifications - blue color and cogs icon
                            $iconClass = 'fa-cogs';
                            $colorClass = 'text-blue-500';
                            $bgClass = 'bg-blue-100';
                        } else {
                            // Default styling for other notifications
                            $iconClass = 'fa-bell';
                            $colorClass = 'text-blue-600';
                            $bgClass = 'bg-blue-100';
                        }
                        
                        $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                        $title = $data['title'] ?? 'Notification';
                        $message = $data['message'] ?? '';
                        $url = $data['url'] ?? route('notifications.index');
                        @endphp
                        
                        <!-- Make the entire notification clickable -->
                        <a href="{{ $url }}" class="block no-underline" @click="open = false">
                            <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                                <!-- Icône selon le type -->
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 {{ $bgClass }} rounded-full flex items-center justify-center">
                                        <i class="fas {{ $iconClass }} {{ $colorClass }} text-sm"></i>
                                    </div>
                                </div>
                                
                                <!-- Contenu de la notification -->
                                <div class="flex-1 min-w-0">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $title }}</p>
                                        <p class="text-sm text-gray-500 line-clamp-2">{{ $message }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('notifications.index') }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800">
                        Voir toutes les notifications
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function notificationDropdown() {
    return {
        open: false
    }
}
</script>