/**
 * Media Compress - Compression cote client pour images et videos
 * Reduit la taille des fichiers AVANT upload pour economiser bande passante et stockage
 */
(function () {
    'use strict';

    // ─── Image Compression (Canvas API) ────────────────────

    /**
     * Compresse une image avant upload
     * @param {File} file - Fichier image original
     * @param {Object} options
     * @param {number} options.maxWidth   - Largeur max (default: 1920)
     * @param {number} options.maxHeight  - Hauteur max (default: 1920)
     * @param {number} options.quality    - Qualite 0-1 (default: 0.8)
     * @param {string} options.format     - 'image/webp' ou 'image/jpeg' (default: webp avec fallback jpeg)
     * @returns {Promise<File>} - Fichier compresse
     */
    async function compressImage(file, options = {}) {
        const {
            maxWidth = 1920,
            maxHeight = 1920,
            quality = 0.8,
            format = null
        } = options;

        // Skip non-images
        if (!file.type.startsWith('image/')) return file;

        // Skip tiny files (< 100KB)
        if (file.size < 100 * 1024) return file;

        // Skip GIFs (animation loss)
        if (file.type === 'image/gif') return file;

        return new Promise((resolve) => {
            const img = new Image();
            const reader = new FileReader();

            reader.onload = (e) => {
                img.onload = () => {
                    // Calculate new dimensions
                    let { width, height } = img;

                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    // Draw on canvas
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Determine output format
                    let outputFormat = format;
                    if (!outputFormat) {
                        // Try WebP first (smaller), fallback to JPEG
                        const testCanvas = document.createElement('canvas');
                        testCanvas.width = 1;
                        testCanvas.height = 1;
                        outputFormat = testCanvas.toDataURL('image/webp').startsWith('data:image/webp')
                            ? 'image/webp'
                            : 'image/jpeg';
                    }

                    canvas.toBlob((blob) => {
                        if (!blob || blob.size >= file.size) {
                            // Compressed version is bigger, use original
                            resolve(file);
                            return;
                        }

                        const ext = outputFormat === 'image/webp' ? '.webp' : '.jpg';
                        const name = file.name.replace(/\.[^.]+$/, '') + ext;
                        const compressed = new File([blob], name, { type: outputFormat });

                        const saved = ((1 - compressed.size / file.size) * 100).toFixed(0);
                        console.log('[MediaCompress] Image: ' + file.name + ' ' + formatSize(file.size) + ' -> ' + formatSize(compressed.size) + ' (-' + saved + '%)');

                        resolve(compressed);
                    }, outputFormat, quality);
                };

                img.onerror = () => resolve(file); // fallback to original
                img.src = e.target.result;
            };

            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    /**
     * Compresse toutes les images d'un FileList ou d'un input[type=file]
     * @param {FileList|HTMLInputElement} input
     * @param {Object} options - memes options que compressImage
     * @returns {Promise<File[]>} - Tableau de fichiers compresses
     */
    async function compressImages(input, options = {}) {
        const files = input instanceof HTMLInputElement ? Array.from(input.files) : Array.from(input);
        const results = await Promise.all(files.map(f => compressImage(f, options)));
        return results;
    }

    /**
     * Attache la compression automatique a un input[type=file] image
     * Remplace les fichiers dans le FormData avant soumission
     * @param {string|HTMLInputElement} inputOrSelector
     * @param {Object} options
     */
    function autoCompressInput(inputOrSelector, options = {}) {
        const input = typeof inputOrSelector === 'string'
            ? document.querySelector(inputOrSelector)
            : inputOrSelector;

        if (!input || input.tagName !== 'INPUT') return;

        const form = input.closest('form');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            const files = Array.from(input.files);
            const imageFiles = files.filter(f => f.type.startsWith('image/'));

            if (imageFiles.length === 0) return; // no images, skip

            e.preventDefault();

            // Show loading state
            const submitBtn = form.querySelector('[type="submit"]');
            let originalText = '';
            if (submitBtn) {
                originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>Compression...';
                submitBtn.disabled = true;
            }

            try {
                // Compress images
                const compressed = await compressImages(imageFiles, options);

                // Replace files in input
                const dt = new DataTransfer();
                compressed.forEach(f => dt.items.add(f));
                // Add non-image files back unchanged
                files.filter(f => !f.type.startsWith('image/')).forEach(f => dt.items.add(f));
                input.files = dt.files;

                // Submit form
                form.submit();
            } catch (err) {
                console.error('[MediaCompress] Error:', err);
                // Restore button and submit with originals
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
                form.submit();
            }
        }, { once: true });
    }

    // ─── Helpers ────────────────────────────────────────────

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // ─── Auto-attach: compression globale sur TOUS les inputs image ──

    document.addEventListener('DOMContentLoaded', () => {
        // Selectionne tous les file inputs qui acceptent des images
        document.querySelectorAll('input[type="file"]').forEach(input => {
            const accept = (input.getAttribute('accept') || '').toLowerCase();
            if (!accept.includes('image')) return;

            // Options personnalisables via data-attributes
            const opts = {
                maxWidth: parseInt(input.dataset.compressWidth) || 1920,
                maxHeight: parseInt(input.dataset.compressHeight) || 1920,
                quality: parseFloat(input.dataset.compressQuality) || 0.8,
            };
            autoCompressInput(input, opts);
        });
    });

    // Expose globally
    window.mediaCompress = {
        compressImage,
        compressImages,
        autoCompressInput,
        formatSize
    };
})();
