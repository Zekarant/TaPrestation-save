{{-- 
    Composant pour afficher la note d'un client
    Usage: @include('components.client-rating', ['user' => $client->user])
--}}
@if($user && $user->isClient())
    @php
        $rating = $user->client_rating;
        $reviewsCount = $user->client_reviews_count;
        $wouldWorkAgain = $user->would_work_again_percentage;
    @endphp
    
    <div class="client-rating-badge inline-flex items-center gap-1.5 text-sm">
        @if($rating)
            <span class="flex items-center gap-0.5">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= floor($rating))
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                    @elseif($i - 0.5 <= $rating)
                        <i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>
                    @else
                        <i class="far fa-star text-gray-300 text-xs"></i>
                    @endif
                @endfor
            </span>
            <span class="font-semibold text-gray-700">{{ number_format($rating, 1) }}</span>
            <span class="text-gray-400 text-xs">({{ $reviewsCount }})</span>
            
            @if($wouldWorkAgain !== null && $wouldWorkAgain >= 80)
                <span class="ml-1 px-1.5 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">
                    <i class="fas fa-thumbs-up mr-0.5"></i>{{ $wouldWorkAgain }}%
                </span>
            @endif
        @else
            <span class="text-gray-400 text-xs italic">
                <i class="far fa-star mr-1"></i>Nouveau client
            </span>
        @endif
        
        @if($reviewsCount > 0)
            <a href="{{ route('client.reviews', $user) }}" class="text-blue-500 hover:text-blue-700 text-xs ml-1">
                Voir avis
            </a>
        @endif
    </div>
@endif
