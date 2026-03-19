@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ======================================
   PAGE PRESTATAIRES - DESIGN PREMIUM 2025
   ====================================== */

:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #a5b4fc;
    --secondary: #f59e0b;
    --success: #10b981;
    --danger: #ef4444;
    --dark: #0f172a;
    --gray-900: #1e293b;
    --gray-700: #334155;
    --gray-500: #64748b;
    --gray-300: #cbd5e1;
    --gray-100: #f1f5f9;
    --white: #ffffff;
    --gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
    --gradient-hero: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 24px;
}

/* Page container */
.presta-page {
    min-height: 100vh;
    background: var(--gray-100);
    padding-bottom: 100px;
}

/* ============ HERO SECTION ============ */
.presta-hero {
    background: var(--gradient-hero);
    padding: 40px 20px 80px;
    position: relative;
    overflow: hidden;
}

.presta-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}

.presta-hero-inner {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.presta-hero-content {
    text-align: center;
    margin-bottom: 32px;
}

.presta-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 100px;
    color: var(--primary-light);
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
}

.presta-hero-badge i {
    color: var(--secondary);
}

.presta-hero h1 {
    font-size: 32px;
    font-weight: 800;
    color: var(--white);
    margin-bottom: 12px;
    line-height: 1.2;
}

.presta-hero h1 span {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.presta-hero-subtitle {
    font-size: 16px;
    color: var(--gray-300);
    max-width: 500px;
    margin: 0 auto;
    line-height: 1.6;
}

@media (min-width: 768px) {
    .presta-hero {
        padding: 60px 40px 100px;
    }
    .presta-hero h1 {
        font-size: 42px;
    }
    .presta-hero-subtitle {
        font-size: 18px;
    }
}

/* Stats row */
.presta-hero-stats {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
    margin-top: 24px;
}

.presta-stat-item {
    text-align: center;
}

.presta-stat-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--white);
    display: block;
}

.presta-stat-label {
    font-size: 13px;
    color: var(--gray-300);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ============ SEARCH SECTION ============ */
.presta-search-section {
    max-width: 900px;
    margin: -50px auto 0;
    padding: 0 16px;
    position: relative;
    z-index: 10;
}

.presta-search-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    padding: 24px;
    border: 1px solid rgba(99, 102, 241, 0.1);
}

.presta-search-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.presta-search-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

@media (min-width: 640px) {
    .presta-search-row {
        grid-template-columns: 2fr 1fr 1fr;
    }
}

.presta-input-group {
    position: relative;
}

.presta-input-group i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-500);
    font-size: 16px;
    z-index: 1;
}

.presta-input {
    width: 100%;
    padding: 14px 16px 14px 48px;
    border: 2px solid var(--gray-100);
    border-radius: var(--radius-md);
    font-size: 15px;
    background: var(--gray-100);
    transition: all 0.2s ease;
    color: var(--gray-900);
}

.presta-input:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--white);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.presta-select {
    width: 100%;
    padding: 14px 40px 14px 48px;
    border: 2px solid var(--gray-100);
    border-radius: var(--radius-md);
    font-size: 15px;
    background: var(--gray-100);
    transition: all 0.2s ease;
    color: var(--gray-900);
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 20px;
}

.presta-select:focus {
    outline: none;
    border-color: var(--primary);
    background-color: var(--white);
}

.presta-search-actions {
    display: flex;
    gap: 12px;
}

.presta-btn {
    padding: 14px 28px;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    text-decoration: none;
}

.presta-btn-primary {
    background: var(--gradient-primary);
    color: var(--white);
    flex: 1;
}

.presta-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
}

.presta-btn-outline {
    background: transparent;
    color: var(--gray-500);
    border: 2px solid var(--gray-300);
}

.presta-btn-outline:hover {
    border-color: var(--primary);
    color: var(--primary);
}

/* ============ CATEGORIES CHIPS ============ */
.presta-categories-section {
    max-width: 1200px;
    margin: 32px auto 0;
    padding: 0 16px;
}

.presta-categories-scroll {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 4px 0 16px;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.presta-categories-scroll::-webkit-scrollbar {
    display: none;
}

.presta-category-chip {
    flex-shrink: 0;
    padding: 10px 20px;
    border-radius: 100px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 2px solid var(--gray-300);
    background: var(--white);
    color: var(--gray-700);
    white-space: nowrap;
}

.presta-category-chip:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}

