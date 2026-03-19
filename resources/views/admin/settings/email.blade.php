@extends('layouts.admin-modern')

@section('title', 'Configuration Email')
@section('page-title', 'Configuration Email')

@section('content')
<div class="page-header">
    <h1 class="page-title">📧 Configuration Email</h1>
    <p class="page-subtitle">Configurez les paramètres d'envoi d'emails</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base">
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Les identifiants sensibles SMTP doivent etre geres via la configuration serveur et le fichier d'environnement, pas via l'interface d'administration.
    </div>

    <form action="{{ route('admin.settings.email.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Serveur SMTP</label>
                <input type="text" name="mail_host" value="{{ $emailSettings['mail_host'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="smtp.example.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Port</label>
                <input type="number" name="mail_port" value="{{ $emailSettings['mail_port'] ?? 587 }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                <input type="text" name="mail_username" value="{{ $emailSettings['mail_username'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email expéditeur</label>
                <input type="email" name="mail_from_address" value="{{ $emailSettings['mail_from_address'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom expéditeur</label>
                <input type="text" name="mail_from_name" value="{{ $emailSettings['mail_from_name'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>

        <div class="mt-6 flex justify-between">
            <button type="button" onclick="testEmail()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-paper-plane mr-2"></i> Envoyer un email test
            </button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i> Enregistrer
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function testEmail() {
    const testEmail = window.prompt('Adresse email de test');
    if (!testEmail) {
        return;
    }

    fetch('{{ route("admin.settings.email.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ test_email: testEmail })
    }).then(r => r.json()).then(data => {
        alert(data.message || 'Email test envoyé !');
    }).catch(e => alert('Erreur : ' + e.message));
}
</script>
@endpush
@endsection
