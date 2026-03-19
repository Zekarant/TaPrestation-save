@extends('layouts.client')

@section('title', 'Pourquoi devenir prestataire ?')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/become-provider.css') }}">
<style>
.benefits-page {
    min-height: 100vh;
}

/* Hero Section */
.benefits-hero {
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 50%, #3b82f6 100%);
    padding: 4rem 2rem;
    text-align: center;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.benefits-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
}

.hero-content h1 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.hero-content p {
    font-size: 1.25rem;
    opacity: 0.95;
    margin-bottom: 2rem;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 2rem;
}

.hero-stat {
    text-align: center;
}

.hero-stat .value {
    font-size: 2.5rem;
    font-weight: 700;
    display: block;
}

.hero-stat .label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.hero-cta {
    margin-top: 2.5rem;
}

.btn-hero {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: #fff;
    color: #8b5cf6;
    font-size: 1.1rem;
    font-weight: 700;
    border-radius: 3rem;
    text-decoration: none;
    transition: all 0.3s;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.btn-hero:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
}

/* Benefits Grid */
.benefits-section {
    padding: 4rem 2rem;
    background: #f8fafc;
}

.section-header {
    text-align: center;
    max-width: 600px;
    margin: 0 auto 3rem;
}

.section-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.section-header p {
    color: #64748b;
    font-size: 1.1rem;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.benefit-card {
    background: #fff;
    border-radius: 1.5rem;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    transition: all 0.3s;
}

.benefit-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}

.benefit-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(99, 102, 241, 0.1));
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
}

.benefit-icon i {
    font-size: 1.75rem;
    color: #8b5cf6;
}

.benefit-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.benefit-card p {
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.6;
}

/* How it Works */
.how-it-works {
    padding: 4rem 2rem;
    background: #fff;
}

.steps-container {
    display: flex;
    justify-content: center;
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
    flex-wrap: wrap;
}

.step-item {
    flex: 1;
    min-width: 200px;
    max-width: 280px;
    text-align: center;
    position: relative;
}

