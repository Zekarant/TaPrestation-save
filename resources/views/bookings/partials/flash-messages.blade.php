@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 p-4 rounded-xl shadow-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-green-800 font-medium text-base">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 p-4 rounded-xl shadow-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-red-800 font-medium text-base">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 p-4 rounded-xl shadow-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-red-800 font-bold text-lg mb-2">Erreurs détectées :</h3>
                <ul class="list-disc list-inside text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-base">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif