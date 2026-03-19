@extends('layouts.app')

@section('preload_hero')
<link rel="preload" href="{{ asset('images/og-image.png') }}" as="image" type="image/png" fetchpriority="high">
@endsection

@section('title', 'TaPrestation - Trouvez, réservez, achetez, louez')
@section('meta_description', 'TaPrestation réunit services, location, vente et food sur une interface moderne avec téléchargement d’application mis en avant.')
@section('meta_keywords', 'prestataire, réservation, location, vente, food, TaPrestation, PWA')
@section('canonical', config('app.url', 'https://taprestation.com'))
@section('og_title', 'TaPrestation - L’application locale tout-en-un')
@section('og_description', 'Trouvez un prestataire, réservez un service, louez ou achetez localement depuis une seule interface.')
@section('og_type', 'website')
@section('twitter_title', 'TaPrestation - Réservation, services et vente')
@section('twitter_description', 'Une home plus moderne avec installation d’application mise en avant.')

@section('json_ld')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebSite",
      "name": "TaPrestation",
      "url": "{{ config('app.url', 'https://taprestation.com') }}",
      "potentialAction": {
        "@@type": "SearchAction",
        "target": {
          "@@type": "EntryPoint",
          "urlTemplate": "{{ url('/services') }}?search={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@@type": "Organization",
      "name": "TaPrestation",
      "url": "{{ config('app.url', 'https://taprestation.com') }}",
      "logo": "{{ asset('images/logo.png') }}"
    }
  ]
}
</script>
@endsection

@section('content')
@php
    $homeCategories = collect($categories ?? [])->filter()->take(8);

    $categoryVisuals = [
        'Coiffure' => ['icon' => 'fa-scissors'],
        'Esthétique' => ['icon' => 'fa-spa'],
        'Ménage' => ['icon' => 'fa-soap'],
        'Bricolage' => ['icon' => 'fa-hammer'],
        'Traiteur' => ['icon' => 'fa-utensils'],
        'Photographe' => ['icon' => 'fa-camera'],
        'DJ' => ['icon' => 'fa-music'],
        'Décoration' => ['icon' => 'fa-wand-magic-sparkles'],
        'Coach sportif' => ['icon' => 'fa-dumbbell'],
        'Cours particuliers' => ['icon' => 'fa-graduation-cap'],
        'Livraison' => ['icon' => 'fa-bicycle'],
        'Jardinage' => ['icon' => 'fa-leaf'],
        'Location matériel' => ['icon' => 'fa-toolbox'],
    ];

    $fallbackCategories = collect([
        'Coiffure',
        'Esthétique',
        'Bricolage',
        'Traiteur',
        'Photographe',
        'Livraison',
        'Location matériel',
        'Cours particuliers',
    ]);

    $displayCategories = $homeCategories->pluck('name')->filter()->merge($fallbackCategories)->unique()->take(8)->values();

    $clientBenefits = [
        'Trouver le bon pro sans perdre du temps',
        'Comparer les profils, tarifs et disponibilités',
        'Réserver, commander ou demander un devis',
        'Payer et suivre les échanges au même endroit',
    ];

    $proBenefits = [
        'Créer une vitrine pro plus crédible',
        'Recevoir des réservations et des demandes',
        'Gérer agenda, ventes, location et messages',
        'Centraliser activité, paiement et visibilité',
    ];

    $trustPoints = [
        ['icon' => 'fa-shield-halved', 'title' => 'Paiement sécurisé', 'text' => 'Parcours propre et rassurant.'],
        ['icon' => 'fa-calendar-check', 'title' => 'Réservation simple', 'text' => 'Moins de friction, plus d’action.'],
        ['icon' => 'fa-comments', 'title' => 'Messagerie intégrée', 'text' => 'Tout reste au même endroit.'],
        ['icon' => 'fa-mobile-screen-button', 'title' => 'Version app', 'text' => 'Installation rapide sur mobile.'],
    ];
@endphp

