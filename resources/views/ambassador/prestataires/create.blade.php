@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('ambassador.prestataires.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-arrow-left mr-1"></i>Retour
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Inscrire un prestataire</h1>
        <p class="text-sm text-gray-500">Le prestataire sera automatiquement rattaché à votre compte ambassadeur.</p>
    </div>

    <div class="bg-white rounded-xl shadow border border-blue-200 p-6">
        <form method="POST" action="{{ route('ambassador.prestataires.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nom du responsable *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-1">Nom de l'enseigne *</label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Téléphone *</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-1">Ville *</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1">Catégorie *</label>
                    <select name="category_id" id="category_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                        <option value="">Choisir une catégorie</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="subcategory_id" class="block text-sm font-semibold text-gray-700 mb-1">Sous-catégorie *</label>
                    <select name="subcategory_id" id="subcategory_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                        <option value="">Choisir d'abord une catégorie</option>
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg">
                    <i class="fas fa-check mr-2"></i>Inscrire le prestataire
                </button>
            </div>
        </form>
    </div>
</div>

@push('ambassador-scripts')
<script>
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    const subcategorySelect = document.getElementById('subcategory_id');
    subcategorySelect.innerHTML = '<option value="">Chargement...</option>';

    if (!categoryId) {
        subcategorySelect.innerHTML = '<option value="">Choisir d\'abord une catégorie</option>';
        return;
    }

    fetch(`/api/categories/${categoryId}/subcategories`)
        .then(r => r.json())
        .then(data => {
            subcategorySelect.innerHTML = '<option value="">Choisir une sous-catégorie</option>';
            (data.data || data).forEach(sub => {
                subcategorySelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
            });
        })
        .catch(() => {
            subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
        });
});
</script>
@endpush
@endsection