.presta-category-chip.active {
    background: var(--gradient-primary);
    color: var(--white);
    border-color: transparent;
}

.presta-category-chip i {
    margin-right: 6px;
}

/* ============ RESULTS BAR ============ */
.presta-results-bar {
    max-width: 1200px;
    margin: 24px auto 0;
    padding: 0 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.presta-results-count {
    font-size: 15px;
    color: var(--gray-500);
}

.presta-results-count strong {
    color: var(--gray-900);
    font-weight: 700;
}

/* ============ GRID PRESTATAIRES ============ */
.presta-grid {
    max-width: 1400px;
    margin: 24px auto 0;
    padding: 0 16px;
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
}

@media (min-width: 1200px) {
    .presta-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

@media (min-width: 900px) and (max-width: 1199px) {
    .presta-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 600px) and (max-width: 899px) {
    .presta-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 599px) {
    .presta-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
}

@media (max-width: 400px) {
    .presta-grid {
        grid-template-columns: 1fr;
    }
}

/* ============ CARTE PRESTATAIRE ============ */
.presta-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-100);
    transition: all 0.2s ease;
    position: relative;
}

.presta-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: rgba(99, 102, 241, 0.2);
}

/* Image cover */
.presta-card-cover {
    height: 90px;
    position: relative;
    overflow: hidden;
}

.presta-card-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.presta-card:hover .presta-card-cover-img {
    transform: scale(1.08);
}

.presta-card-cover-gradient {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 80px;
    background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
}

.presta-card-cover-default {
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.presta-card-cover-default i {
    font-size: 48px;
    color: rgba(255,255,255,0.3);
}

/* Avatar */
.presta-card-avatar {
    position: absolute;
    bottom: -20px;
    left: 12px;
    width: 50px;
    height: 50px;
    border-radius: 10px;
    overflow: hidden;
    border: 3px solid var(--white);
    box-shadow: var(--shadow-md);
    background: var(--white);
    z-index: 2;
}

.presta-card-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.presta-card-avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-primary);
    color: var(--white);
    font-size: 18px;
    font-weight: 700;
}

/* Badges */
.presta-card-badges {
    position: absolute;
    top: 6px;
    right: 6px;
    display: flex;
    gap: 4px;
    z-index: 2;
}

.presta-badge {
    padding: 3px 8px;
    border-radius: 100px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    gap: 3px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
}

.presta-badge-verified {
    background: rgba(16, 185, 129, 0.9);
    color: var(--white);
}

.presta-badge-verified i {
    font-size: 10px;
}

.presta-badge-new {
    background: rgba(99, 102, 241, 0.9);
    color: var(--white);
}

.presta-badge-top {
    background: rgba(245, 158, 11, 0.9);
    color: var(--white);
}

/* Content */
.presta-card-content {
    padding: 28px 12px 12px;
}

.presta-card-header {
    margin-bottom: 8px;
}

.presta-card-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.presta-card-category {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--primary);
    font-weight: 600;
    padding: 2px 8px;
    background: rgba(99, 102, 241, 0.08);
    border-radius: 100px;
}

.presta-card-category i {
    font-size: 9px;
}

/* Rating */
.presta-card-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 6px;
}

.presta-rating-stars {
    display: flex;
    gap: 1px;
}

.presta-rating-stars i {
    font-size: 10px;
    color: var(--secondary);
}

.presta-rating-stars i.empty {
    color: var(--gray-300);
}

.presta-rating-value {
    font-size: 11px;
    font-weight: 700;
    color: var(--gray-900);
}

.presta-rating-count {
    font-size: 10px;
    color: var(--gray-500);
}

/* Description - Hidden on compact */
.presta-card-desc {
    display: none;
}

/* Location & Meta */
.presta-card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-top: 1px solid var(--gray-100);
    margin-bottom: 8px;
}

.presta-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--gray-500);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.presta-meta-item i {
    color: var(--primary);
    font-size: 10px;
    flex-shrink: 0;
}

/* Actions */
.presta-card-actions {
    display: flex;
    gap: 6px;
}

.presta-action-btn {
    flex: 1;
    padding: 8px 10px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 11px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
}

.presta-action-primary {
    background: var(--gradient-primary);
    color: var(--white);
}

.presta-action-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.presta-action-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
}

.presta-action-secondary:hover {
    background: var(--gray-300);
}