<div class="tp-home-v2">
    <section class="tp-hero-v2">
        <div class="tp-shell tp-hero-v2__grid">
            <div class="tp-hero-v2__content">
                <div class="tp-brand-mark-wrap"><span class="tp-kicker tp-brand-mark">TaPrestation</span></div>

                <h1 class="tp-hero-v2__title">
                    L’application pour <span>réserver, louer, acheter et commander.</span>
                </h1>

                <p class="tp-hero-v2__text">
                    Trouvez un prestataire, réservez un service, commandez du food, louez du matériel ou vendez depuis une seule plateforme vraiment lisible.
                </p>

                

                <div class="tp-mini-stats" aria-label="Statistiques plateforme">
                    <div class="tp-mini-stat">
                        <strong>{{ number_format($stats['total_prestataires'] ?? 0, 0, ',', ' ') }}</strong>
                        <span>prestataires</span>
                    </div>
                    <div class="tp-mini-stat">
                        <strong>{{ number_format($stats['total_services'] ?? 0, 0, ',', ' ') }}</strong>
                        <span>services</span>
                    </div>
                    <div class="tp-mini-stat">
                        <strong>{{ number_format((float) ($stats['avg_rating'] ?? 4.9), 1, ',', ' ') }}/5</strong>
                        <span>note</span>
                    </div>
                </div>
            </div>

            <div class="tp-hero-v2__aside">
                <div id="download-app" class="tp-install-spotlight">
                    <div class="tp-install-spotlight__top">
                        <span class="tp-install-badge">Téléchargement</span>
                        <i class="fas fa-mobile-screen-button" aria-hidden="true"></i>
                    </div>

                    <h2>Installe l’application TaPrestation</h2>
                    <p>
                        Garde la plateforme sous la main, comme une vraie app. Ouverture rapide, accès direct, usage mobile plus propre. Cette fois, le téléchargement est au premier rang, pas au fond du couloir.
                    </p>

                    <div class="tp-install-spotlight__actions">
                        <button id="android-install-btn" type="button" class="tp-btn-v2 tp-btn-v2--install">Installer sur Android</button>
                        <button id="ios-install-btn" type="button" class="tp-btn-v2 tp-btn-v2--ghost">Installer sur iPhone</button>
                    </div>

                    <div class="tp-install-spotlight__meta">
                        <span><i class="fas fa-check"></i> Android</span>
                        <span><i class="fas fa-check"></i> iPhone</span>
                        <span><i class="fas fa-check"></i> PWA</span>
                    </div>

                    <div id="android-install-hint" class="tp-install-spotlight__hint"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="tp-section-v2">
        <div class="tp-shell">
            <div class="tp-section-head-v2 tp-section-head-v2--center">
                <span class="tp-kicker">Catégories</span>
                <h2>Ce que l’utilisateur doit voir immédiatement</h2>
                <p>Des entrées courtes, visuelles, modernes, sans effet catalogue poussiéreux.</p>
            </div>

            <div class="tp-category-grid-v2">
                @foreach($displayCategories as $categoryName)
                    @php $visual = $categoryVisuals[$categoryName] ?? ['icon' => 'fa-sparkles']; @endphp
                    <a href="{{ url('/services?search=' . urlencode($categoryName)) }}" class="tp-category-card-v2">
                        <span class="tp-category-card-v2__icon">
                            <i class="fas {{ $visual['icon'] }}" aria-hidden="true"></i>
                        </span>
                        <strong>{{ $categoryName }}</strong>
                        <small>Explorer</small>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="tp-section-v2 tp-section-v2--dark">
        <div class="tp-shell">
            <div class="tp-section-head-v2 tp-section-head-v2--light">
                <span class="tp-kicker tp-kicker--light">Pourquoi ça tient mieux</span>
                <h2>Une home plus moderne, plus nette, plus mobile-first.</h2>
                <p>Moins de pavés, plus de respiration, plus de contraste utile.</p>
            </div>

            <div class="tp-trust-grid-v2">
                @foreach($trustPoints as $point)
                    <article class="tp-trust-card-v2">
                        <div class="tp-trust-card-v2__icon"><i class="fas {{ $point['icon'] }}" aria-hidden="true"></i></div>
                        <h3>{{ $point['title'] }}</h3>
                        <p>{{ $point['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="tp-section-v2">
        <div class="tp-shell">
            <div class="tp-panels-v2">
                <article class="tp-panel-v2">
                    <span class="tp-kicker">Pour les clients</span>
                    <h2>Trouver vite. Réserver proprement.</h2>
                    <div class="tp-list-v2">
                        @foreach($clientBenefits as $item)
                            <div><i class="fas fa-check"></i><span>{{ $item }}</span></div>
                        @endforeach
                    </div>
                </article>

                <article class="tp-panel-v2 tp-panel-v2--accent">
                    <span class="tp-kicker">Pour les prestataires</span>
                    <h2>Montrer son activité avec plus d’impact.</h2>
                    <div class="tp-list-v2">
                        @foreach($proBenefits as $item)
                            <div><i class="fas fa-check"></i><span>{{ $item }}</span></div>
                        @endforeach
                    </div>
                </article>
            </div>
        </div>
    </section>

    @if(isset($featuredPrestataires) && $featuredPrestataires->count())
    <section class="tp-section-v2 tp-section-v2--soft">
        <div class="tp-shell">
            <div class="tp-section-head-v2 tp-section-head-v2--center">
                <span class="tp-kicker">Prestataires visibles</span>
                <h2>Des profils en avant dès l’arrivée</h2>
                <p>Parce qu’une plateforme vide, c’est comme une vitrine sans lumière.</p>
            </div>

            <div class="tp-provider-grid-v2">
                @foreach($featuredPrestataires->take(6) as $prestataire)
                    <article class="tp-provider-card-v2">
                        <div class="tp-provider-card-v2__head">
                            <div class="tp-provider-card-v2__avatar">
                                @if(!empty($prestataire->profile_photo_url ?? null))
                                    <img src="{{ $prestataire->profile_photo_url }}" alt="{{ $prestataire->user?->name ?? 'Prestataire' }}">
                                @elseif(!empty($prestataire->photo ?? null))
                                    <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user?->name ?? 'Prestataire' }}">
                                @else
                                    <span>{{ strtoupper(substr($prestataire->user?->name ?? 'P', 0, 1)) }}</span>
                                @endif
                            </div>
                            <div>
                                <h3>{{ $prestataire->user?->name ?? 'Prestataire' }}</h3>
                                <p>{{ $prestataire->city ?? 'France' }} @if(!empty($prestataire->profession ?? null)) · {{ $prestataire->profession }} @endif</p>
                            </div>
                        </div>

                        <div class="tp-provider-card-v2__stats">
                            <span>{{ $prestataire->services->count() ?? 0 }} service{{ ($prestataire->services->count() ?? 0) > 1 ? 's' : '' }}</span>
                            <span>{{ number_format((float) ($prestataire->average_rating ?? 0), 1, ',', ' ') }}/5</span>
                        </div>

                        <a href="{{ route('prestataires.show', $prestataire) }}" class="tp-provider-card-v2__link">Voir le profil</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="tp-section-v2 tp-section-v2--final">
        <div class="tp-shell">
            <div class="tp-final-v2">
                <div>
                    <span class="tp-kicker">Passe à l’action</span>
                    <h2>Une home qui donne envie d’entrer, pas juste de scroller.</h2>
                    <p>Le visiteur doit comprendre, cliquer et rester. Le reste, c’est de la décoration avec un diplôme.</p>
                </div>
                <div class="tp-final-v2__actions">
                    <a href="{{ route('services.index') }}" class="tp-btn-v2 tp-btn-v2--dark">Explorer</a>
                    <a href="{{ route('prestataire.register') }}" class="tp-btn-v2 tp-btn-v2--ghost-dark">Créer mon espace</a>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="pwa-install-modal" class="tp-install-modal hidden" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <button type="button" class="tp-install-modal__backdrop" data-install-modal-close aria-label="Fermer"></button>
    <div class="tp-install-modal__dialog" data-install-modal-panel>
        <div class="tp-install-modal__header">
            <h4 id="modal-title">Installation</h4>
            <button id="close-install-modal-btn" type="button" class="tp-install-modal__close" aria-label="Fermer la fenêtre">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <div id="modal-body" class="tp-install-modal__body"></div>
    </div>
</div>
</div>

@push('scripts')
<script defer>
function showInstallInstructions(platform) {
    const modal = document.getElementById('pwa-install-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');

    if (!modal || !modalTitle || !modalBody) return;

    if (platform === 'ios') {
        modalTitle.textContent = 'Installer sur iPhone';
        modalBody.innerHTML = `
            <div class="tp-install-steps">
                <p class="tp-install-steps__intro">Sur iPhone, l’ajout se fait depuis <strong>Safari</strong>.</p>
                <div class="tp-install-steps__note">Ouvre TaPrestation dans Safari, appuie sur <strong>Partager</strong>, puis sur <strong>Sur l’écran d’accueil</strong>.</div>
                <ol>
                    <li>Ouvre le site dans Safari.</li>
                    <li>Appuie sur l’icône <strong>Partager</strong>.</li>
                    <li>Choisis <strong>Sur l’écran d’accueil</strong>.</li>
                    <li>Appuie sur <strong>Ajouter</strong>.</li>
                </ol>
            </div>`;
    } else {
        modalTitle.textContent = 'Installer sur Android';
        modalBody.innerHTML = `
            <div class="tp-install-steps">
                <p class="tp-install-steps__intro">Sur Android, Chrome peut proposer l’installation automatiquement.</p>
                <div class="tp-install-steps__note">Si aucune fenêtre ne s’ouvre, utilise le menu du navigateur puis <strong>Installer l’application</strong> ou <strong>Ajouter à l’écran d’accueil</strong>.</div>
                <ol>
                    <li>Ouvre le site dans Chrome.</li>
                    <li>Appuie sur le menu du navigateur.</li>
                    <li>Choisis <strong>Installer l’application</strong> ou <strong>Ajouter à l’écran d’accueil</strong>.</li>
                    <li>Confirme l’installation.</li>
                </ol>
            </div>`;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeInstallModal() {
    const modal = document.getElementById('pwa-install-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function () {
    let deferredPrompt = null;
    const androidBtn = document.getElementById('android-install-btn');
    const iosBtn = document.getElementById('ios-install-btn');
    const hint = document.getElementById('android-install-hint');

    const ua = window.navigator.userAgent || '';
    const isIOS = /iphone|ipad|ipod/i.test(ua);
    const isAndroid = /android/i.test(ua);
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    function setHint(text) {
        if (hint) hint.textContent = text;
    }

    if (isStandalone) {
        if (androidBtn) {
            androidBtn.disabled = true;
            androidBtn.textContent = 'Application installée';
        }
        if (iosBtn) {
            iosBtn.disabled = true;
            iosBtn.textContent = 'Application installée';
        }
        setHint('L’application est déjà ajoutée sur cet appareil.');
    } else if (isIOS) {
        setHint('iPhone : utilise Safari puis “Sur l’écran d’accueil”.');
    } else if (isAndroid) {
        setHint('Android : installation possible depuis Chrome.');
    } else {
        setHint('Choisis Android ou iPhone pour voir la bonne méthode.');
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (androidBtn && !isStandalone) {
            androidBtn.disabled = false;
            androidBtn.textContent = 'Installer sur Android';
        }
        if (!isIOS) {
            setHint('Installation Android disponible maintenant sur cet appareil.');
        }
    });

    if (androidBtn) {
        androidBtn.addEventListener('click', async () => {
            if (isStandalone) return;

            if (isIOS) {
                showInstallInstructions('android');
                return;
            }

            if (deferredPrompt) {
                deferredPrompt.prompt();
                const choice = await deferredPrompt.userChoice;
                deferredPrompt = null;

                if (choice?.outcome === 'accepted') {
                    androidBtn.textContent = 'Installation lancée';
                    setHint('Confirme l’ajout si le navigateur te le demande.');
                } else {
                    setHint('Installation Android annulée. Tu peux réessayer.');
                }
                return;
            }

            showInstallInstructions('android');
        });
    }

    if (iosBtn) {
        iosBtn.addEventListener('click', () => {
            if (isStandalone) return;
            showInstallInstructions('ios');
        });
    }

    document.getElementById('close-install-modal-btn')?.addEventListener('click', closeInstallModal);
    document.querySelectorAll('[data-install-modal-close]').forEach(el => {
        el.addEventListener('click', closeInstallModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeInstallModal();
    });
});
</script>
@endpush

<style>
:root {
    --tp-bg: #f6efe6;
    --tp-panel: rgba(255,248,241,.82);
    --tp-panel-strong: #fffaf4;
    --tp-ink: #2f241d;
    --tp-muted: #7c6a5b;
    --tp-line: rgba(92,67,44,.08);
    --tp-line-strong: rgba(92,67,44,.14);
    --tp-brand: #c98f5b;
    --tp-brand-2: #e3b98e;
    --tp-brand-soft: rgba(201,143,91,.12);
    --tp-shadow: 0 8px 22px rgba(94,72,52,.06), 0 18px 40px rgba(94,72,52,.05);
    --tp-radius-xl: 30px;
    --tp-radius-lg: 22px;
    --tp-radius-md: 18px;
}

.tp-home-v2 {
    background:
        radial-gradient(circle at top left, rgba(227,185,142,.18), transparent 34%),
        radial-gradient(circle at top right, rgba(201,143,91,.12), transparent 30%),
        linear-gradient(180deg, #fffaf4 0%, #f6efe6 100%);
    color: var(--tp-ink);
}

.tp-shell {
    width: min(1200px, calc(100% - 32px));
    margin: 0 auto;
}

.tp-hero-v2 {
    padding: 28px 0 34px;
}

.tp-hero-v2__grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
    gap: 22px;
    align-items: stretch;
}

.tp-hero-v2__content,
.tp-install-spotlight,
.tp-panel-v2,
.tp-provider-card-v2,
.tp-trust-card-v2,
.tp-final-v2,
.tp-category-card-v2 {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}

.tp-hero-v2__content {
    background: linear-gradient(180deg, rgba(255,250,244,.88), rgba(255,246,238,.78));
    border: 1px solid rgba(217,189,160,.22);
    box-shadow: var(--tp-shadow);
    border-radius: 28px;
    padding: 30px;
}

.tp-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .18em;
    color: var(--tp-brand);
    margin-bottom: 14px;
}

.tp-kicker--light {
    color: #f7dfc6;
}

.tp-brand-mark-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 18px;
}

.tp-brand-mark {
    font-size: clamp(1rem, 1.4vw, 1.2rem);
    letter-spacing: .28em;
    padding: 10px 18px;
    border-radius: 999px;
    background: rgba(31,111,255,.08);
    border: 1px solid rgba(31,111,255,.12);
}

.tp-hero-v2__content {
    text-align: center;
}

.tp-hero-v2__title,
.tp-hero-v2__text {
    margin-left: auto;
    margin-right: auto;
}

.tp-mini-stats {
    justify-content: center;
}

.tp-hero-v2__title {
    margin: 0;
    font-size: clamp(2.2rem, 4vw, 4.2rem);
    line-height: .96;
    letter-spacing: -.04em;
    max-width: 760px;
}

.tp-hero-v2__title span {
    display: block;
    background: linear-gradient(135deg, var(--tp-brand), var(--tp-brand-2));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.tp-hero-v2__text {
    margin: 18px 0 24px;
    font-size: 1.05rem;
    line-height: 1.7;
    color: var(--tp-muted);
    max-width: 720px;
}

.tp-search-v2 {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
    margin-bottom: 18px;
}

.tp-search-v2__field {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,.94);
    border: 1px solid rgba(9,18,37,.08);
    border-radius: 14px;
    padding: 0 18px;
    min-height: 62px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
}

.tp-search-v2__field i {
    color: var(--tp-brand);
    font-size: 1rem;
}

.tp-search-v2__field input {
    flex: 1;
    border: 0;
    outline: none;
    background: transparent;
    min-width: 0;
    color: var(--tp-ink);
    font-size: 1rem;
}

.tp-btn-v2 {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border: 0;
    border-radius: 14px;
    min-height: 62px;
    padding: 0 22px;
    font-weight: 800;
    text-decoration: none;
    transition: .2s ease;
    cursor: pointer;
}

.tp-btn-v2:hover {
    transform: translateY(-1px);
}

.tp-btn-v2--primary,
.tp-btn-v2--install {
    color: #fffaf5;
    background: linear-gradient(135deg, var(--tp-brand), var(--tp-brand-2));
    box-shadow: 0 12px 30px rgba(31,111,255,.28);
}

.tp-btn-v2--ghost {
    background: rgba(255,250,244,.82);
    color: var(--tp-ink);
    border: 1px solid rgba(9,18,37,.10);
}

.tp-btn-v2--dark {
    background: #6f5442;
    color: #fffaf5;
}

.tp-btn-v2--ghost-dark {
    background: rgba(255,255,255,.14);
    color: #fffaf5;
    border: 1px solid rgba(255,255,255,.16);
}

.tp-text-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 18px;
    color: #7a8699;
    font-size: .96rem;
}

