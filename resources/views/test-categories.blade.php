<!DOCTYPE html>
<html>
<head>
    <title>Test Categories</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test Categories Loading</h1>
    
    <div>
        <label for="category_id">Catégorie principale:</label>
        <select id="category_id" name="category_id">
            <option value="">Sélectionnez une catégorie</option>
        </select>
        <div id="category-status"></div>
    </div>
    
    <div>
        <label for="subcategory_id">Sous-catégorie:</label>
        <select id="subcategory_id" name="subcategory_id">
            <option value="">Sélectionnez d'abord une catégorie</option>
        </select>
        <div id="subcategory-status"></div>
    </div>
    
    <div id="debug-info"></div>
    
    <script>
        // Debug function to log messages
        function debugLog(message) {
            const debugInfo = document.getElementById('debug-info');
            debugInfo.innerHTML += '<p>' + new Date().toISOString() + ': ' + message + '</p>';
            console.log(message);
        }
        
        // Test loading categories
        function loadMainCategories() {
            debugLog('Loading main categories...');
            fetch('/api/categories/main')
                .then(response => {
                    debugLog('Received response from /api/categories/main');
                    return response.json();
                })
                .then(data => {
                    debugLog('Categories data received: ' + data.length + ' items');
                    const categorySelect = document.getElementById('category_id');
                    const statusDiv = document.getElementById('category-status');
                    
                    if (categorySelect) {
                        categorySelect.innerHTML = '<option value="">Sélectionnez une catégorie</option>';
                        data.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            categorySelect.appendChild(option);
                        });
                        statusDiv.innerHTML = '<p style="color: green;">Loaded ' + data.length + ' categories</p>';
                        debugLog('Categories successfully loaded into select');
                    } else {
                        statusDiv.innerHTML = '<p style="color: red;">Category select not found</p>';
                        debugLog('Category select element not found');
                    }
                })
                .catch(error => {
                    const statusDiv = document.getElementById('category-status');
                    statusDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                    debugLog('Error loading categories: ' + error.message);
                    console.error('Error loading categories:', error);
                });
        }
        
        // Handle category change to load subcategories
        document.getElementById('category_id').addEventListener('change', function() {
            const categoryId = this.value;
            const subcategorySelect = document.getElementById('subcategory_id');
            const statusDiv = document.getElementById('subcategory-status');
            
            debugLog('Category changed to: ' + categoryId);
            
            if (categoryId) {
                // Fetch subcategories from API
                fetch(`/api/categories/${categoryId}/subcategories`)
                    .then(response => {
                        debugLog('Received response from /api/categories/' + categoryId + '/subcategories');
                        return response.json();
                    })
                    .then(data => {
                        debugLog('Subcategories data received: ' + data.length + ' items');
                        subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie</option>';
                        data.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;
                            subcategorySelect.appendChild(option);
                        });
                        subcategorySelect.disabled = false;
                        statusDiv.innerHTML = '<p style="color: green;">Loaded ' + data.length + ' subcategories</p>';
                        debugLog('Subcategories successfully loaded into select');
                    })
                    .catch(error => {
                        subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
                        subcategorySelect.disabled = true;
                        statusDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                        debugLog('Error loading subcategories: ' + error.message);
                        console.error('Error loading subcategories:', error);
                    });
            } else {
                subcategorySelect.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie</option>';
                subcategorySelect.disabled = true;
                statusDiv.innerHTML = '<p>Sous-catégorie désactivée</p>';
                debugLog('Subcategory select disabled');
            }
        });
        
        // Load categories when page loads
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('DOM loaded, starting category loading...');
            loadMainCategories();
        });
    </script>
</body>
</html>