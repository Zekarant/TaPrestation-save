@props(['prestataire'])

<div class="bg-white rounded-lg shadow-sm border p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-qrcode mr-2 text-gray-600"></i>
        QR Code Profil
    </h3>
    
    <div class="text-center">
        <!-- Container pour le QR Code -->
        <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg mb-4">
            <div id="qrcode-{{ $prestataire->id }}" class="flex items-center justify-center w-48 h-48">
                <!-- Le QR code sera généré ici par JavaScript -->
            </div>
        </div>
        
        <!-- URL du profil -->
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Lien du profil :</p>
            <div class="flex items-center justify-center bg-gray-50 rounded-lg p-3">
                <input type="text" 
                       id="profile-url-{{ $prestataire->id }}"
                       value="{{ $prestataire->generateQrCode() }}" 
                       readonly 
                       class="flex-1 bg-transparent border-none text-sm text-gray-700 focus:outline-none text-center">
                <button onclick="copyProfileUrl({{ $prestataire->id }})" 
                        class="ml-2 p-2 text-gray-500 hover:text-gray-700 transition-colors"
                        title="Copier le lien">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-2 justify-center">
            <button onclick="downloadQRCode({{ $prestataire->id }})" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-download mr-2"></i>
                Télécharger QR Code
            </button>
            <button onclick="shareProfile({{ $prestataire->id }})" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-share-alt mr-2"></i>
                Partager
            </button>
        </div>
        
        <!-- Instructions -->
        <div class="mt-4 text-xs text-gray-500">
            <p>Scannez ce QR code pour accéder directement au profil</p>
        </div>
    </div>
</div>

<!-- Toast notification -->
<div id="copy-toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    <i class="fas fa-check mr-2"></i>
    Lien copié dans le presse-papiers !
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
// Générer le QR Code au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    generateQRCode({{ $prestataire->id }});
});

function generateQRCode(prestataireId) {
    const url = document.getElementById(`profile-url-${prestataireId}`).value;
    const qrContainer = document.getElementById(`qrcode-${prestataireId}`);
    
    // Vider le container
    qrContainer.innerHTML = '';
    
    // Générer le QR code
    QRCode.toCanvas(qrContainer, url, {
        width: 192,
        height: 192,
        margin: 2,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
    }, function (error) {
        if (error) {
            console.error('Erreur lors de la génération du QR code:', error);
            qrContainer.innerHTML = '<div class="text-red-500 text-sm">Erreur lors de la génération du QR code</div>';
        }
    });
}

function copyProfileUrl(prestataireId) {
    const urlInput = document.getElementById(`profile-url-${prestataireId}`);
    
    // Sélectionner et copier le texte
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // Pour mobile
    
    try {
        document.execCommand('copy');
        showToast();
    } catch (err) {
        // Fallback pour les navigateurs modernes
        navigator.clipboard.writeText(urlInput.value).then(() => {
            showToast();
        }).catch(err => {
            console.error('Erreur lors de la copie:', err);
        });
    }
}

function downloadQRCode(prestataireId) {
    const canvas = document.querySelector(`#qrcode-${prestataireId} canvas`);
    if (canvas) {
        const link = document.createElement('a');
        link.download = `qrcode-profil-prestataire-${prestataireId}.png`;
        link.href = canvas.toDataURL();
        link.click();
    }
}

function shareProfile(prestataireId) {
    const url = document.getElementById(`profile-url-${prestataireId}`).value;
    
    if (navigator.share) {
        navigator.share({
            title: 'Profil Prestataire - TaPrestation',
            text: 'Découvrez ce prestataire sur TaPrestation',
            url: url
        }).catch(err => console.log('Erreur lors du partage:', err));
    } else {
        // Fallback: copier le lien
        copyProfileUrl(prestataireId);
    }
}

function showToast() {
    const toast = document.getElementById('copy-toast');
    toast.classList.remove('translate-x-full');
    
    setTimeout(() => {
        toast.classList.add('translate-x-full');
    }, 3000);
}
</script>