.tp-text-links a {
    color: var(--tp-ink);
    text-decoration: none;
    font-weight: 700;
}

.tp-text-links a:hover {
    color: var(--tp-brand);
}

.tp-mini-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.tp-mini-stat {
    width: 108px;
    min-height: 96px;
    border-radius: 16px;
    padding: 14px 12px;
    background: rgba(255,250,244,.78);
    border: 1px solid rgba(9,18,37,.08);
    box-shadow: 0 4px 12px rgba(94,72,52,.05);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.tp-mini-stat strong {
    font-size: 1.15rem;
    line-height: 1;
}

.tp-mini-stat span {
    font-size: .78rem;
    color: var(--tp-muted);
    line-height: 1.2;
}

.tp-install-spotlight {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #6f5442 0%, #8d6c56 45%, #c98f5b 100%);
    color: #fffaf5;
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 18px 42px rgba(111,84,66,.18);
    min-height: 100%;
}

.tp-install-spotlight::before {
    content: "";
    position: absolute;
    inset: auto -80px -80px auto;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(255,255,255,.18), transparent 68%);
}

.tp-install-spotlight__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}

.tp-install-spotlight__top i {
    font-size: 1.6rem;
    opacity: .95;
}

.tp-install-badge {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.16);
    font-size: .8rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.tp-install-spotlight h2 {
    margin: 0 0 12px;
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    line-height: 1;
    letter-spacing: -.03em;
}