.presta-action-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--gray-200);
    background: var(--white);
    color: var(--gray-500);
    font-size: 12px;
    transition: all 0.2s ease;
    cursor: pointer;
    flex-shrink: 0;
}

.presta-action-icon:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}

.presta-action-icon.active {
    background: rgba(239, 68, 68, 0.1);
    border-color: var(--danger);
    color: var(--danger);
}

/* ============ EMPTY STATE ============ */
.presta-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 24px;
    background: var(--white);
    border-radius: var(--radius-xl);
    border: 2px dashed var(--gray-300);
}

.presta-empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 24px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.2;
}

.presta-empty-icon i {
    font-size: 40px;
    color: var(--white);
}

.presta-empty h3 {
    font-size: 24px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 12px;
}

.presta-empty p {
    color: var(--gray-500);
    font-size: 16px;
    margin-bottom: 24px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* ============ PAGINATION ============ */
.presta-pagination {
    max-width: 1200px;
    margin: 40px auto 0;
    padding: 0 16px;
    display: flex;
    justify-content: center;
}

.presta-pagination nav {
    display: flex;
    gap: 8px;
}

.presta-pagination .page-link {
    min-width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s ease;
    background: var(--white);
    color: var(--gray-700);
    border: 2px solid var(--gray-200);
}

.presta-pagination .page-link:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.presta-pagination .page-item.active .page-link {
    background: var(--gradient-primary);
    color: var(--white);
    border-color: transparent;
}

/* ============ SCROLL TO TOP ============ */
.scroll-top-btn {
    position: fixed;
    bottom: 100px;
    right: 20px;
    width: 52px;
    height: 52px;
    background: var(--gradient-primary);
    color: var(--white);
    border: none;
    border-radius: 50%;
    box-shadow: var(--shadow-lg);
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    z-index: 30;
    transition: all 0.3s ease;
}

.scroll-top-btn.visible {
    display: flex;
}

.scroll-top-btn:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
}

/* ============ ANIMATIONS ============ */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.presta-card {
    animation: fadeInUp 0.5s ease forwards;
}

.presta-card:nth-child(1) { animation-delay: 0.05s; }
.presta-card:nth-child(2) { animation-delay: 0.1s; }
.presta-card:nth-child(3) { animation-delay: 0.15s; }
.presta-card:nth-child(4) { animation-delay: 0.2s; }
.presta-card:nth-child(5) { animation-delay: 0.25s; }
.presta-card:nth-child(6) { animation-delay: 0.3s; }
.presta-card:nth-child(7) { animation-delay: 0.35s; }
.presta-card:nth-child(8) { animation-delay: 0.4s; }

/* ============ RESPONSIVE ============ */
@media (max-width: 640px) {
    .presta-hero h1 {
        font-size: 24px;
    }
    
    .presta-hero-subtitle {
        font-size: 13px;
    }
    
    .presta-search-card {
        padding: 12px;
    }
    
    .presta-search-actions {
        flex-direction: column;
    }
    
    .presta-card-cover {
        height: 70px;
    }
    
    .presta-card-avatar {
        width: 40px;
        height: 40px;
        bottom: -16px;
        left: 10px;
    }
    
    .presta-card-avatar-placeholder {
        font-size: 14px;
    }
    
    .presta-card-content {
        padding: 22px 10px 10px;
    }
    
    .presta-card-name {
        font-size: 12px;
    }
    
    .presta-card-category {
        font-size: 9px;
        padding: 2px 6px;
    }
    
    .presta-action-btn {
        padding: 6px 8px;
        font-size: 10px;
    }
    
    .presta-action-icon {
        width: 28px;
        height: 28px;
        font-size: 10px;
    }
}
</style>
@endpush