.step-number {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
    border-radius: 50%;
    color: #fff;
    font-size: 1.25rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.step-item h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.step-item p {
    color: #64748b;
    font-size: 0.9rem;
}

/* Testimonials */
.testimonials {
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    max-width: 1000px;
    margin: 0 auto;
}

.testimonial-card {
    background: #fff;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}

.testimonial-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.testimonial-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;
}

.testimonial-info h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

.testimonial-info span {
    font-size: 0.85rem;
    color: #64748b;
}

.testimonial-rating {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 0.75rem;
}

.testimonial-rating i {
    color: #f59e0b;
    font-size: 0.9rem;
}

.testimonial-card blockquote {
    font-size: 0.95rem;
    color: #475569;
    line-height: 1.6;
    font-style: italic;
}

/* Final CTA */
.final-cta {
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #1e293b, #334155);
    text-align: center;
    color: #fff;
}

.final-cta h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.final-cta p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.cta-features {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.cta-feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.cta-feature i {
    color: #10b981;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }

    .hero-stats {
        flex-direction: column;
        gap: 1.5rem;
    }

    .hero-stat .value {
        font-size: 2rem;
    }
}
</style>
@endsection

@section('content')
<div class="benefits-page">
    {{-- Hero Section --}}
    <section class="benefits-hero">
        <div class="hero-content">
            <h1>Développez votre activité avec TaPrestation</h1>
            <p>Rejoignez des milliers de professionnels qui trouvent de nouveaux clients chaque jour grâce à notre plateforme.</p>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="value">{{ number_format($stats['prestataires']) }}+</span>
                    <span class="label">Prestataires actifs</span>
                </div>
                <div class="hero-stat">
                    <span class="value">{{ $stats['categories'] }}+</span>
                    <span class="label">Catégories de services</span>
                </div>
                <div class="hero-stat">
                    <span class="value">{{ number_format($stats['average_rating'], 1) }}/5</span>
                    <span class="label">Note moyenne</span>
                </div>
            </div>

            <div class="hero-cta">
                <a href="{{ route('client.become-provider.index') }}" class="btn-hero">
                    <i class="fas fa-rocket"></i>
                    Commencer maintenant
                </a>
            </div>
        </div>
    </section>

    {{-- Benefits Grid --}}
    <section class="benefits-section">
        <div class="section-header">
            <h2>Pourquoi nous rejoindre ?</h2>
            <p>Des avantages concrets pour développer votre activité</p>
        </div>

        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Accès à des milliers de clients</h3>
                <p>Recevez des demandes de clients à la recherche de vos services, directement dans votre zone géographique.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Alertes personnalisées</h3>
                <p>Soyez notifié instantanément des nouvelles demandes correspondant à vos compétences et votre localisation.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3>Gratuit pour commencer</h3>
                <p>Créez votre profil gratuitement et commencez à recevoir des demandes sans engagement.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Construisez votre réputation</h3>
                <p>Collectez des avis clients pour renforcer votre crédibilité et attirer plus de demandes.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Gérez votre agenda</h3>
                <p>Outils intégrés pour gérer vos rendez-vous, devis et factures en un seul endroit.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Paiements sécurisés</h3>
                <p>Transactions protégées et paiements garantis pour travailler en toute sérénité.</p>
            </div>
        </div>
    </section>

    {{-- How it Works --}}
    <section class="how-it-works">
        <div class="section-header">
            <h2>Comment ça marche ?</h2>
            <p>3 étapes simples pour commencer</p>
        </div>

        <div class="steps-container">
            <div class="step-item">
                <div class="step-number">1</div>
                <h4>Créez votre profil</h4>
                <p>Renseignez vos services, votre zone d'intervention et vos tarifs en quelques minutes.</p>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <h4>Recevez des demandes</h4>
                <p>Les clients vous contactent directement ou vous répondez aux appels d'offres.</p>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <h4>Développez votre activité</h4>
                <p>Réalisez des prestations, collectez des avis et augmentez votre visibilité.</p>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="testimonials">
        <div class="section-header">
            <h2>Ils nous font confiance</h2>
            <p>Découvrez les témoignages de nos prestataires</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar">MC</div>
                    <div class="testimonial-info">
                        <h5>Marie C.</h5>
                        <span>Photographe</span>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <blockquote>
                    "Grâce à TaPrestation, j'ai doublé mon nombre de clients en 6 mois. La plateforme est intuitive et les demandes correspondent vraiment à mon profil."
                </blockquote>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar">PL</div>
                    <div class="testimonial-info">
                        <h5>Pierre L.</h5>
                        <span>Plombier</span>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <blockquote>
                    "Les appels d'offres me permettent de trouver des chantiers près de chez moi. Je gagne un temps précieux !"
                </blockquote>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar">SD</div>
                    <div class="testimonial-info">
                        <h5>Sophie D.</h5>
                        <span>Coach sportif</span>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <blockquote>
                    "Une excellente plateforme pour développer son activité. Les outils de gestion sont vraiment pratiques."
                </blockquote>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="final-cta">
        <h2>Prêt à développer votre activité ?</h2>
        <p>Créez votre profil en moins de 5 minutes</p>
        
        <div class="cta-features">
            <div class="cta-feature">
                <i class="fas fa-check"></i>
                <span>Inscription gratuite</span>
            </div>
            <div class="cta-feature">
                <i class="fas fa-check"></i>
                <span>Sans engagement</span>
            </div>
            <div class="cta-feature">
                <i class="fas fa-check"></i>
                <span>Support 7j/7</span>
            </div>
        </div>

        <a href="{{ route('client.become-provider.index') }}" class="btn-hero">
            <i class="fas fa-user-plus"></i>
            Créer mon profil prestataire
        </a>
    </section>
</div>
@endsection
