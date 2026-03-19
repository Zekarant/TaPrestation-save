@extends('layouts.app')

@section('title', '419 | Page Expirée')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-linear-to-br from-slate-900 via-blue-900 to-slate-900 py-20">
    <div class="max-w-2xl mx-auto text-center px-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-xl bg-linear-to-br from-cyan-500 to-blue-500 mb-6 shadow-lg">
            <span class="text-2xl font-extrabold text-cyan-50">419</span>
        </div>

        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-cyan-300 mb-3">Page Expirée</h1>
        <p class="text-sm sm:text-base text-blue-200 mb-2">Votre session a expiré — le jeton CSRF n'est plus valide.</p>
        <p id="countdown" class="text-sm text-blue-300 mb-6">Rechargement automatique dans <span id="timer">3</span> secondes...</p>

        <div class="flex items-center justify-center gap-3 flex-wrap">
            <button onclick="location.reload()" class="inline-flex items-center gap-2 px-5 py-3 bg-white/5 border border-blue-700/30 text-blue-200 rounded-lg hover:bg-white/10 transition">Recharger maintenant</button>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-linear-to-r from-blue-500 to-cyan-500 text-cyan-50 rounded-lg font-bold hover:scale-[1.02] transition">Se reconnecter</a>
        </div>
    </div>
</div>

<script>
    // Auto-reload after 3 seconds
    let countdown = 3;
    const timerElement = document.getElementById('timer');
    
    const interval = setInterval(() => {
        countdown--;
        if (timerElement) {
            timerElement.textContent = countdown;
        }
        
        if (countdown <= 0) {
            clearInterval(interval);
            location.reload();
        }
    }, 1000);

    // Allow user to stop auto-reload
    document.addEventListener('click', () => {
        clearInterval(interval);
        document.getElementById('countdown').textContent = 'Rechargement annulé.';
    });
</script>
@endsection

