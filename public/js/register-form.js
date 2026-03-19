/**
 * Script amélioré pour gérer le formulaire d'inscription dynamique
 */
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du type d'utilisateur
    const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
    const prestataireFields = document.getElementById('prestataire-fields');
    const clientFields = document.getElementById('client-fields');
    
    // Vérifier que les éléments essentiels sont présents
    if (userTypeRadios.length === 0) {
        console.warn('⚠️ Aucun radio button user_type trouvé');
        return;
    }
    
    // Récupérer le type d'utilisateur depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const userType = urlParams.get('type') || 'prestataire';
    
    // Mettre à jour le champ caché du type d'utilisateur
    const userTypeInput = document.querySelector('input[name="user_type"]');
    if (userTypeInput) {
        userTypeInput.value = userType;
    }
    
    // Ajouter la classe spécifique au formulaire selon le type d'utilisateur
    const form = document.querySelector('form');
    if (form) {
        form.classList.add(userType + '-form');
    }
    
    // Chargement des sous-catégories
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            // Réinitialiser les sous-catégories
            subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie</option>';
            subcategorySelect.disabled = true;
            
            if (categoryId) {
                // Charger les sous-catégories
                fetch(`/api/categories/${categoryId}/subcategories`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(subcategory => {
                                const option = document.createElement('option');
                                option.value = subcategory.id;
                                option.textContent = subcategory.name;
                                subcategorySelect.appendChild(option);
                            });
                            subcategorySelect.disabled = false;
                        } else {
                            console.warn('⚠️ Aucune sous-catégorie disponible');
                        }
                    })
                    .catch(error => {
                        console.error('❌ Erreur lors du chargement des sous-catégories:', error);
                        // Afficher un message d'erreur à l'utilisateur
                        const errorOption = document.createElement('option');
                        errorOption.value = '';
                        errorOption.textContent = 'Erreur de chargement';
                        subcategorySelect.appendChild(errorOption);
                    });
            }
        });
    } else {
        console.warn('⚠️ Éléments category_id ou subcategory_id non trouvés');
    }
    
    // Gestion de la géolocalisation
    const useLocationBtn = document.getElementById('use_location');
    const cityInput = document.getElementById('city') || document.getElementById('location');
    
    if (useLocationBtn && cityInput) {
        useLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                useLocationBtn.disabled = true;
                cityInput.value = 'Recherche de votre position...';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Ici, vous pourriez utiliser un service de géocodage inverse pour obtenir la ville
                        // Pour l'exemple, nous allons simplement afficher les coordonnées
                        cityInput.value = `Position détectée (${position.coords.latitude.toFixed(4)}, ${position.coords.longitude.toFixed(4)})`;
                        useLocationBtn.disabled = false;
                    },
                    function(error) {
                        cityInput.value = '';
                        alert('Impossible de récupérer votre position. Veuillez entrer votre ville manuellement.');
                        useLocationBtn.disabled = false;
                    }
                );
            } else {
                alert('La géolocalisation n\'est pas prise en charge par votre navigateur.');
            }
        });
    }
    
    // Gestion de l'affichage du nom de fichier pour les champs de type file
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        const fileNameDisplay = document.createElement('div');
        fileNameDisplay.className = 'file-name-display';
        input.parentNode.appendChild(fileNameDisplay);
        
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        });
    });
    
    // Ajouter un effet de chargement sur le bouton de soumission
    const submitForm = document.querySelector('form');
    const submitButton = document.querySelector('.submit-button');
    const buttonText = document.querySelector('.button-text');
    
    if (submitForm && submitButton) {
        submitForm.addEventListener('submit', function() {
            // Vérifier si le formulaire est valide avant d'ajouter l'effet de chargement
            if (this.checkValidity()) {
                submitButton.classList.add('submit-button-loading');
                buttonText.textContent = 'Chargement...';
                submitButton.disabled = true;
            }
        });
    }
    
    // Validation des champs en temps réel
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        if (input.type !== 'hidden' && input.type !== 'submit') {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                // Supprimer les messages d'erreur lorsque l'utilisateur commence à corriger
                const errorElement = this.parentNode.querySelector('.field-error');
                if (errorElement) {
                    errorElement.remove();
                }
                this.classList.remove('is-invalid');
            });
        }
    });
    
    // Validation du formulaire avant soumission
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Valider tous les champs requis
            formInputs.forEach(input => {
                if (input.hasAttribute('required') && !validateField(input)) {
                    isValid = false;
                }
            });
            
            // Valider la confirmation du mot de passe
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            if (password && passwordConfirmation && password.value !== passwordConfirmation.value) {
                showError(passwordConfirmation, 'Les mots de passe ne correspondent pas');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                // Faire défiler jusqu'au premier champ avec erreur
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                // Afficher l'état de chargement sur le bouton
                const submitButton = document.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.classList.add('submit-button-loading');
                    submitButton.dataset.originalText = submitButton.textContent;
                    submitButton.textContent = '';
                }
            }
        });
    }
    
    // Fonction pour valider un champ
    function validateField(field) {
        // Supprimer les messages d'erreur précédents
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        field.classList.remove('is-invalid');
        field.classList.remove('is-valid');
        
        // Vérifier si le champ est vide alors qu'il est requis
        if (field.hasAttribute('required') && !field.value.trim()) {
            showError(field, 'Ce champ est obligatoire');
            return false;
        }
        
        // Validation spécifique selon le type de champ
        if (field.type === 'email' && field.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                showError(field, 'Veuillez entrer une adresse email valide');
                return false;
            }
        }
        
        if (field.type === 'url' && field.value.trim()) {
            try {
                new URL(field.value);
            } catch (_) {
                showError(field, 'Veuillez entrer une URL valide');
                return false;
            }
        }
        
        if (field.type === 'tel' && field.value.trim()) {
            const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
            if (!phoneRegex.test(field.value)) {
                showError(field, 'Veuillez entrer un numéro de téléphone valide');
                return false;
            }
        }
        
        if (field.type === 'password' && field.value.trim()) {
            if (field.value.length < 8) {
                showError(field, 'Le mot de passe doit contenir au moins 8 caractères');
                return false;
            }
        }
        
        // Si tout est valide, ajouter la classe de validation
        if (field.value.trim()) {
            field.classList.add('is-valid');
        }
        
        return true;
    }
    
    // Fonction pour afficher un message d'erreur
    function showError(field, message) {
        field.classList.add('is-invalid');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        
        field.parentNode.appendChild(errorElement);
    }
    
    // Initialisation de l'autocomplétion pour les villes (exemple simple)
    if (cityInput) {
        // Ici, vous pourriez intégrer une bibliothèque d'autocomplétion comme Awesomplete ou utiliser l'API Google Places
        // Pour cet exemple, nous allons simplement ajouter un événement de saisie
        cityInput.addEventListener('input', function() {
            // Logique d'autocomplétion à implémenter
            // Par exemple, faire une requête à une API de villes marocaines
        });
    }
});