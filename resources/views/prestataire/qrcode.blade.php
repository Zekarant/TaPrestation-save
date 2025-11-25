@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6 md:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête responsive -->
            <div class="mb-6 md:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">Mon QR Code</h1>
                <p class="text-sm sm:text-base md:text-lg text-gray-700 px-2">Partagez votre profil facilement avec votre QR code personnalisé</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <a href="{{ route('prestataire.dashboard') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200 p-2 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-arrow-left text-lg sm:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900">QR Code de profil</h2>
                            <p class="text-sm sm:text-base text-gray-700">Scannez ce code pour accéder à votre profil public</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
                <!-- QR Code Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Votre QR Code</h2>
                    <div class="flex flex-col items-center justify-center text-center p-4 sm:p-6 bg-gray-50 rounded-lg">
                        <div id="qrcode" class="p-3 sm:p-4 bg-white border border-gray-300 rounded-lg shadow-md mb-4 sm:mb-0"></div>
                        <button id="download-btn" class="mt-4 sm:mt-6 bg-gray-600 hover:bg-gray-700 text-white px-4 sm:px-6 md:px-8 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl w-full sm:w-auto text-sm sm:text-base">
                            <i class="fas fa-download mr-2"></i><span class="hidden sm:inline">Télécharger le QR Code (PNG)</span><span class="sm:hidden">Télécharger</span>
                        </button>
                    </div>
                </div>

                <!-- Information Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Partage et utilisation</h2>
                    <div class="space-y-4 sm:space-y-6">
                        <p class="text-sm sm:text-base text-gray-600">
                            Partagez ce QR code sur vos supports de communication, cartes de visite ou réseaux sociaux pour permettre un accès rapide à votre profil.
                        </p>

                        <div>
                            <label for="profile_url" class="block text-sm font-medium text-gray-700 mb-2">Lien du profil public</label>
                            <div class="relative">
                                <input type="text" id="profile_url" readonly value="{{ $url }}" class="w-full px-3 py-2 pr-12 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 bg-gray-50 text-gray-800 text-sm">
                                <button onclick="copyToClipboard('{{ $url }}')" class="absolute inset-y-0 right-0 px-3 sm:px-4 flex items-center bg-gray-200 hover:bg-gray-300 rounded-r-md text-gray-600 transition duration-200">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <button onclick="copyToClipboard('{{ $url }}')" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-medium text-sm sm:text-base">
                            <i class="fas fa-copy mr-2"></i><span class="hidden sm:inline">Copier le lien du profil</span><span class="sm:hidden">Copier le lien</span>
                        </button>
                        <div id="copy-feedback" class="text-center text-green-500 mt-2 h-4 text-sm"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const url = "{{ $url }}";
    const qrcodeContainer = document.getElementById('qrcode');

    const qrcode = new QRCode(qrcodeContainer, {
        text: url,
        width: 300,
        height: 300,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });

    document.getElementById('download-btn').addEventListener('click', function() {
        const img = qrcodeContainer.getElementsByTagName('img')[0];
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'qrcode-profil.png';
        a.click();
    });

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            const feedback = document.getElementById('copy-feedback');
            feedback.textContent = 'Lien copié !';
            setTimeout(() => { feedback.textContent = '' }, 3000);
        }, function(err) {
            const feedback = document.getElementById('copy-feedback');
            feedback.textContent = 'Erreur de copie.';
            feedback.classList.remove('text-green-500');
            feedback.classList.add('text-red-500');
            setTimeout(() => {
                 feedback.textContent = '';
                 feedback.classList.remove('text-red-500');
                 feedback.classList.add('text-green-500');
            }, 3000);
        });
    }
</script>
@endpush
@endsection