.tp-install-spotlight p {
    margin: 0 0 22px;
    color: rgba(255,255,255,.82);
    line-height: 1.7;
}

.tp-install-spotlight__actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 18px;
}

.tp-install-spotlight__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    color: rgba(255,255,255,.86);
    font-size: .92rem;
}

.tp-install-spotlight__meta span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.tp-install-spotlight__hint {
    margin-top: 14px;
    min-height: 22px;
    color: #b9d7ff;
    font-size: .92rem;
}

.tp-section-v2 {
    padding: 22px 0 34px;
}

.tp-section-v2--soft {
    padding-top: 8px;
}

.tp-section-head-v2 {
    margin-bottom: 18px;
}

.tp-section-head-v2--center {
    text-align: center;
}

.tp-section-head-v2--light h2,
.tp-section-head-v2--light p {
    color: #fffaf5;
}

.tp-section-head-v2 h2 {
    margin: 0 0 10px;
    font-size: clamp(1.7rem, 3vw, 2.6rem);
    line-height: 1.02;
    letter-spacing: -.03em;
}

.tp-section-head-v2 p {
    margin: 0;
    color: var(--tp-muted);
    line-height: 1.7;
}

.tp-category-grid-v2 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.tp-category-card-v2 {
    min-height: 138px;
    border-radius: 24px;
    padding: 18px;
    text-decoration: none;
    color: var(--tp-ink);
    background: linear-gradient(180deg, rgba(255,255,255,.82), rgba(255,255,255,.62));
    border: 1px solid rgba(255,255,255,.75);
    box-shadow: 0 12px 28px rgba(15,23,42,.05);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: .2s ease;
}

