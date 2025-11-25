@props(['message'])

@php
    $fileExtension = pathinfo($message->file_name, PATHINFO_EXTENSION);
    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    $isVideo = in_array(strtolower($fileExtension), ['mp4', 'webm', 'ogg', 'avi', 'mov']);
    $isPdf = strtolower($fileExtension) === 'pdf';
    $isDocument = in_array(strtolower($fileExtension), ['doc', 'docx', 'txt', 'rtf']);
    $isSpreadsheet = in_array(strtolower($fileExtension), ['xls', 'xlsx', 'csv']);
    $isPresentation = in_array(strtolower($fileExtension), ['ppt', 'pptx']);
    $isArchive = in_array(strtolower($fileExtension), ['zip', 'rar', '7z', 'tar', 'gz']);
    
    $fileIcon = 'fas fa-file';
    $iconColor = 'text-gray-600';
    $bgColor = 'bg-gray-100';
    
    if ($isImage) {
        $fileIcon = 'fas fa-image';
        $iconColor = 'text-green-600';
        $bgColor = 'bg-green-100';
    } elseif ($isVideo) {
        $fileIcon = 'fas fa-video';
        $iconColor = 'text-red-600';
        $bgColor = 'bg-red-100';
    } elseif ($isPdf) {
        $fileIcon = 'fas fa-file-pdf';
        $iconColor = 'text-red-600';
        $bgColor = 'bg-red-100';
    } elseif ($isDocument) {
        $fileIcon = 'fas fa-file-word';
        $iconColor = 'text-blue-600';
        $bgColor = 'bg-blue-100';
    } elseif ($isSpreadsheet) {
        $fileIcon = 'fas fa-file-excel';
        $iconColor = 'text-green-600';
        $bgColor = 'bg-green-100';
    } elseif ($isPresentation) {
        $fileIcon = 'fas fa-file-powerpoint';
        $iconColor = 'text-orange-600';
        $bgColor = 'bg-orange-100';
    } elseif ($isArchive) {
        $fileIcon = 'fas fa-file-archive';
        $iconColor = 'text-purple-600';
        $bgColor = 'bg-purple-100';
    }
    
    $fileSize = $message->file_size;
    $fileSizeFormatted = '';
    if ($fileSize < 1024) {
        $fileSizeFormatted = $fileSize . ' B';
    } elseif ($fileSize < 1024 * 1024) {
        $fileSizeFormatted = round($fileSize / 1024, 1) . ' KB';
    } elseif ($fileSize < 1024 * 1024 * 1024) {
        $fileSizeFormatted = round($fileSize / (1024 * 1024), 1) . ' MB';
    } else {
        $fileSizeFormatted = round($fileSize / (1024 * 1024 * 1024), 1) . ' GB';
    }
@endphp

<div class="message-bubble">
    @if($isImage)
        <!-- Prévisualisation d'image -->
        <div class="image-preview mb-2">
            <img src="/storage/{{ $message->file_path }}" 
                 alt="{{ $message->file_name }}"
                 class="max-w-xs max-h-64 rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                 onclick="openImageModal('{{ $message->file_path }}', '{{ $message->file_name }}')">
        </div>
    @elseif($isVideo)
        <!-- Prévisualisation vidéo -->
        <div class="video-preview mb-2">
            <video controls class="max-w-xs max-h-64 rounded-lg">
                <source src="/storage/{{ $message->file_path }}" type="video/{{ $fileExtension }}">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>
        </div>
    @endif
    
    <!-- Informations du fichier -->
    <div class="file-attachment flex items-center space-x-3 p-3 {{ $isImage || $isVideo ? 'bg-gray-50' : 'bg-white' }} rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
        <div class="w-12 h-12 {{ $bgColor }} rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="{{ $fileIcon }} {{ $iconColor }} text-lg"></i>
        </div>
        
        <div class="flex-1 min-w-0">
            <p class="font-medium text-sm text-gray-900 truncate" title="{{ $message->file_name }}">
                {{ $message->file_name }}
            </p>
            <div class="flex items-center space-x-2 text-xs text-gray-500">
                <span>{{ $fileSizeFormatted }}</span>
                <span>•</span>
                <span class="uppercase">{{ $fileExtension }}</span>
                @if($message->created_at)
                    <span>•</span>
                    <span>{{ $message->created_at->format('H:i') }}</span>
                @endif
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center space-x-2 flex-shrink-0">
            @if($isImage || $isVideo || $isPdf)
                <!-- Bouton de prévisualisation -->
                <button type="button" 
                        onclick="previewFile('{{ $message->file_path }}', '{{ $message->file_name }}', '{{ $fileExtension }}')"
                        class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Prévisualiser">
                    <i class="fas fa-eye"></i>
                </button>
            @endif
            
            <!-- Bouton de téléchargement -->
            <a href="/storage/{{ $message->file_path }}" 
               download="{{ $message->file_name }}"
               class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
               title="Télécharger">
                <i class="fas fa-download"></i>
            </a>
            
            @if($message->sender_id === auth()->id())
                <!-- Menu contextuel -->
                <div class="relative">
                    <button type="button" 
                            class="file-menu-btn p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors"
                            onclick="toggleFileMenu(this)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="file-menu hidden absolute right-0 top-full mt-1 bg-white rounded-lg shadow-lg border z-10 min-w-32">
                        <button type="button" 
                                class="delete-file block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg"
                                data-message-id="{{ $message->id }}"
                                onclick="deleteFileMessage({{ $message->id }})">
                            <i class="fas fa-trash mr-2"></i>Supprimer
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Statut de lecture -->
    @if($message->sender_id === auth()->id())
        <div class="flex justify-end mt-2">
            @if($message->read_at)
                <i class="fas fa-check-double text-blue-400 text-xs" title="Lu"></i>
            @else
                <i class="fas fa-check text-gray-400 text-xs" title="Envoyé"></i>
            @endif
        </div>
    @endif
