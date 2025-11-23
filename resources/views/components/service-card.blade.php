<div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
    <a href="{{ route('services.show', $service) }}" class="block">
        @if($service->coverImage)
            <img src="{{ Storage::url($service->coverImage->image_path) }}" alt="{{ $service->title }}" class="w-full h-48 object-cover">
        @else
            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                <span class="text-gray-500">Pas d'image</span>
            </div>
        @endif
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $service->title }}</h3>
            <p class="text-sm text-gray-600 mt-1 truncate">{{ $service->prestataire->user->name }}</p>
            <div class="mt-4 flex items-center justify-between">
                <p class="text-lg font-bold text-blue-600">{{ number_format($service->price, 2) }}€</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.368-2.448a1 1 0 00-1.175 0l-3.368 2.448c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z" />
                    </svg>
                    <span>{{ number_format($service->reviews->avg('rating'), 1) ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </a>
</div>