.tp-category-card-v2:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 42px rgba(15,23,42,.08);
}

.tp-category-card-v2__icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(31,111,255,.12), rgba(123,97,255,.14));
    color: var(--tp-brand);
    font-size: 1.1rem;
}

.tp-category-card-v2 strong {
    font-size: 1rem;
}

.tp-category-card-v2 small {
    color: var(--tp-muted);
}

.tp-feature-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 18px;
    padding: 0 2px;
}

.tp-feature-strip a {
    color: var(--tp-ink);
    text-decoration: none;
    font-weight: 700;
    position: relative;
}

.tp-feature-strip a::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -3px;
    width: 100%;
    height: 1px;
    background: rgba(9,18,37,.18);
}

.tp-section-v2--dark {
    background: linear-gradient(180deg, #07101f 0%, #0c1630 100%);
    margin-top: 8px;
    padding: 34px 0;
}

.tp-trust-grid-v2 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.tp-trust-card-v2 {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 24px;
    padding: 18px;
    color: #fffaf5;
}

.tp-trust-card-v2__icon {
    width: 46px;
    height: 46px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    background: rgba(255,255,255,.08);
    color: #f7dfc6;
}

.tp-trust-card-v2 h3 {
    margin: 0 0 8px;
    font-size: 1rem;
}

.tp-trust-card-v2 p {
    margin: 0;
    color: rgba(255,255,255,.72);
    line-height: 1.6;
}

.tp-panels-v2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.tp-panel-v2 {
    padding: 24px;
    border-radius: 28px;
    background: linear-gradient(180deg, rgba(255,255,255,.82), rgba(255,255,255,.66));
    border: 1px solid rgba(255,255,255,.74);
    box-shadow: 0 14px 36px rgba(15,23,42,.05);
}

.tp-panel-v2--accent {
    background: linear-gradient(135deg, rgba(31,111,255,.10), rgba(123,97,255,.11), rgba(255,255,255,.78));
}

.tp-panel-v2 h2 {
    margin: 0 0 14px;
    font-size: 1.7rem;
    line-height: 1.04;
    letter-spacing: -.03em;
}

.tp-list-v2 {
    display: grid;
    gap: 12px;
}

.tp-list-v2 div {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    color: var(--tp-muted);
    line-height: 1.6;
}

.tp-list-v2 i {
    color: var(--tp-brand);
    margin-top: 4px;
}

.tp-provider-grid-v2 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.tp-provider-card-v2 {
    background: linear-gradient(180deg, rgba(255,255,255,.84), rgba(255,255,255,.66));
    border: 1px solid rgba(255,255,255,.74);
    box-shadow: 0 14px 32px rgba(15,23,42,.05);
    border-radius: 24px;
    padding: 18px;
}

.tp-provider-card-v2__head {
    display: flex;
    gap: 14px;
    align-items: center;
    margin-bottom: 14px;
}

.tp-provider-card-v2__avatar {
    width: 58px;
    height: 58px;
    border-radius: 14px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(31,111,255,.15), rgba(123,97,255,.18));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: var(--tp-brand);
    flex-shrink: 0;
}