</div>

<!-- Modal de prévisualisation d'image -->
<div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button type="button" 
                onclick="closeImageModal()"
                class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <img id="modal-image" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
        <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 text-white px-3 py-2 rounded">
            <span id="modal-image-name"></span>
        </div>
    </div>
</div>

<!-- Modal de prévisualisation de fichier -->
<div id="file-preview-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-full overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 id="preview-title" class="text-lg font-semibold text-gray-900"></h3>
                <button type="button" 
                        onclick="closeFilePreview()"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="preview-content" class="p-4" style="height: 70vh; overflow: auto;">
                <!-- Contenu de prévisualisation -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Gestion des modals et prévisualisations
function openImageModal(imagePath, imageName) {
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');
    const modalImageName = document.getElementById('modal-image-name');
    
    modalImage.src = '/storage/' + imagePath;
    modalImage.alt = imageName;
    modalImageName.textContent = imageName;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('image-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function previewFile(filePath, fileName, fileExtension) {
    const modal = document.getElementById('file-preview-modal');
    const title = document.getElementById('preview-title');
    const content = document.getElementById('preview-content');
    
    title.textContent = fileName;
    
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExtension.toLowerCase())) {
        content.innerHTML = `<img src="/storage/${filePath}" alt="${fileName}" class="max-w-full h-auto mx-auto">`;
    } else if (['mp4', 'webm', 'ogg', 'avi', 'mov'].includes(fileExtension.toLowerCase())) {
        content.innerHTML = `
            <video controls class="max-w-full h-auto mx-auto">
                <source src="/storage/${filePath}" type="video/${fileExtension}">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>
        `;
    } else if (fileExtension.toLowerCase() === 'pdf') {
        content.innerHTML = `
            <iframe src="/storage/${filePath}" 
                    class="w-full h-full border-0" 
                    style="min-height: 500px;">
                <p>Votre navigateur ne supporte pas l'affichage des PDF. 
                   <a href="/storage/${filePath}" target="_blank">Cliquez ici pour ouvrir le fichier</a>.
                </p>
            </iframe>
        `;
    } else {
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-file text-6xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 mb-4">Prévisualisation non disponible pour ce type de fichier.</p>
                <a href="/storage/${filePath}" 
                   download="${fileName}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Télécharger le fichier
                </a>
            </div>
        `;
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFilePreview() {
    const modal = document.getElementById('file-preview-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function toggleFileMenu(button) {
    const menu = button.nextElementSibling;
    const allMenus = document.querySelectorAll('.file-menu');
    
    // Fermer tous les autres menus
    allMenus.forEach(m => {
        if (m !== menu) {
            m.classList.add('hidden');
        }
    });
    
    // Basculer le menu actuel
    menu.classList.toggle('hidden');
}

function deleteFileMessage(messageId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
        return;
    }
    
    fetch(`/messages/${messageId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Supprimer l'élément du DOM
            const messageElement = document.querySelector(`[data-message-id="${messageId}"]`).closest('.message-item');
            if (messageElement) {
                messageElement.remove();
            }
            
            // Fermer le menu
            document.querySelectorAll('.file-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        } else {
            alert('Erreur lors de la suppression du fichier.');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression du fichier.');
    });
}

// Fermer les menus en cliquant ailleurs
document.addEventListener('click', function(e) {
    if (!e.target.closest('.file-menu-btn') && !e.target.closest('.file-menu')) {
        document.querySelectorAll('.file-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Fermer les modals avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeFilePreview();
    }
});

// Fermer les modals en cliquant sur l'arrière-plan
document.getElementById('image-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

document.getElementById('file-preview-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeFilePreview();
    }
});
</script>
@endpush

@push('styles')
<style>
.file-attachment {
    transition: all 0.2s ease;
}

.file-attachment:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.image-preview img {
    transition: transform 0.2s ease;
}

.image-preview img:hover {
    transform: scale(1.02);
}

.file-menu {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

#image-modal {
    backdrop-filter: blur(4px);
}

#file-preview-modal {
    backdrop-filter: blur(2px);
}

@media (max-width: 640px) {
    .file-attachment {
        padding: 0.75rem;
    }
    
    .file-attachment .w-12 {
        width: 2.5rem;
        height: 2.5rem;
    }
    
    .image-preview img,
    .video-preview video {
        max-width: 100%;
        max-height: 200px;
    }
    
    #preview-content {
        height: 60vh;
    }
}
</style>
@endpush