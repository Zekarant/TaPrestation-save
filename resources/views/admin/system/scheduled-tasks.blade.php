@extends('layouts.admin-modern')

@section('title', 'Tâches planifiées')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-tools fa-3x text-muted mb-3"></i>
            <h3>Tâches planifiées</h3>
            <p class="text-muted">Cette page est en cours de développement.</p>
            <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>
@endsection