.tp-provider-card-v2__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tp-provider-card-v2 h3 {
    margin: 0 0 4px;
    font-size: 1rem;
}

.tp-provider-card-v2 p {
    margin: 0;
    color: var(--tp-muted);
    font-size: .92rem;
    line-height: 1.5;
}

.tp-provider-card-v2__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.tp-provider-card-v2__stats span {
    min-height: 34px;
    display: inline-flex;
    align-items: center;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(9,18,37,.05);
    font-size: .84rem;
    color: var(--tp-ink);
}

.tp-provider-card-v2__link {
    color: var(--tp-brand);
    font-weight: 800;
    text-decoration: none;
}

.tp-section-v2--final {
    padding-bottom: 48px;
}

.tp-final-v2 {
    padding: 26px;
    border-radius: 30px;
    background: linear-gradient(140deg, #081120 0%, #12264e 56%, #234cb7 100%);
    color: #fffaf5;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    box-shadow: 0 32px 80px rgba(10,17,32,.26);
}

.tp-final-v2 h2 {
    margin: 0 0 10px;
    font-size: clamp(1.7rem, 3vw, 2.6rem);
    line-height: 1.04;
    letter-spacing: -.03em;
}

.tp-final-v2 p {
    margin: 0;
    color: rgba(255,255,255,.76);
    line-height: 1.7;
}

.tp-final-v2__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    flex-shrink: 0;
}

