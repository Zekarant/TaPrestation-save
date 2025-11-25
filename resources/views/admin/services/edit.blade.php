@extends('layouts.admin-modern')

@section('title', 'Modifier le Service')

@section('content')
<div class="admin-header">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--dark); margin: 0;">Modifier le Service</h2>
        <p style="color: var(--secondary); margin: 0.5rem 0 0 0;">{{ $service->title }}</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('administrateur.services.show', $service->id) }}" class="btn btn-outline">
            <i class="fas fa-eye"></i>
            Voir
        </a>
        <a href="{{ route('administrateur.services.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Retour
        </a>
    </div>
</div>

<div class="admin-content">
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Informations du Service</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('administrateur.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label for="title" class="form-label">Titre du Service *</label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $service->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="price" class="form-label">Prix (€) *</label>
                        <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service->price) }}" step="0.01" min="0" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="prestataire_id" class="form-label">Prestataire *</label>
                    <select id="prestataire_id" name="prestataire_id" class="form-control @error('prestataire_id') is-invalid @enderror" required>
                        <option value="">Sélectionner un prestataire</option>
                        @foreach($prestataires as $prestataire)
                            <option value="{{ $prestataire->id }}" {{ old('prestataire_id', $service->prestataire_id) == $prestataire->id ? 'selected' : '' }}>
                                {{ $prestataire->user->name }} ({{ $prestataire->user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('prestataire_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="description" class="form-label">Description *</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description', $service->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="categories" class="form-label">Catégories</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
                        @foreach($categories as $category)
                            <label class="checkbox-label" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.375rem; cursor: pointer;">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                    {{ in_array($category->id, old('categories', $service->categories->pluck('id')->toArray())) ? 'checked' : '' }}
                                    style="margin: 0;">
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('categories')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="image" class="form-label">Image du Service</label>
                    @if($service->image)
                        <div style="margin-bottom: 1rem;">
                            <p style="margin-bottom: 0.5rem; font-weight: 500;">Image actuelle:</p>
                            <img src="{{ asset('storage/' . $service->image) }}" alt="Image du service" style="max-width: 200px; height: auto; border-radius: 0.375rem; border: 1px solid var(--border);">
                        </div>
                    @endif
                    <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    <small class="form-text text-muted">Formats acceptés: JPEG, PNG, JPG, GIF. Taille max: 2MB. Laissez vide pour conserver l'image actuelle.</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <a href="{{ route('administrateur.services.show', $service->id) }}" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.checkbox-label:hover {
    background-color: var(--light);
}

.checkbox-label input:checked + span {
    font-weight: 600;
    color: var(--primary);
}
</style>
@endsection