@section('content')
<div class="presta-page">
    {{-- ============ HERO SECTION ============ --}}
    <section class="presta-hero">
        <div class="presta-hero-inner">
            <div class="presta-hero-content">
                <div class="presta-hero-badge">
                    <i class="fas fa-star"></i>
                    Professionnels vérifiés
                </div>
                <h1>Trouvez le <span>prestataire idéal</span></h1>
                <p class="presta-hero-subtitle">
                    Des milliers de professionnels qualifiés prêts à réaliser vos projets. 
                    Comparez, contactez et réservez en toute confiance.
                </p>
            </div>
            
            <div class="presta-hero-stats">
                <div class="presta-stat-item">
                    <span class="presta-stat-value">{{ $prestataires->total() }}+</span>
                    <span class="presta-stat-label">Prestataires</span>
                </div>
                <div class="presta-stat-item">
                    <span class="presta-stat-value">{{ $categories->count() }}</span>
                    <span class="presta-stat-label">Catégories</span>
                </div>
                <div class="presta-stat-item">
                    <span class="presta-stat-value">4.8</span>
                    <span class="presta-stat-label">Note moyenne</span>
                </div>
            </div>
        </div>
    </section>
    
    {{-- ============ SEARCH SECTION ============ --}}
    <section class="presta-search-section">
        <div class="presta-search-card">
            <form action="{{ route('prestataires.index') }}" method="GET" class="presta-search-form" id="searchForm">
                <div class="presta-search-row">
                    <div class="presta-input-group">
                        <i class="fas fa-search"></i>
                        <input 
                            type="text" 
                            name="name" 
                            class="presta-input" 
                            placeholder="Nom du prestataire..."
                            value="{{ request('name') }}"
                        >
                    </div>
                    
                    <div class="presta-input-group">
                        <i class="fas fa-folder"></i>
                        <select name="category" id="category" class="presta-select">
                            <option value="">Catégorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="presta-input-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <input 
                            type="text" 
                            name="city" 
                            class="presta-input" 
                            placeholder="Ville..."
                            value="{{ request('city') }}"
                            style="padding-left: 48px;"
                        >
                    </div>
                </div>
                
                <div class="presta-search-actions">
                    <button type="submit" class="presta-btn presta-btn-primary">
                        <i class="fas fa-search"></i>
                        Rechercher
                    </button>
                    @if(request()->anyFilled(['name', 'category', 'subcategory', 'city']))
                        <a href="{{ route('prestataires.index') }}" class="presta-btn presta-btn-outline">
                            <i class="fas fa-undo"></i>
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </section>
    
    {{-- ============ CATEGORIES CHIPS ============ --}}
    <section class="presta-categories-section">
        <div class="presta-categories-scroll">
            <a href="{{ route('prestataires.index') }}" class="presta-category-chip {{ !request('category') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> Tous
            </a>
            @foreach($categories->take(10) as $cat)
                <a href="{{ route('prestataires.index', ['category' => $cat->id]) }}" 
                   class="presta-category-chip {{ request('category') == $cat->id ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </section>
    
    {{-- ============ RESULTS BAR ============ --}}
    <div class="presta-results-bar">
        <div class="presta-results-count">
            <strong>{{ $prestataires->total() }}</strong> prestataire{{ $prestataires->total() > 1 ? 's' : '' }} trouvé{{ $prestataires->total() > 1 ? 's' : '' }}
            @if(request('category') && $categories->where('id', request('category'))->first())
                dans <strong>{{ $categories->where('id', request('category'))->first()->name }}</strong>
            @endif
            @if(request('city'))
                à <strong>{{ request('city') }}</strong>
            @endif
        </div>
    </div>
    
    {{-- ============ GRID PRESTATAIRES ============ --}}
    <div class="presta-grid">
        @forelse($prestataires as $prestataire)
            @php
                $rating = isset($prestataire->reviews) ? $prestataire->reviews->avg('rating') : 0;
                $reviewCount = isset($prestataire->reviews) ? $prestataire->reviews->count() : 0;
                $isNew = $prestataire->created_at && $prestataire->created_at->diffInDays(now()) < 30;
                $isTop = $reviewCount >= 5 && $rating >= 4.5;
                $categoryName = $prestataire->category->name ?? $prestataire->secteur_activite ?? null;
                $hasPhoto = $prestataire->photo || $prestataire->user->avatar;
                $coverPhoto = $prestataire->cover_photo ?? null;
            @endphp
            
            <div class="presta-card">
                {{-- Cover Image --}}
                <div class="presta-card-cover">
                    @if($coverPhoto)
                        <img src="{{ asset('storage/' . $coverPhoto) }}" class="presta-card-cover-img" alt="{{ $prestataire->user->name ?? 'Prestataire' }}">
                    @else
                        <div class="presta-card-cover-default">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    @endif
                    <div class="presta-card-cover-gradient"></div>
                    
                    {{-- Avatar --}}
                    <div class="presta-card-avatar">
                        @if($prestataire->photo)
                            <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user->name }}">
                        @elseif($prestataire->user->avatar)
                            <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" alt="{{ $prestataire->user->name }}">
                        @else
                            <div class="presta-card-avatar-placeholder">
                                {{ strtoupper(substr($prestataire->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    {{-- Badges --}}
                    <div class="presta-card-badges">
                        @if($prestataire->isVerified())
                            <span class="presta-badge presta-badge-verified">
                                <i class="fas fa-check-circle"></i> Vérifié
                            </span>
                        @endif
                        @if($isTop)
                            <span class="presta-badge presta-badge-top">
                                <i class="fas fa-trophy"></i> Top
                            </span>
                        @elseif($isNew)
                            <span class="presta-badge presta-badge-new">
                                <i class="fas fa-sparkles"></i> Nouveau
                            </span>
                        @endif
                    </div>
                </div>
                
                {{-- Content --}}
                <div class="presta-card-content">
                    <div class="presta-card-header">
                        <div class="presta-card-name">
                            {{ $prestataire->user->name }}
                        </div>
                        @if($categoryName)
                            <span class="presta-card-category">
                                <i class="fas fa-tag"></i>
                                {{ $categoryName }}
                            </span>
                        @endif
                    </div>
                    
                    {{-- Rating --}}
                    <div class="presta-card-rating">
                        <div class="presta-rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($rating) ? '' : 'empty' }}"></i>
                            @endfor
                        </div>
                        @if($reviewCount > 0)
                            <span class="presta-rating-value">{{ number_format($rating, 1) }}</span>
                            <span class="presta-rating-count">({{ $reviewCount }} avis)</span>
                        @else
                            <span class="presta-rating-count">Pas encore d'avis</span>
                        @endif
                    </div>
                    
                    {{-- Description --}}
                    @if($prestataire->description)
                        <p class="presta-card-desc">
                            {{ Str::limit($prestataire->description, 100) }}
                        </p>
                    @endif
                    
                    {{-- Meta Info --}}
                    <div class="presta-card-meta">
                        @if($prestataire->city || $prestataire->address)
                            <div class="presta-meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $prestataire->city ?: $prestataire->address }}</span>
                            </div>
                        @endif
                        @if($prestataire->services_count ?? false)
                            <div class="presta-meta-item">
                                <i class="fas fa-tools"></i>
                                <span>{{ $prestataire->services_count }} services</span>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Actions --}}
                    <div class="presta-card-actions">
                        <a href="{{ route('prestataires.show', $prestataire) }}" class="presta-action-btn presta-action-primary">
                            <i class="fas fa-eye"></i>
                            Voir profil
                        </a>
                        @auth
                            @if(auth()->user()->isClient())
                                <a href="{{ route('client.messaging.start', $prestataire) }}" class="presta-action-icon" title="Contacter">
                                    <i class="fas fa-comment-dots"></i>
                                </a>
                                @if(auth()->user()->client && auth()->user()->client->isFollowing($prestataire->id))
                                    <form action="{{ route('client.prestataire-follows.unfollow', $prestataire) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="presta-action-icon active" title="Ne plus suivre">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('client.prestataire-follows.follow', $prestataire) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="presta-action-icon" title="Suivre">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="presta-empty">
                <div class="presta-empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Aucun prestataire trouvé</h3>
                <p>Modifiez vos critères de recherche ou explorez toutes les catégories disponibles</p>
                @if(request()->anyFilled(['name', 'category', 'subcategory', 'city']))
                    <a href="{{ route('prestataires.index') }}" class="presta-btn presta-btn-primary">
                        <i class="fas fa-undo"></i>
                        Voir tous les prestataires
                    </a>
                @endif
            </div>
        @endforelse
    </div>
    
    {{-- Pagination --}}
    @if($prestataires->hasPages())
        <div class="presta-pagination">
            {{ $prestataires->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Bouton scroll to top --}}
<button id="scrollTopBtn" class="scroll-top-btn">
    <i class="fas fa-chevron-up"></i>
</button>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Catégorie -> Sous-catégorie (if needed)
    const categorySelect = document.getElementById('category');
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            // Auto-submit on category change for faster filtering
            // document.getElementById('searchForm').submit();
        });
    }
    
    // Scroll to top button
    const scrollBtn = document.getElementById('scrollTopBtn');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });
    
    scrollBtn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Submit form on Enter in search input
    const searchInputs = document.querySelectorAll('.presta-input');
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchForm').submit();
            }
        });
    });
    
    // Lazy load images with Intersection Observer
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
});
</script>
@endpush