@media (max-width: 1080px) {
    .tp-hero-v2__grid,
    .tp-trust-grid-v2,
    .tp-provider-grid-v2,
    .tp-category-grid-v2,
    .tp-panels-v2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .tp-hero-v2__grid {
        align-items: start;
    }
}

@media (max-width: 780px) {
    .tp-shell {
        width: min(100% - 20px, 1200px);
    }

    .tp-hero-v2 {
        padding-top: 18px;
    }

    .tp-hero-v2__grid,
    .tp-category-grid-v2,
    .tp-trust-grid-v2,
    .tp-panels-v2,
    .tp-provider-grid-v2,
    .tp-install-spotlight__actions,
    .tp-search-v2 {
        grid-template-columns: 1fr;
    }

    .tp-hero-v2__content,
    .tp-install-spotlight,
    .tp-final-v2,
    .tp-panel-v2 {
        padding: 20px;
        border-radius: 24px;
    }

    .tp-mini-stat {
        width: calc(33.333% - 8px);
        min-width: 92px;
    }

    .tp-final-v2 {
        align-items: flex-start;
    }

    .tp-final-v2__actions {
        width: 100%;
    }

    .tp-btn-v2,
    .tp-search-v2__field {
        min-height: 56px;
    }
}

.tp-mini-stat,
.tp-category-card-v2,
.tp-trust-card-v2,
.tp-provider-card-v2,
.tp-panel-v2 {
    border-width: 1px !important;
    box-shadow: 0 3px 10px rgba(94,72,52,.04) !important;
}

.tp-mini-stat {
    width: 118px;
    min-height: 82px;
    border-radius: 14px;
    background: rgba(255,250,244,.70);
}

