document.addEventListener('DOMContentLoaded', function() {
    // Drag and drop
    const dropArea = document.getElementById('drop-area');
    const videoInput = document.getElementById('video');
    const fileNameDisplay = document.getElementById('file-name');
    const submitBtn = document.getElementById('submit-btn');

    if (dropArea) {
        dropArea.addEventListener('click', (event) => {
            event.stopPropagation();
            videoInput.click();
        });

        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropArea.style.borderColor = '#5a67d8';
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.style.borderColor = '#dee2e6';
        });

        dropArea.addEventListener('drop', (event) => {
            event.preventDefault();
            dropArea.style.borderColor = '#dee2e6';
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                videoInput.files = files;
                handleFileUpload({ target: { files } });
            }
        });
    }

    if (videoInput) {
        videoInput.addEventListener('change', handleFileUpload);
    }

    function handleFileUpload(event) {
        const file = event.target.files[0];
        const videoPreview = document.getElementById('video-preview');
        
        if (file) {
            fileNameDisplay.textContent = file.name;
            const objectURL = URL.createObjectURL(file);
            videoPreview.src = objectURL;
            videoPreview.style.display = 'block';
            
            videoPreview.onloadedmetadata = function() {
                if (videoPreview.duration > 60) { // 60 seconds max
                    alert('La vidéo ne doit pas dépasser 60 secondes.');
                    videoInput.value = '';
                    videoPreview.style.display = 'none';
                    fileNameDisplay.textContent = '';
                    submitBtn.disabled = true;
                } else {
                    submitBtn.disabled = false;
                }
            };
        } else {
            submitBtn.disabled = true;
        }
    }

    // Gestion du formulaire d'importation
    const uploadForm = document.querySelector('form');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(event) {
            const videoFile = videoInput.files.length > 0;
            if (!videoFile) {
                alert('Veuillez sélectionner une vidéo à importer.');
                event.preventDefault();
                return;
            }
            const submitButton = event.submitter;
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
            }
        });
    }
});