.tp-category-grid-v2,
.tp-trust-grid-v2,
.tp-provider-grid-v2,
.tp-panels-v2 {
    gap: 14px;
}

.tp-category-card-v2,
.tp-trust-card-v2,
.tp-provider-card-v2,
.tp-panel-v2,
.tp-hero-v2__content,
.tp-install-spotlight {
    background-clip: padding-box;
}

.tp-category-card-v2 {
    background: rgba(255,250,244,.62) !important;
}

.tp-section-v2--soft {
    background: transparent !important;
}

@media (min-width: 992px) {
    .tp-hero-v2__grid {
        gap: 16px;
    }
}


/* Full-bleed mobile / PWA overrides */
.tp-home-v2{
    width:100vw;
    margin-left:calc(50% - 50vw);
    margin-right:calc(50% - 50vw);
    overflow:hidden;
}

.tp-home-v2 .tp-shell{
    width:min(1280px, 100%);
    max-width:none;
}

@media (max-width: 780px){
    .tp-home-v2{
        border-radius:0 !important;
    }

    .tp-home-v2 .tp-shell{
        width:100% !important;
        padding-left:0 !important;
        padding-right:0 !important;
        margin:0 !important;
    }

    .tp-hero-v2,
    .tp-section-v2{
        padding-top:0 !important;
        padding-bottom:0 !important;
    }

    .tp-hero-v2__grid,
    .tp-category-grid-v2,
    .tp-trust-grid-v2,
    .tp-panels-v2,
    .tp-provider-grid-v2{
        gap:0 !important;
    }

    .tp-hero-v2__content,
    .tp-install-spotlight,
    .tp-panel-v2,
    .tp-provider-card-v2,
    .tp-trust-card-v2,
    .tp-category-card-v2,
    .tp-final-v2{
        border-radius:0 !important;
        border-left:0 !important;
        border-right:0 !important;
        margin:0 !important;
        width:100% !important;
    }

    .tp-hero-v2__content,
    .tp-install-spotlight,
    .tp-panel-v2,
    .tp-final-v2{
        padding:18px 16px !important;
    }

    .tp-category-card-v2,
    .tp-trust-card-v2,
    .tp-provider-card-v2{
        padding-left:16px !important;
        padding-right:16px !important;
    }

    .tp-mini-stats{
        padding-left:0 !important;
        padding-right:0 !important;
        gap:8px !important;
    }

    .tp-mini-stat{
        width:calc(33.333% - 6px) !important;
        min-width:0 !important;
    }
}


.tp-install-modal{
    position:fixed;
    inset:0;
    z-index:70;
}
.tp-install-modal.hidden{
    display:none;
}
.tp-install-modal__backdrop{
    position:absolute;
    inset:0;
    border:0;
    background:rgba(46,31,21,.42);
    backdrop-filter:blur(4px);
}
.tp-install-modal__dialog{
    position:relative;
    z-index:1;
    width:min(560px, calc(100% - 24px));
    margin:clamp(24px, 8vh, 72px) auto;
    background:#fffaf4;
    border:1px solid rgba(201,143,91,.16);
    border-radius:24px;
    box-shadow:0 20px 60px rgba(76,55,37,.16);
    overflow:hidden;
}
.tp-install-modal__header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:18px 20px;
    background:linear-gradient(180deg, #fff6ed 0%, #f7e6d3 100%);
    border-bottom:1px solid rgba(201,143,91,.16);
}
.tp-install-modal__header h4{
    margin:0;
    font-size:1.08rem;
    font-weight:800;
    color:#3d2f24;
}
.tp-install-modal__close{
    width:42px;
    height:42px;
    border-radius:999px;
    border:1px solid rgba(201,143,91,.16);
    background:#fffaf4;
    color:#6f5442;
    cursor:pointer;
}
.tp-install-modal__body{
    padding:20px;
}
.tp-install-steps{
    color:#5f4b3b;
}
.tp-install-steps__intro{
    margin:0 0 14px;
    font-size:.98rem;
    line-height:1.6;
}
.tp-install-steps__note{
    margin-bottom:14px;
    padding:14px 16px;
    border-radius:16px;
    background:#f7ecdf;
    border:1px solid rgba(201,143,91,.14);
    line-height:1.55;
}
.tp-install-steps ol{
    margin:0;
    padding-left:20px;
    display:grid;
    gap:10px;
}
.tp-install-steps li{
    line-height:1.55;
}

</style>
@endsection
