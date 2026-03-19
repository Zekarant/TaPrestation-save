@extends('layouts.app')
 
@section('title', $prestataire->user->name . ' - Prestataire')
 
@push('styles')
<link rel="stylesheet" href="{{ asset('css/prestataire-profile.css') }}?v={{ time() }}">
<style>
/* ═══ FONT ═══ */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
 
/* ═══ PAGE ═══ */
.prestataire-profile-page {
    background: #f6f6fa;
    margin-top: 0 !important;
    padding-top: 0 !important;
    font-family: 'Outfit', system-ui, sans-serif;
    -webkit-font-smoothing: antialiased;
}
 
/* ═══ NAVBAR RESET ═══ */
#site-navbar,
nav#site-navbar,
nav#site-navbar.bg-white.shadow-sm {
    border-bottom: none !important;
    box-shadow: none !important;
    margin-bottom: 0 !important;
    border-radius: 0 !important;
    border: none !important;
}
 
/* ═══ SHOP HEADER ═══ */
.shop-header {
    position: -webkit-sticky !important;
    position: sticky !important;
    top: 70px !important;
    z-index: 45 !important;
    margin: 0 !important;
    box-shadow: none !important;
    border-top: none !important;
    background: #ffffff;
    border-bottom: 1px solid #ededf3;
    transition: box-shadow .2s ease;
}
.shop-header-content {
    padding: 10px 16px !important;
    max-width: 56rem;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: .6rem;
}
 
/* ═══ BACK BUTTON ═══ */
.shop-back-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.1rem;
    height: 2.1rem;
    flex-shrink: 0;
    border-radius: .55rem;
    background: #f1f1f6;
    color: #1a1a2e;
    text-decoration: none;
    transition: background .15s ease;
}
.shop-back-btn:active { background: #e0e0ea; }
.shop-back-btn svg { width: 1.1rem; height: 1.1rem; }
 
/* ═══ SELLER INFO ═══ */
.shop-seller {
    display: flex;
    align-items: center;
    gap: .55rem;
    flex: 1;
    min-width: 0;
}
.shop-seller-avatar {
    position: relative;
    width: 2.3rem;
    height: 2.3rem;
    flex-shrink: 0;
}
.shop-seller-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ededf3;
}
.shop-avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488, #0a7a70);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: .82rem;
    font-weight: 700;
}
.shop-verified {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #0d9488;
    color: #fff;
    font-size: .48rem;
    display: grid;
    place-items: center;
    border: 2px solid #fff;
    line-height: 1;
}
.shop-seller-info { min-width: 0; }
.shop-seller-name {
    font-size: .88rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
    font-family: 'Outfit', system-ui, sans-serif;
}
.shop-seller-meta {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-top: .1rem;
    font-size: .68rem;
    color: #8b8ba3;
    font-family: 'Outfit', system-ui, sans-serif;
}
.shop-status.online { color: #16a34a; font-weight: 600; }
.shop-rating { color: #d97706; font-weight: 600; }
 
/* ═══ CONTACT BUTTON ═══ */
.shop-contact-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.1rem;
    height: 2.1rem;
    flex-shrink: 0;
    border-radius: .55rem;
    background: #0d9488;
    color: #fff;
    text-decoration: none;
    font-size: .78rem;
    transition: background .15s ease;
}
.shop-contact-btn:active { background: #0a7a70; }
 
/* ═══ NAV BAR ═══ */
.shop-nav-bar {
    position: -webkit-sticky !important;
    position: sticky !important;
    top: calc(70px + var(--shop-header-h, 56px)) !important;
    z-index: 40 !important;
    background: white !important;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06) !important;
    margin: 0 !important;
    border-top: none !important;
}
.shop-nav-container {
    display: flex;
    gap: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    max-width: 56rem;
    margin: 0 auto;
}
.shop-nav-container::-webkit-scrollbar { display: none; }
 
.shop-nav-tab {
    display: flex;
    align-items: center;
    gap: .3rem;
    flex: 1 0 auto;
    justify-content: center;
    padding: .6rem .7rem;
    border: 0;
    background: transparent;
    color: #8b8ba3;
    font-family: 'Outfit', system-ui, sans-serif;
    font-size: .72rem;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    border-bottom: 2.5px solid transparent;
    transition: color .15s ease, border-color .15s ease;
    -webkit-tap-highlight-color: transparent;
}
.shop-nav-tab i { font-size: .68rem; }
.shop-nav-tab:active { color: #1a1a2e; }
.shop-nav-tab.active {
    color: #0d9488;
    border-bottom-color: #0d9488;
}
 
.tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.15rem;
    height: 1.15rem;
    padding: 0 .3rem;
    border-radius: 999px;
    background: #f1f1f6;
    font-size: .56rem;
    font-weight: 700;
    color: #8b8ba3;
    line-height: 1;
}
.shop-nav-tab.active .tab-badge {
    background: #e6f7f5;
    color: #0d9488;
}
 
/* ═══ TABS CONTENT ═══ */
.shop-tabs-content {
    max-width: 56rem;
    margin: 0 auto;
    padding: 0 .5rem;
}
 
/* ═══ PANEL HEADERS ═══ */
.panel-header {
    padding: 1rem .75rem .65rem;
    font-family: 'Outfit', system-ui, sans-serif;
}
.panel-header h2 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.panel-header h2 i { font-size: .85rem; color: #0d9488; }
.panel-header.teal h2 i { color: #0d9488; }
.panel-header.orange h2 i { color: #e8590c; }
.panel-header.green h2 i { color: #16a34a; }
.panel-header.gold h2 i { color: #d97706; }
.panel-header p {
    margin: .15rem 0 0;
    font-size: .72rem;
    color: #8b8ba3;
}
 
/* ═══ RATING SUMMARY ═══ */
.rating-summary {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-top: .5rem;
}
.big-rating {
    font-size: 2rem;
    font-weight: 800;
    color: #d97706;
    line-height: 1;
    font-family: 'Outfit', system-ui, sans-serif;
}
.rating-details { display: flex; flex-direction: column; gap: .1rem; }
.stars-display { display: flex; gap: .1rem; }
.stars-display i { font-size: .72rem; color: #e0e0ea; }
.stars-display i.filled { color: #d97706; }
.reviews-count { font-size: .68rem; color: #8b8ba3; }
 
/* ═══ TOOLBAR / FILTERS ═══ */
.panel-toolbar {
    padding: 0 .75rem .4rem;
    font-family: 'Outfit', system-ui, sans-serif;
}
.panel-toolbar-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
}
.panel-results { font-size: .78rem; font-weight: 700; color: #1a1a2e; }
.panel-results-note { font-size: .6rem; color: #8b8ba3; margin-top: .05rem; }
 
.panel-filter-toggle {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .35rem .65rem;
    border: 1px solid #ededf3;
    border-radius: 999px;
    background: #fff;
    color: #4a4a68;
    font-family: 'Outfit', system-ui, sans-serif;
    font-size: .7rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease;
    -webkit-tap-highlight-color: transparent;
}
.panel-filter-toggle i { font-size: .65rem; }
.panel-filter-toggle:active,
.panel-filter-toggle.active {
    border-color: #0d9488;
    color: #0d9488;
    background: #e6f7f5;
}
 
.panel-filters {
    max-height: 0;
    overflow: hidden;
    transition: max-height .25s ease, padding .25s ease;
    padding: 0;
}
.panel-filters.open {
    max-height: 20rem;
    padding: .55rem 0 .3rem;
}
 
.panel-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr));
    gap: .4rem;
}
.filter-field { display: flex; flex-direction: column; gap: .15rem; }
.filter-field span {
    font-size: .6rem;
    font-weight: 600;
    color: #8b8ba3;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.filter-field input,
.filter-field select {
    height: 2.1rem;
    padding: 0 .55rem;
    border: 1px solid #ededf3;
    border-radius: .55rem;
    background: #fff;
    color: #1a1a2e;
    font-family: 'Outfit', system-ui, sans-serif;
    font-size: .75rem;
    outline: none;
    transition: border-color .15s ease;
    -webkit-appearance: none;
    appearance: none;
}
.filter-field input:focus,
.filter-field select:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 2px rgba(13,148,136,.08);
}
 
.panel-filter-actions { display: flex; justify-content: flex-end; margin-top: .35rem; }
.panel-filter-reset {
    padding: .3rem .65rem;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #8b8ba3;
    font-family: 'Outfit', system-ui, sans-serif;
    font-size: .68rem;
    font-weight: 600;
    cursor: pointer;
}
.panel-filter-reset:active { color: #dc2626; }
 
.panel-filter-empty { padding: 2rem 1rem; text-align: center; color: #8b8ba3; }
.panel-filter-empty i { font-size: 1.5rem; margin-bottom: .4rem; display: block; }
.panel-filter-empty p { font-size: .8rem; margin: 0; }
 
.filterable-card.is-filter-hidden { display: none !important; }
 
/* ═══ CARD GRIDS ═══ */
.services-grid,
.equipment-grid,
.boutique-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .6rem;
    padding: .3rem .75rem;
}
.menu-grid {
    display: flex;
    flex-direction: column;
    gap: .45rem;
    padding: .3rem .75rem;
}
.reviews-list {
    display: flex;
    flex-direction: column;
    gap: .4rem;
    padding: .3rem .75rem;
}
@media (min-width: 640px) {
    .services-grid, .equipment-grid, .boutique-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (min-width: 900px) {
    .services-grid, .equipment-grid, .boutique-grid { grid-template-columns: repeat(4, 1fr); }
}
 
/* ═══ SERVICE CARD ═══ */
.service-card {
    background: #fff;
    border-radius: .75rem;
    overflow: hidden;
    border: 1px solid #ededf3;
    transition: box-shadow .15s ease;
}
.service-card:active { box-shadow: 0 2px 10px rgba(0,0,0,.06); }
 
.service-image {
    display: block;
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: #f1f1f6;
}
.service-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.service-no-image { width: 100%; height: 100%; display: grid; place-items: center; font-size: 1.3rem; color: #8b8ba3; }
.image-count {
    position: absolute; bottom: .35rem; right: .35rem;
    padding: .15rem .4rem; border-radius: 999px;
    background: rgba(0,0,0,.55); color: #fff;
    font-size: .56rem; font-weight: 600;
}
 
.service-body { padding: .55rem; }
.service-body h3 { margin: 0; font-size: .78rem; font-weight: 700; line-height: 1.25; font-family: 'Outfit', system-ui, sans-serif; }
.service-body h3 a { color: #1a1a2e; text-decoration: none; }
.service-desc {
    margin: .2rem 0 0; font-size: .66rem; color: #8b8ba3; line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
 
.item-meta-row { display: flex; flex-wrap: wrap; gap: .2rem; margin-top: .35rem; }
.item-chip {
    display: inline-flex; align-items: center; padding: .12rem .42rem;
    border-radius: 999px; background: #e6f7f5; color: #0d9488;
    font-size: .56rem; font-weight: 600; font-family: 'Outfit', system-ui, sans-serif;
}
.item-chip.muted { background: #f1f1f6; color: #8b8ba3; }
.item-chip.soft { background: #f1f1f6; color: #4a4a68; }
 
.service-footer { display: flex; align-items: center; justify-content: space-between; gap: .3rem; margin-top: .45rem; }
.price-value { font-size: .82rem; font-weight: 800; color: #1a1a2e; font-family: 'Outfit', system-ui, sans-serif; }
.price-quote { font-size: .7rem; font-weight: 600; color: #8b8ba3; font-style: italic; }
 
.btn-service {
    display: inline-flex; align-items: center; gap: .2rem;
    padding: .32rem .6rem; border-radius: 999px;
    background: #0d9488; color: #fff;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .62rem; font-weight: 700;
    text-decoration: none; white-space: nowrap;
    transition: background .15s ease;
}
.btn-service:active { background: #0a7a70; }
.btn-service i { font-size: .55rem; }
 
/* ═══ EQUIPMENT CARD ═══ */
.equipment-card {
    background: #fff; border-radius: .75rem;
    overflow: hidden; border: 1px solid #ededf3;
}
.equipment-image { display: block; position: relative; aspect-ratio: 4/3; overflow: hidden; background: #f1f1f6; }
.equipment-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.equipment-no-image { width: 100%; height: 100%; display: grid; place-items: center; font-size: 1.3rem; color: #8b8ba3; }
 
.availability-badge {
    position: absolute; top: .35rem; left: .35rem;
    padding: .12rem .45rem; border-radius: 999px;
    font-size: .56rem; font-weight: 700; font-family: 'Outfit', system-ui, sans-serif;
}
.availability-badge.available { background: #ecfdf5; color: #16a34a; }
.availability-badge.unavailable { background: #fef2f2; color: #dc2626; }
 
.equipment-body { padding: .55rem; }
.equipment-body h3 { margin: 0; font-size: .78rem; font-weight: 700; line-height: 1.25; font-family: 'Outfit', system-ui, sans-serif; }
.equipment-body h3 a { color: #1a1a2e; text-decoration: none; }
.equipment-desc {
    margin: .2rem 0 0; font-size: .66rem; color: #8b8ba3; line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
 
.equipment-pricing { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .35rem; }
.rate { font-size: .65rem; color: #4a4a68; font-family: 'Outfit', system-ui, sans-serif; }
.rate strong { font-size: .78rem; font-weight: 800; color: #1a1a2e; }
 
.btn-equipment {
    display: flex; align-items: center; justify-content: center; gap: .3rem;
    width: 100%; margin-top: .45rem; padding: .42rem;
    border-radius: .55rem; background: #0d9488; color: #fff;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .68rem; font-weight: 700;
    text-decoration: none;
}
.btn-equipment:active { background: #0a7a70; }
 
/* ═══ BOUTIQUE CARD ═══ */
.product-card-boutique {
    background: #fff; border-radius: .75rem;
    overflow: hidden; border: 1px solid #ededf3;
}
.product-image-wrap { display: block; position: relative; aspect-ratio: 1/1; overflow: hidden; background: #f1f1f6; }
.product-image-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
.product-no-image { width: 100%; height: 100%; display: grid; place-items: center; font-size: 1.3rem; color: #8b8ba3; }
 
.stock-badge {
    position: absolute; top: .35rem; left: .35rem;
    padding: .12rem .45rem; border-radius: 999px;
    font-size: .56rem; font-weight: 700; font-family: 'Outfit', system-ui, sans-serif;
}
.stock-badge.low { background: #fff4ed; color: #e8590c; }
.stock-badge.out { background: #fef2f2; color: #dc2626; }
 
.product-body { padding: .55rem; }
.product-body h3 { margin: 0; font-size: .76rem; font-weight: 700; line-height: 1.25; font-family: 'Outfit', system-ui, sans-serif; }
.product-body h3 a { color: #1a1a2e; text-decoration: none; }
.product-desc {
    margin: .15rem 0 0; font-size: .64rem; color: #8b8ba3; line-height: 1.3;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
 
.product-bottom { display: flex; align-items: center; justify-content: space-between; gap: .3rem; margin-top: .45rem; }
.current-price { font-size: .82rem; font-weight: 800; color: #1a1a2e; font-family: 'Outfit', system-ui, sans-serif; }
 
.btn-add-cart {
    display: inline-flex; align-items: center; gap: .2rem;
    padding: .32rem .55rem; border: 0; border-radius: 999px;
    background: #e8590c; color: #fff;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .62rem; font-weight: 700;
    cursor: pointer; text-decoration: none; white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}
.btn-add-cart i { font-size: .6rem; }
.btn-add-cart:active { transform: scale(.95); }
.btn-add-cart.added { background: #16a34a; }
 
.btn-out-of-stock {
    display: inline-flex; align-items: center; padding: .32rem .55rem;
    border-radius: 999px; background: #f1f1f6; color: #8b8ba3;
    font-size: .62rem; font-weight: 600;
}
.add-to-cart-form { display: inline; }
 
.cart-indicator { display: none; padding: .4rem .75rem; }
.cart-indicator.visible { display: block; }
.cart-btn {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    width: 100%; padding: .55rem;
    border-radius: .55rem; background: #e8590c; color: #fff;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .78rem; font-weight: 700;
    text-decoration: none;
}
.cart-btn:active { background: #d14e0a; }
.cart-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 1.2rem; height: 1.2rem; padding: 0 .3rem;
    border-radius: 999px; background: rgba(255,255,255,.2);
    font-size: .65rem; font-weight: 800;
}
 
/* ═══ MENU FOOD ═══ */
.menu-cta { padding: .3rem .75rem .4rem; }
.btn-full-menu {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    width: 100%; padding: .6rem;
    border-radius: .55rem; background: #16a34a; color: #fff;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .78rem; font-weight: 700;
    text-decoration: none;
}
.btn-full-menu:active { background: #138a3e; }
 
.menu-item-card {
    display: flex; gap: .55rem; padding: .55rem;
    background: #fff; border-radius: .75rem; border: 1px solid #ededf3;
}
.menu-item-image {
    width: 4.5rem; height: 4.5rem; flex-shrink: 0;
    border-radius: .55rem; overflow: hidden; background: #f1f1f6;
}
.menu-item-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.menu-no-image { width: 100%; height: 100%; display: grid; place-items: center; font-size: 1.2rem; }
 
.menu-item-body { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.menu-item-body h3 { margin: 0; font-size: .78rem; font-weight: 700; color: #1a1a2e; line-height: 1.2; font-family: 'Outfit', system-ui, sans-serif; }
.menu-item-body p { margin: .1rem 0 0; font-size: .64rem; color: #8b8ba3; line-height: 1.3; }
 
.menu-item-footer { display: flex; align-items: center; justify-content: space-between; gap: .3rem; margin-top: auto; padding-top: .3rem; }
.menu-price { font-size: .82rem; font-weight: 800; color: #16a34a; font-family: 'Outfit', system-ui, sans-serif; }
.btn-order-item {
    display: inline-flex; align-items: center; gap: .2rem;
    padding: .28rem .52rem; border-radius: 999px;
    background: #16a34a; color: #fff;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .62rem; font-weight: 700;
    text-decoration: none; white-space: nowrap;
}
 
/* ═══ REVIEWS ═══ */
.review-card {
    padding: .6rem; background: #fff;
    border-radius: .75rem; border: 1px solid #ededf3;
}
.review-header { display: flex; align-items: center; gap: .4rem; }
.reviewer-avatar {
    width: 1.9rem; height: 1.9rem; flex-shrink: 0; border-radius: 50%;
    background: #fffbeb; color: #d97706;
    display: grid; place-items: center;
    font-size: .7rem; font-weight: 700; font-family: 'Outfit', system-ui, sans-serif;
}
.reviewer-info { flex: 1; min-width: 0; }
.reviewer-name { font-size: .75rem; font-weight: 700; color: #1a1a2e; font-family: 'Outfit', system-ui, sans-serif; }
.review-stars { display: flex; gap: .05rem; }
.review-stars i { font-size: .56rem; color: #e0e0ea; }
.review-stars i.filled { color: #d97706; }
.review-date { font-size: .6rem; color: #8b8ba3; flex-shrink: 0; }
.review-text { margin: .35rem 0 0; font-size: .74rem; line-height: 1.45; color: #4a4a68; }
 
.review-form-section { padding: .6rem .75rem; }
.review-form-section h3 { font-size: .85rem; font-weight: 700; margin: 0 0 .4rem; font-family: 'Outfit', system-ui, sans-serif; }
.review-form { display: flex; flex-direction: column; gap: .4rem; }
.star-rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: .15rem; }
.star-rating-input input { display: none; }
.star-rating-input label { cursor: pointer; font-size: 1.2rem; color: #e0e0ea; transition: color .12s; }
.star-rating-input input:checked ~ label,
.star-rating-input label:hover,
.star-rating-input label:hover ~ label { color: #d97706; }
.review-form textarea {
    width: 100%; min-height: 4rem; padding: .55rem .65rem;
    border: 1px solid #ededf3; border-radius: .55rem;
    background: #fff; color: #1a1a2e;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .78rem; line-height: 1.4;
    resize: vertical; outline: none;
}
.review-form textarea:focus { border-color: #0d9488; }
.btn-submit-review {
    display: inline-flex; align-items: center; gap: .3rem;
    align-self: flex-start; padding: .45rem .85rem;
    border: 0; border-radius: 999px;
    background: #d97706; color: #fff;
    font-family: 'Outfit', system-ui, sans-serif; font-size: .75rem; font-weight: 700; cursor: pointer;
}
 
.no-reviews { padding: 2.5rem 1rem; text-align: center; color: #8b8ba3; }
.no-reviews i { font-size: 1.5rem; margin-bottom: .4rem; }
.no-reviews p { margin: .2rem 0; font-size: .85rem; font-weight: 600; }
.no-reviews span { font-size: .72rem; }
 
/* ═══ ABOUT & VIDEOS ═══ */
.about-section {
    margin: 1rem .75rem 0; padding: .7rem;
    background: #fff; border-radius: .75rem; border: 1px solid #ededf3;
}
.about-section h3 {
    margin: 0 0 .3rem; font-size: .82rem; font-weight: 700;
    display: flex; align-items: center; gap: .3rem;
    font-family: 'Outfit', system-ui, sans-serif;
}
.about-section h3 i { color: #0d9488; font-size: .72rem; }
.about-section p { margin: 0; font-size: .76rem; line-height: 1.5; color: #4a4a68; }
.about-contact-info { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .5rem; }
.about-contact-info span,
.about-contact-info a { display: inline-flex; align-items: center; gap: .25rem; font-size: .7rem; color: #8b8ba3; text-decoration: none; }
 
.videos-section {
    margin: .7rem .75rem 0; padding: .7rem;
    background: #fff; border-radius: .75rem; border: 1px solid #ededf3;
}
.videos-section h3 {
    margin: 0 0 .4rem; font-size: .82rem; font-weight: 700;
    display: flex; align-items: center; gap: .3rem;
    font-family: 'Outfit', system-ui, sans-serif;
}
.videos-section h3 i { color: #0d9488; font-size: .72rem; }
.videos-scroll {
    display: flex; gap: .4rem;
    overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;
}
.videos-scroll::-webkit-scrollbar { display: none; }
.video-thumb {
    position: relative; width: 8rem; height: 5.5rem; flex-shrink: 0;
    border-radius: .55rem; overflow: hidden; background: #f1f1f6; cursor: pointer;
}
.video-thumb img, .video-thumb video { width: 100%; height: 100%; object-fit: cover; display: block; }
.video-play {
    position: absolute; inset: 0; display: grid; place-items: center;
    background: rgba(0,0,0,.3); color: #fff; font-size: 1rem;
}
 
.video-modal { display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,.85); place-items: center; }
.video-modal.active { display: grid; }
.video-modal-content { position: relative; width: min(92vw, 40rem); max-height: 85vh; }
.video-modal-content video { width: 100%; max-height: 85vh; border-radius: .75rem; background: #000; }
.video-modal-close {
    position: absolute; top: -.6rem; right: -.6rem;
    width: 2rem; height: 2rem; border: 0; border-radius: 50%;
    background: #fff; color: #1a1a2e; font-size: .85rem;
    cursor: pointer; display: grid; place-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,.12);
}
 
/* ═══ FLOATING FOLLOW ═══ */
.floating-action {
    position: fixed; left: .75rem;
    bottom: calc(env(safe-area-inset-bottom, 0px) + .75rem);
    z-index: 60;
}
.floating-form { display: inline; }
.floating-btn {
    width: 2.6rem; height: 2.6rem; border: 0; border-radius: 50%;
    display: grid; place-items: center; font-size: .95rem;
    cursor: pointer; box-shadow: 0 4px 16px rgba(0,0,0,.12);
}
.floating-btn:active { transform: scale(.9); }
.floating-btn.secondary { background: #fff; color: #8b8ba3; }
.floating-btn.danger { background: #dc2626; color: #fff; }
 
/* ═══ TOASTS ═══ */
.cart-toast {
    position: fixed;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 1rem);
    left: 50%;
    transform: translateX(-50%) translateY(1rem);
    padding: .55rem 1.1rem;
    border-radius: 999px;
    font-family: 'Outfit', system-ui, sans-serif;
    font-size: .75rem;
    font-weight: 700;
    z-index: 9999;
    opacity: 0;
    transition: all .25s ease;
    pointer-events: none;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    display: flex;
    align-items: center;
    gap: .3rem;
}
.cart-toast.visible { opacity: 1; transform: translateX(-50%) translateY(0); }
.cart-toast.success { background: #065f46; color: white; }
.cart-toast.error { background: #991b1b; color: white; }
 
/* ═══ MOBILE ═══ */
@media (max-width: 768px) {
    body.prestataire-profile-immersive,
    body:has(.prestataire-profile-page) {
        padding-top: 0 !important;
    }
    body.prestataire-profile-immersive #site-navbar,
    body:has(.prestataire-profile-page) #site-navbar,
    body.prestataire-profile-immersive #mobile-bottom-nav,
    body:has(.prestataire-profile-page) #mobile-bottom-nav {
        display: none !important;
    }
    body.prestataire-profile-immersive main,
    body:has(.prestataire-profile-page) main {
        padding-top: 0 !important;
        padding-bottom: 8px !important;
    }
    body.prestataire-profile-immersive .prestataire-profile-page,
    body:has(.prestataire-profile-page) .prestataire-profile-page {
        padding-bottom: 24px !important;
    }
    body.prestataire-profile-immersive .shop-header,
    body:has(.prestataire-profile-page) .shop-header {
        top: 0 !important;
    }
    body.prestataire-profile-immersive .shop-nav-bar,
    body:has(.prestataire-profile-page) .shop-nav-bar {
        top: var(--shop-header-h, 56px) !important;
    }
    body.prestataire-profile-immersive .shop-header-content,
    body:has(.prestataire-profile-page) .shop-header-content {
        padding: 8px 12px !important;
    }
    main {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    .prestataire-profile-page {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    .shop-header {
        margin-top: 0 !important;
        border-radius: 0 !important;
    }
}
@media (min-width: 640px) {
    .shop-header { top: 80px !important; }
    .shop-nav-bar { top: calc(80px + var(--shop-header-h, 56px)) !important; }
}
 
/* ═══ PWA SAFE AREAS ═══ */
@supports (padding-top: env(safe-area-inset-top)) {
    body.app-mode .shop-header {
        top: calc(70px + env(safe-area-inset-top)) !important;
    }
    body.app-mode .shop-nav-bar {
        top: calc(70px + var(--shop-header-h, 56px) + env(safe-area-inset-top)) !important;
    }
    body.app-mode.prestataire-profile-immersive .shop-header {
        top: 0 !important;
    }
    body.app-mode.prestataire-profile-immersive .shop-nav-bar {
        top: var(--shop-header-h, 56px) !important;
    }
    body.app-mode.prestataire-profile-immersive .shop-header-content {
        padding-top: calc(env(safe-area-inset-top) + 4px) !important;
        padding-bottom: 6px !important;
    }
}
@media (display-mode: standalone) {
    @supports (padding-top: env(safe-area-inset-top)) {
        .shop-header { top: calc(70px + env(safe-area-inset-top)) !important; }
        .shop-nav-bar { top: calc(70px + var(--shop-header-h, 56px) + env(safe-area-inset-top)) !important; }
    }
}
</style>
@endpush
 
@push('scripts')
<script>
/* Mesurer les hauteurs réelles des barres sticky */
(function () {
    document.body.classList.add('prestataire-profile-immersive');
 
    function measure() {
        var siteNav = document.getElementById('site-navbar');
        var shopH = document.querySelector('.shop-header');
        var navBar = document.querySelector('.shop-nav-bar');
        if (siteNav) {
            var h = siteNav.getBoundingClientRect().height;
            if (h && isFinite(h)) document.documentElement.style.setProperty('--site-nav-h', h + 'px');
        }
        if (shopH) {
            var sh = shopH.getBoundingClientRect().height;
            if (sh && isFinite(sh)) document.documentElement.style.setProperty('--shop-header-h', sh + 'px');
        }
        if (navBar) {
            var nh = navBar.getBoundingClientRect().height;
            if (nh && isFinite(nh)) document.documentElement.style.setProperty('--shop-nav-h', nh + 'px');
        }
 
        var total = (siteNav ? siteNav.getBoundingClientRect().height : 0)
            + (shopH ? shopH.getBoundingClientRect().height : 0)
            + (navBar ? navBar.getBoundingClientRect().height : 0)
            + 24;
 
        if (total && isFinite(total)) {
            document.documentElement.style.setProperty('--profile-sticky-offset', total + 'px');
        }
    }
    /* Mesurer au DOMContentLoaded */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', measure, { once: true });
    } else {
        measure();
    }
    /* Re-mesurer après chargement complet (CSS différés chargés) */
    window.addEventListener('load', function () {
        requestAnimationFrame(function() {
            measure();
            /* Re-mesurer encore après 500ms pour les polices/CSS tardifs */
            setTimeout(measure, 500);
        });
    }, { once: true });
    window.addEventListener('resize', function () { requestAnimationFrame(measure); }, { passive: true });
})();
</script>
@endpush
 
@section('content')
@php
    $totalReviews = $allReviews->count();
    $averageRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 0;
    $hasProducts = $allUrgentSales->count() > 0;
    $hasFood = isset($foodProducts) && $foodProducts->count() > 0;
    $hasVideos = $prestataire->videos && $prestataire->videos->count() > 0;
    $hasServices = $allServices->count() > 0;
    $hasEquipments = $allEquipments->count() > 0;
    $isOnline = $prestataire->user->is_online ?? false;
    
    // Calcul des tabs actifs
    $tabs = [];
    if($hasServices) $tabs[] = ['id' => 'services', 'icon' => 'fa-concierge-bell', 'label' => 'Services', 'count' => $allServices->count()];
    if($hasEquipments) $tabs[] = ['id' => 'location', 'icon' => 'fa-tools', 'label' => 'Location', 'count' => $allEquipments->count()];
    if($hasProducts) $tabs[] = ['id' => 'boutique', 'icon' => 'fa-store', 'label' => 'Boutique', 'count' => $allUrgentSales->count()];
    if($hasFood) $tabs[] = ['id' => 'menu', 'icon' => 'fa-utensils', 'label' => 'Menu', 'count' => $foodProducts->count()];
    $tabs[] = ['id' => 'avis', 'icon' => 'fa-star', 'label' => 'Avis', 'count' => $totalReviews];
 
    $serviceCategories = $allServices
        ->flatMap(fn ($service) => $service->categories?->pluck('name') ?? collect())
        ->filter()
        ->unique()
        ->sort()
        ->values();
 
    $equipmentCategories = $allEquipments
        ->pluck('category.name')
        ->filter()
        ->unique()
        ->sort()
        ->values();
 
    $equipmentConditions = $allEquipments
        ->pluck('condition')
        ->filter()
        ->unique()
        ->values();
 
    $urgentCategories = $allUrgentSales
        ->pluck('category.name')
        ->filter()
        ->unique()
        ->sort()
        ->values();
 
    $urgentConditions = collect(\App\Models\UrgentSale::CONDITION_OPTIONS)
        ->only($allUrgentSales->pluck('condition')->filter()->unique()->values()->all());
 
    $foodCategoryOptions = collect(\App\Models\FoodProduct::categories())
        ->only(($foodProducts ?? collect())->pluck('category')->filter()->unique()->values()->all());
 
    $defaultActiveTab = $tabs[0]['id'] ?? 'avis';
@endphp
 
<div class="prestataire-profile-page">
    
    {{-- HEADER COMPACT --}}
    <div class="shop-header">
        <div class="shop-header-content">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('services.index') }}" class="shop-back-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            
            <div class="shop-seller">
                <div class="shop-seller-avatar">
                    @php
                        $avatarUrl = null;
                        if ($prestataire->photo) {
                            $avatarUrl = filter_var($prestataire->photo, FILTER_VALIDATE_URL) ? $prestataire->photo : asset('storage/' . $prestataire->photo);
                        } elseif ($prestataire->user->avatar) {
                            $avatarUrl = filter_var($prestataire->user->avatar, FILTER_VALIDATE_URL) ? $prestataire->user->avatar : asset('storage/' . $prestataire->user->avatar);
                        } elseif ($prestataire->user->profile_photo_url) {
                            $avatarUrl = $prestataire->user->profile_photo_url;
                        }
                    @endphp
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $prestataire->user->name }}">
                    @else
                        <div class="shop-avatar-placeholder">{{ strtoupper(substr($prestataire->user->name, 0, 1)) }}</div>
                    @endif
                    @if($prestataire->isVerified())
                        <span class="shop-verified">✓</span>
                    @endif
                </div>
                <div class="shop-seller-info">
                    <h1 class="shop-seller-name">{{ $prestataire->user->name }}</h1>
                    <div class="shop-seller-meta">
                        <span class="shop-status {{ $isOnline ? 'online' : '' }}">{{ $isOnline ? '● En ligne' : '○ Hors ligne' }}</span>
                        @if($totalReviews > 0)
                            <span class="shop-rating">★ {{ $averageRating }} ({{ $totalReviews }})</span>
                        @endif
                    </div>
                </div>
            </div>
            
            @auth
                @if(auth()->user()->isClient())
                    <a href="{{ route('client.messaging.start', $prestataire) }}" class="shop-contact-btn">
                        <i class="fas fa-comment-dots"></i>
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="shop-contact-btn">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
            @endauth
        </div>
    </div>
 
    {{-- BARRE DE NAVIGATION FLOTTANTE STICKY --}}
    <nav class="shop-nav-bar">
        <div class="shop-nav-container">
            @foreach($tabs as $index => $tab)
                <button class="shop-nav-tab {{ $index === 0 ? 'active' : '' }}" data-tab="{{ $tab['id'] }}">
                    <i class="fas {{ $tab['icon'] }}"></i>
                    <span>{{ $tab['label'] }}</span>
                    @if($tab['count'] > 0)
                        <span class="tab-badge">{{ $tab['count'] }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </nav>
 
    {{-- CONTENU PAR ONGLETS --}}
    <div class="shop-tabs-content">
        
        {{-- TAB: SERVICES --}}
        @if($hasServices)
        <section class="tab-panel active" id="panel-services">
            <div class="panel-header">
                <h2><i class="fas fa-concierge-bell"></i> Nos Services</h2>
                <p>Découvrez nos prestations professionnelles</p>
            </div>
            <div class="panel-toolbar" data-filter-group="services">
                <div class="panel-toolbar-main">
                    <div>
                        <div class="panel-results" id="results-services">{{ $allServices->count() }} service{{ $allServices->count() > 1 ? 's' : '' }}</div>
                        <div class="panel-results-note">Recherche instantanée sur les prestations de ce profil.</div>
                    </div>
                    <button type="button" class="panel-filter-toggle" data-filter-toggle="services">
                        <i class="fas fa-sliders-h"></i>
                        Filtres
                    </button>
                </div>
                <div class="panel-filters" id="filters-services">
                    <div class="panel-filter-grid">
                        <label class="filter-field">
                            <span>Recherche</span>
                            <input type="search" data-filter-input="services-search" placeholder="Titre ou mot-clé">
                        </label>
                        <label class="filter-field">
                            <span>Catégorie</span>
                            <select data-filter-input="services-category">
                                <option value="">Toutes</option>
                                @foreach($serviceCategories as $categoryName)
                                    <option value="{{ \Illuminate\Support\Str::slug($categoryName) }}">{{ $categoryName }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Tarif</span>
                            <select data-filter-input="services-pricing">
                                <option value="">Tous</option>
                                <option value="priced">Avec prix</option>
                                <option value="quote">Sur devis</option>
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Prix max (€)</span>
                            <input type="number" min="0" step="1" data-filter-input="services-max-price" placeholder="Ex: 150">
                        </label>
                    </div>
                    <div class="panel-filter-actions">
                        <button type="button" class="panel-filter-reset" data-filter-reset="services">Réinitialiser</button>
                    </div>
                </div>
            </div>
            <div class="services-grid">
                @foreach($allServices as $service)
                @php
                    $serviceCategoryNames = $service->categories?->pluck('name')->filter()->values() ?? collect();
                    $serviceCategorySlugs = $serviceCategoryNames->map(fn ($name) => \Illuminate\Support\Str::slug($name))->implode(' ');
                    $serviceSearch = mb_strtolower(trim($service->title . ' ' . strip_tags($service->description ?? '') . ' ' . $serviceCategoryNames->implode(' ')));
                @endphp
                <article
                    class="service-card filterable-card"
                    data-filter-group="services"
                    data-search="{{ $serviceSearch }}"
                    data-category="{{ $serviceCategorySlugs }}"
                    data-price="{{ $service->price !== null ? (float) $service->price : '' }}"
                    data-price-mode="{{ $service->price !== null ? 'priced' : 'quote' }}"
                >
                    <a href="{{ route('services.show', $service) }}" class="service-image">
                        @if($service->images && $service->images->count() > 0)
                            <img src="{{ asset('storage/' . $service->images->first()->image_path) }}" alt="{{ $service->title }}" loading="lazy">
                        @else
                            <div class="service-no-image">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                        @endif
                        @if($service->images && $service->images->count() > 1)
                            <span class="image-count"><i class="fas fa-images"></i> {{ $service->images->count() }}</span>
                        @endif
                    </a>
                    <div class="service-body">
                        <h3><a href="{{ route('services.show', $service) }}">{{ $service->title }}</a></h3>
                        <p class="service-desc">{{ Str::limit($service->description, 80) }}</p>
                        @if($serviceCategoryNames->isNotEmpty() || $service->delivery_time)
                            <div class="item-meta-row">
                                @foreach($serviceCategoryNames->take(2) as $categoryName)
                                    <span class="item-chip">{{ $categoryName }}</span>
                                @endforeach
                                @if($service->delivery_time)
                                    <span class="item-chip muted">{{ $service->delivery_time }}</span>
                                @endif
                            </div>
                        @endif
                        <div class="service-footer">
                            <div class="service-price">
                                @if($service->price)
                                    <span class="price-value">{{ number_format($service->price, 0, ',', ' ') }} €</span>
                                @else
                                    <span class="price-quote">Sur devis</span>
                                @endif
                            </div>
                            <a href="{{ route('services.show', $service) }}" class="btn-service">
                                Réserver <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            <div class="panel-filter-empty" id="empty-services" hidden>
                <i class="fas fa-search"></i>
                <p>Aucun service ne correspond à ces filtres.</p>
            </div>
        </section>
        @endif
 
        {{-- TAB: LOCATION / ÉQUIPEMENTS --}}
        @if($hasEquipments)
        <section class="tab-panel {{ !$hasServices ? 'active' : '' }}" id="panel-location">
            <div class="panel-header teal">
                <h2><i class="fas fa-tools"></i> Location de Matériel</h2>
                <p>Équipements professionnels disponibles à la location</p>
            </div>
            <div class="panel-toolbar" data-filter-group="location">
                <div class="panel-toolbar-main">
                    <div>
                        <div class="panel-results" id="results-location">{{ $allEquipments->count() }} équipement{{ $allEquipments->count() > 1 ? 's' : '' }}</div>
                        <div class="panel-results-note">Filtrez le matériel par mot-clé, catégorie, état ou budget.</div>
                    </div>
                    <button type="button" class="panel-filter-toggle" data-filter-toggle="location">
                        <i class="fas fa-sliders-h"></i>
                        Filtres
                    </button>
                </div>
                <div class="panel-filters" id="filters-location">
                    <div class="panel-filter-grid">
                        <label class="filter-field">
                            <span>Recherche</span>
                            <input type="search" data-filter-input="location-search" placeholder="Nom, marque, modèle">
                        </label>
                        <label class="filter-field">
                            <span>Catégorie</span>
                            <select data-filter-input="location-category">
                                <option value="">Toutes</option>
                                @foreach($equipmentCategories as $categoryName)
                                    <option value="{{ \Illuminate\Support\Str::slug($categoryName) }}">{{ $categoryName }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>État</span>
                            <select data-filter-input="location-condition">
                                <option value="">Tous</option>
                                @foreach($equipmentConditions as $condition)
                                    <option value="{{ $condition }}">{{ ucfirst(str_replace('_', ' ', $condition)) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Prix max / jour (€)</span>
                            <input type="number" min="0" step="1" data-filter-input="location-max-price" placeholder="Ex: 80">
                        </label>
                    </div>
                    <div class="panel-filter-actions">
                        <button type="button" class="panel-filter-reset" data-filter-reset="location">Réinitialiser</button>
                    </div>
                </div>
            </div>
            <div class="equipment-grid">
                @foreach($allEquipments as $equipment)
                @php
                    $equipmentCategoryName = $equipment->category?->name;
                    $equipmentSearch = mb_strtolower(trim(
                        $equipment->name . ' ' .
                        strip_tags($equipment->description ?? '') . ' ' .
                        ($equipment->brand ?? '') . ' ' .
                        ($equipment->model ?? '') . ' ' .
                        ($equipmentCategoryName ?? '') . ' ' .
                        ($equipment->condition ?? '')
                    ));
                    $equipmentPriceReference = $equipment->price_per_day ?? $equipment->price_per_hour ?? $equipment->price_per_week ?? null;
                @endphp
                <article
                    class="equipment-card filterable-card"
                    data-filter-group="location"
                    data-search="{{ $equipmentSearch }}"
                    data-category="{{ $equipmentCategoryName ? \Illuminate\Support\Str::slug($equipmentCategoryName) : '' }}"
                    data-condition="{{ $equipment->condition ?? '' }}"
                    data-price="{{ $equipmentPriceReference !== null ? (float) $equipmentPriceReference : '' }}"
                >
                    <a href="{{ route('equipment.show', $equipment) }}" class="equipment-image">
                        @if($equipment->main_photo)
                            <img src="{{ asset('storage/' . $equipment->main_photo) }}" alt="{{ $equipment->name }}" loading="lazy">
                        @else
                            <div class="equipment-no-image">
                                <i class="fas fa-tools"></i>
                            </div>
                        @endif
                        @if($equipment->is_available)
                            <span class="availability-badge available">Disponible</span>
                        @else
                            <span class="availability-badge unavailable">Réservé</span>
                        @endif
                    </a>
                    <div class="equipment-body">
                        <h3><a href="{{ route('equipment.show', $equipment) }}">{{ $equipment->name }}</a></h3>
                        <p class="equipment-desc">{{ Str::limit($equipment->description, 70) }}</p>
                        @if($equipmentCategoryName || $equipment->condition || $equipment->brand)
                            <div class="item-meta-row">
                                @if($equipmentCategoryName)
                                    <span class="item-chip">{{ $equipmentCategoryName }}</span>
                                @endif
                                @if($equipment->condition)
                                    <span class="item-chip muted">{{ ucfirst(str_replace('_', ' ', $equipment->condition)) }}</span>
                                @endif
                                @if($equipment->brand)
                                    <span class="item-chip soft">{{ $equipment->brand }}</span>
                                @endif
                            </div>
                        @endif
                        <div class="equipment-pricing">
                            @if($equipment->price_per_hour)
                                <span class="rate"><strong>{{ number_format($equipment->price_per_hour, 0, ',', ' ') }} €</strong>/h</span>
                            @endif
                            @if($equipment->price_per_day)
                                <span class="rate"><strong>{{ number_format($equipment->price_per_day, 0, ',', ' ') }} €</strong>/jour</span>
                            @endif
                        </div>
                        <a href="{{ route('equipment.show', $equipment) }}" class="btn-equipment">
                            <i class="fas fa-calendar-check"></i> Réserver
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
            <div class="panel-filter-empty" id="empty-location" hidden>
                <i class="fas fa-toolbox"></i>
                <p>Aucun équipement ne correspond à ces filtres.</p>
            </div>
        </section>
        @endif
 
        {{-- TAB: BOUTIQUE --}}
        @if($hasProducts)
        <section class="tab-panel" id="panel-boutique">
            <div class="panel-header orange">
                <h2><i class="fas fa-store"></i> Notre Boutique</h2>
                <p>{{ $allUrgentSales->count() }} articles disponibles</p>
            </div>
            <div class="panel-toolbar" data-filter-group="boutique">
                <div class="panel-toolbar-main">
                    <div>
                        <div class="panel-results" id="results-boutique">{{ $allUrgentSales->count() }} annonce{{ $allUrgentSales->count() > 1 ? 's' : '' }}</div>
                        <div class="panel-results-note">Affinez la boutique par recherche, prix, état ou disponibilité.</div>
                    </div>
                    <button type="button" class="panel-filter-toggle" data-filter-toggle="boutique">
                        <i class="fas fa-sliders-h"></i>
                        Filtres
                    </button>
                </div>
                <div class="panel-filters" id="filters-boutique">
                    <div class="panel-filter-grid">
                        <label class="filter-field">
                            <span>Recherche</span>
                            <input type="search" data-filter-input="boutique-search" placeholder="Titre ou mot-clé">
                        </label>
                        <label class="filter-field">
                            <span>Catégorie</span>
                            <select data-filter-input="boutique-category">
                                <option value="">Toutes</option>
                                @foreach($urgentCategories as $categoryName)
                                    <option value="{{ \Illuminate\Support\Str::slug($categoryName) }}">{{ $categoryName }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>État</span>
                            <select data-filter-input="boutique-condition">
                                <option value="">Tous</option>
                                @foreach($urgentConditions as $conditionValue => $conditionLabel)
                                    <option value="{{ $conditionValue }}">{{ $conditionLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Stock</span>
                            <select data-filter-input="boutique-stock">
                                <option value="">Tous</option>
                                <option value="in">En stock</option>
                                <option value="low">Stock faible</option>
                                <option value="out">Épuisé</option>
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Prix max (€)</span>
                            <input type="number" min="0" step="1" data-filter-input="boutique-max-price" placeholder="Ex: 250">
                        </label>
                    </div>
                    <div class="panel-filter-actions">
                        <button type="button" class="panel-filter-reset" data-filter-reset="boutique">Réinitialiser</button>
                    </div>
                </div>
            </div>
            
            {{-- Indicateur panier flottant dans la boutique --}}
            @auth
            @php
                $cartItemCount = 0;
                try {
                    $userCart = \App\Models\Cart::forUserActive(auth()->id());
                    if ($userCart) {
                        $cartItemCount = $userCart->items()->sum('quantity');
                    }
                } catch (\Exception $e) {}
            @endphp
            <div class="cart-indicator {{ $cartItemCount > 0 ? 'visible' : '' }}" id="cart-indicator">
                <a href="{{ route('client.cart.index') }}" class="cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cart-count">{{ $cartItemCount }}</span>
                    <span class="cart-text">Voir le panier</span>
                </a>
            </div>
            @endauth
 
            <div class="boutique-grid">
                @foreach($allUrgentSales as $sale)
                @php
                    $availableQty = ($sale->quantity ?? 1) - ($sale->reserved_quantity ?? 0) - ($sale->sold_quantity ?? 0);
                    $stockState = $availableQty <= 0 ? 'out' : ($availableQty <= 3 ? 'low' : 'in');
                    $saleCategoryName = $sale->category?->name;
                    $saleSearch = mb_strtolower(trim(
                        $sale->title . ' ' .
                        strip_tags($sale->description ?? '') . ' ' .
                        ($saleCategoryName ?? '') . ' ' .
                        ($sale->condition ?? '')
                    ));
                @endphp
                <article
                    class="product-card-boutique filterable-card"
                    data-product-id="{{ $sale->id }}"
                    data-filter-group="boutique"
                    data-search="{{ $saleSearch }}"
                    data-category="{{ $saleCategoryName ? \Illuminate\Support\Str::slug($saleCategoryName) : '' }}"
                    data-condition="{{ $sale->condition ?? '' }}"
                    data-stock="{{ $stockState }}"
                    data-price="{{ (float) $sale->price }}"
                >
                    <a href="{{ route('urgent-sales.show', $sale) }}" class="product-image-wrap">
                        @if($sale->photos && count($sale->photos) > 0)
                            @php $firstPhoto = $sale->photos[0]; @endphp
                            @if(filter_var($firstPhoto, FILTER_VALIDATE_URL))
                                <img src="{{ $firstPhoto }}" alt="{{ $sale->title }}" loading="lazy">
                            @else
                                <x-media-image :path="$firstPhoto" :alt="$sale->title" class="w-full h-full object-cover" />
                            @endif
                        @else
                            <div class="product-no-image">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        @endif
                        @if($availableQty <= 3 && $availableQty > 0)
                            <span class="stock-badge low">Plus que {{ $availableQty }}!</span>
                        @elseif($availableQty <= 0)
                            <span class="stock-badge out">Épuisé</span>
                        @endif
                    </a>
                    <div class="product-body">
                        <h3><a href="{{ route('urgent-sales.show', $sale) }}">{{ $sale->title }}</a></h3>
                        <p class="product-desc">{{ Str::limit($sale->description, 60) }}</p>
                        @if($saleCategoryName || $sale->condition || $availableQty >= 0)
                            <div class="item-meta-row">
                                @if($saleCategoryName)
                                    <span class="item-chip">{{ $saleCategoryName }}</span>
                                @endif
                                @if($sale->condition)
                                    <span class="item-chip muted">{{ \App\Models\UrgentSale::CONDITION_OPTIONS[$sale->condition] ?? ucfirst($sale->condition) }}</span>
                                @endif
                                <span class="item-chip soft">
                                    {{ $availableQty > 0 ? $availableQty . ' dispo' : 'Épuisé' }}
                                </span>
                            </div>
                        @endif
                        <div class="product-bottom">
                            <div class="product-price-tag">
                                <span class="current-price">{{ number_format($sale->price, 2, ',', ' ') }} €</span>
                            </div>
                            @if($availableQty > 0)
                                @auth
                                    @php
                                        $sellerId = (int) ($sale->prestataire?->user_id ?? 0);
                                        $isOwnSale = (int) auth()->id() === $sellerId;
                                        $hasOnlinePayment = (function_exists('normalize_payment_requirement_for_mode')
                                            ? normalize_payment_requirement_for_mode($sale->payment_requirement ?? 'none')
                                            : ($sale->payment_requirement ?? 'none')) === 'full';
                                        $hasStripeConnect = !empty($sale->prestataire?->stripe_account_id);
                                        $canAddToCart = !$isOwnSale
                                            && $hasOnlinePayment
                                            && $hasStripeConnect
                                            && \Illuminate\Support\Facades\Route::has('client.cart.add.urgent-sale');
                                    @endphp
 
                                    @if($canAddToCart)
                                        <form method="POST" action="{{ route('client.cart.add.urgent-sale', $sale) }}" class="add-to-cart-form">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn-add-cart">
                                                <i class="fas fa-cart-plus"></i>
                                                <span>Ajouter</span>
                                            </button>
                                        </form>
                                    @elseif($isOwnSale)
                                        <span class="btn-out-of-stock">Votre annonce</span>
                                    @else
                                        <a href="{{ route('urgent-sales.show', $sale) }}" class="btn-add-cart">
                                            <i class="fas fa-comments"></i>
                                            <span>{{ $hasOnlinePayment ? 'Contacter' : 'Réserver' }}</span>
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn-add-cart">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Connexion</span>
                                    </a>
                                @endauth
                            @else
                                <span class="btn-out-of-stock">Épuisé</span>
                            @endif
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            <div class="panel-filter-empty" id="empty-boutique" hidden>
                <i class="fas fa-store-slash"></i>
                <p>Aucune annonce ne correspond à ces filtres.</p>
            </div>
        </section>
        @endif
 
        {{-- TAB: MENU FOOD --}}
        @if($hasFood)
        <section class="tab-panel" id="panel-menu">
            <div class="panel-header green">
                <h2><i class="fas fa-utensils"></i> Notre Menu</h2>
                <p>Commandez et faites-vous livrer</p>
            </div>
            <div class="panel-toolbar" data-filter-group="menu">
                <div class="panel-toolbar-main">
                    <div>
                        <div class="panel-results" id="results-menu">{{ $foodProducts->count() }} plat{{ $foodProducts->count() > 1 ? 's' : '' }}</div>
                        <div class="panel-results-note">Préfiltrez le menu avant d'ouvrir la commande complète.</div>
                    </div>
                    <button type="button" class="panel-filter-toggle" data-filter-toggle="menu">
                        <i class="fas fa-sliders-h"></i>
                        Filtres
                    </button>
                </div>
                <div class="panel-filters" id="filters-menu">
                    <div class="panel-filter-grid">
                        <label class="filter-field">
                            <span>Recherche</span>
                            <input type="search" data-filter-input="menu-search" placeholder="Nom ou ingrédient">
                        </label>
                        <label class="filter-field">
                            <span>Catégorie</span>
                            <select data-filter-input="menu-category">
                                <option value="">Toutes</option>
                                @foreach($foodCategoryOptions as $foodCategoryValue => $foodCategoryLabel)
                                    <option value="{{ $foodCategoryValue }}">{{ $foodCategoryLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Disponibilité</span>
                            <select data-filter-input="menu-preorder">
                                <option value="">Toutes</option>
                                <option value="immediate">Disponible maintenant</option>
                                <option value="advance">Sur commande</option>
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Prix max (€)</span>
                            <input type="number" min="0" step="1" data-filter-input="menu-max-price" placeholder="Ex: 20">
                        </label>
                    </div>
                    <div class="panel-filter-actions">
                        <button type="button" class="panel-filter-reset" data-filter-reset="menu">Réinitialiser</button>
                    </div>
                </div>
            </div>
            
            {{-- Lien vers la page complète de commande --}}
            <div class="menu-cta">
                <a href="{{ route('food.menu', $prestataire) }}" class="btn-full-menu">
                    <i class="fas fa-book-open"></i>
                    Voir le menu complet & Commander
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
 
            <div class="menu-grid">
                @foreach($foodProducts as $product)
                @php
                    $menuSearch = mb_strtolower(trim($product->name . ' ' . strip_tags($product->description ?? '') . ' ' . ($product->category ?? '')));
                    $menuCategoryLabel = \App\Models\FoodProduct::categories()[$product->category] ?? ucfirst($product->category ?? 'Autre');
                @endphp
                <article
                    class="menu-item-card filterable-card"
                    data-filter-group="menu"
                    data-search="{{ $menuSearch }}"
                    data-category="{{ $product->category ?? '' }}"
                    data-preorder="{{ $product->requiresAdvanceOrder() ? 'advance' : 'immediate' }}"
                    data-price="{{ (float) $product->price }}"
                >
                    <div class="menu-item-image">
                        @if($product->image)
                            <img src="{{ storage_asset_url($product->image) }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <div class="menu-no-image">🍽️</div>
                        @endif
                    </div>
                    <div class="menu-item-body">
                        <h3>{{ $product->name }}</h3>
                        @if($product->description)
                            <p>{{ Str::limit($product->description, 50) }}</p>
                        @endif
                        <div class="item-meta-row">
                            <span class="item-chip">{{ $menuCategoryLabel }}</span>
                            @if($product->requiresAdvanceOrder())
                                <span class="item-chip muted">{{ $product->advance_order_label }}</span>
                            @endif
                        </div>
                        <div class="menu-item-footer">
                            <span class="menu-price">{{ number_format($product->price, 2, ',', ' ') }} €</span>
                            <a href="{{ route('food.menu', $prestataire) }}" class="btn-order-item">
                                <i class="fas fa-plus"></i> Commander
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            <div class="panel-filter-empty" id="empty-menu" hidden>
                <i class="fas fa-utensils"></i>
                <p>Aucun plat ne correspond à ces filtres.</p>
            </div>
        </section>
        @endif
 
        {{-- TAB: AVIS --}}
        <section class="tab-panel" id="panel-avis">
            <div class="panel-header gold">
                <h2><i class="fas fa-star"></i> Avis Clients</h2>
                <div class="rating-summary">
                    <span class="big-rating">{{ $averageRating }}</span>
                    <div class="rating-details">
                        <div class="stars-display">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($averageRating) ? 'filled' : '' }}"></i>
                            @endfor
                        </div>
                        <span class="reviews-count">{{ $totalReviews }} avis</span>
                    </div>
                </div>
            </div>
            @if($totalReviews > 0)
            <div class="panel-toolbar" data-filter-group="avis">
                <div class="panel-toolbar-main">
                    <div>
                        <div class="panel-results" id="results-avis">{{ $totalReviews }} avis</div>
                        <div class="panel-results-note">Filtrez les retours clients par note ou mot-clé.</div>
                    </div>
                    <button type="button" class="panel-filter-toggle" data-filter-toggle="avis">
                        <i class="fas fa-sliders-h"></i>
                        Filtres
                    </button>
                </div>
                <div class="panel-filters" id="filters-avis">
                    <div class="panel-filter-grid">
                        <label class="filter-field">
                            <span>Recherche</span>
                            <input type="search" data-filter-input="avis-search" placeholder="Nom ou commentaire">
                        </label>
                        <label class="filter-field">
                            <span>Note minimum</span>
                            <select data-filter-input="avis-rating">
                                <option value="">Toutes</option>
                                <option value="5">5 étoiles</option>
                                <option value="4">4 étoiles et +</option>
                                <option value="3">3 étoiles et +</option>
                                <option value="2">2 étoiles et +</option>
                                <option value="1">1 étoile et +</option>
                            </select>
                        </label>
                    </div>
                    <div class="panel-filter-actions">
                        <button type="button" class="panel-filter-reset" data-filter-reset="avis">Réinitialiser</button>
                    </div>
                </div>
            </div>
            @endif
 
            {{-- Formulaire avis --}}
            @auth
                @if(auth()->user()->client && !$existingReview && $hasInteracted)
                <div class="review-form-section">
                    <h3>Donnez votre avis</h3>
                    <form action="{{ route('reviews.store') }}" method="POST" class="review-form">
                        @csrf
                        <input type="hidden" name="prestataire_id" value="{{ $prestataire->id }}">
                        <div class="star-rating-input">
                            @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" required>
                                <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                            @endfor
                        </div>
                        <textarea name="comment" placeholder="Partagez votre expérience avec ce prestataire..." rows="4" maxlength="500"></textarea>
                        <button type="submit" class="btn-submit-review">
                            <i class="fas fa-paper-plane"></i> Publier mon avis
                        </button>
                    </form>
                </div>
                @endif
            @endauth
 
            {{-- Liste des avis --}}
            @if($totalReviews > 0)
            <div class="reviews-list">
                @foreach($allReviews as $review)
                @php
                    $reviewSearch = mb_strtolower(trim(($review->client->name ?? 'Client') . ' ' . strip_tags($review->comment ?? '')));
                @endphp
                <article
                    class="review-card filterable-card"
                    data-filter-group="avis"
                    data-search="{{ $reviewSearch }}"
                    data-rating="{{ (int) $review->rating }}"
                >
                    <div class="review-header">
                        <div class="reviewer-avatar">
                            {{ strtoupper(substr($review->client->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="reviewer-info">
                            <span class="reviewer-name">{{ $review->client->name ?? 'Client' }}</span>
                            <div class="review-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'filled' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->comment)
                        <p class="review-text">{{ $review->comment }}</p>
                    @endif
                </article>
                @endforeach
            </div>
            <div class="panel-filter-empty" id="empty-avis" hidden>
                <i class="fas fa-comment-slash"></i>
                <p>Aucun avis ne correspond à ces filtres.</p>
            </div>
            @else
            <div class="no-reviews">
                <i class="fas fa-comment-slash"></i>
                <p>Aucun avis pour le moment</p>
                <span>Soyez le premier à donner votre avis !</span>
            </div>
            @endif
        </section>
 
        {{-- SECTION: À propos (toujours visible en bas) --}}
        @if($prestataire->description)
        <section class="about-section">
            <h3><i class="fas fa-info-circle"></i> À propos</h3>
            <p>{{ $prestataire->description }}</p>
            @if($prestataire->city || $prestataire->phone)
            <div class="about-contact-info">
                @if($prestataire->city)
                    <span><i class="fas fa-map-marker-alt"></i> {{ $prestataire->city }}</span>
                @endif
                @auth
                    @if($prestataire->phone && ($prestataire->phone_visible ?? true))
                        <a href="tel:{{ $prestataire->phone }}"><i class="fas fa-phone"></i> {{ $prestataire->phone }}</a>
                    @endif
                @endauth
            </div>
            @endif
        </section>
        @endif
 
        {{-- Vidéos (si existantes) --}}
        @if($hasVideos)
        <section class="videos-section">
            <h3><i class="fas fa-video"></i> Nos Vidéos</h3>
            <div class="videos-scroll">
                @foreach($prestataire->videos as $video)
                <div class="video-thumb js-video-card" data-video-url="{{ asset('storage/' . $video->video_path) }}">
                    @if($video->thumbnail)
                        <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" loading="lazy">
                    @else
                        <video src="{{ asset('storage/' . $video->video_path) }}" preload="metadata" muted></video>
                    @endif
                    <div class="video-play"><i class="fas fa-play"></i></div>
                </div>
                @endforeach
            </div>
        </section>
        @endif
 
    </div>
 
    {{-- BOUTON FLOTTANT FOLLOW --}}
    @auth
        @if(auth()->user()->isClient())
        <div class="floating-action">
            @if(auth()->user()->client && auth()->user()->client->isFollowing($prestataire->id))
                <form action="{{ route('client.prestataire-follows.unfollow', $prestataire) }}" method="POST" class="floating-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="floating-btn danger">
                        <i class="fas fa-heart"></i>
                    </button>
                </form>
            @else
                <form action="{{ route('client.prestataire-follows.follow', $prestataire) }}" method="POST" class="floating-form">
                    @csrf
                    <button type="submit" class="floating-btn secondary">
                        <i class="far fa-heart"></i>
                    </button>
                </form>
            @endif
        </div>
        @endif
    @endauth
 
</div>
 
{{-- Modal Vidéo --}}
<div id="video-modal" class="video-modal">
    <div class="video-modal-content">
        <button class="video-modal-close" onclick="closeVideoModal()"><i class="fas fa-times"></i></button>
        <video id="modal-video" controls playsinline></video>
    </div>
</div>
 
<script>
(function () {
    const root = document.documentElement;
    const tabs = Array.from(document.querySelectorAll('.shop-nav-tab'));
    const panels = Array.from(document.querySelectorAll('.tab-panel'));
    const navBar = document.querySelector('.shop-nav-bar');
    const defaultTab = @json($defaultActiveTab);
 
    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }
 
    function getStickyOffset() {
        const cssOffset = parseFloat(getComputedStyle(root).getPropertyValue('--profile-sticky-offset'));
        if (Number.isFinite(cssOffset) && cssOffset > 0) {
            return cssOffset;
        }
 
        const siteNav = document.getElementById('site-navbar');
        const shopHeader = document.querySelector('.shop-header');
        const shopNav = document.querySelector('.shop-nav-bar');
 
        return (siteNav ? siteNav.getBoundingClientRect().height : 0)
            + (shopHeader ? shopHeader.getBoundingClientRect().height : 0)
            + (shopNav ? shopNav.getBoundingClientRect().height : 0)
            + 24;
    }
 
    function scrollToPanel(panel, smooth = true) {
        if (!panel) {
            return;
        }
 
        const top = panel.getBoundingClientRect().top + window.pageYOffset - getStickyOffset();
        window.scrollTo({
            top: Math.max(0, top),
            behavior: smooth ? 'smooth' : 'auto',
        });
    }
 
    function activateTab(tabId, options = {}) {
        const { scroll = true, updateHash = true } = options;
        const targetTab = tabs.find((tab) => tab.dataset.tab === tabId);
        const targetPanel = document.getElementById(`panel-${tabId}`);
 
        if (!targetTab || !targetPanel) {
            return;
        }
 
        tabs.forEach((tab) => tab.classList.toggle('active', tab === targetTab));
        panels.forEach((panel) => panel.classList.toggle('active', panel === targetPanel));
 
        targetTab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
 
        if (updateHash) {
            history.replaceState(null, '', `#panel-${tabId}`);
        }
 
        if (scroll) {
            requestAnimationFrame(() => scrollToPanel(targetPanel, true));
        }
    }
 
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.tab));
    });
 
    const requestedHashTab = window.location.hash.startsWith('#panel-')
        ? window.location.hash.replace('#panel-', '')
        : null;
 
    if (requestedHashTab && document.getElementById(`panel-${requestedHashTab}`)) {
        activateTab(requestedHashTab, { scroll: true, updateHash: false });
    } else {
        activateTab(defaultTab, { scroll: false, updateHash: false });
    }
 
    const filterConfigs = {
        services: {
            resultWords: ['service', 'services'],
            cards: Array.from(document.querySelectorAll('.filterable-card[data-filter-group="services"]')),
            empty: document.getElementById('empty-services'),
            results: document.getElementById('results-services'),
            inputs: {
                search: document.querySelector('[data-filter-input="services-search"]'),
                category: document.querySelector('[data-filter-input="services-category"]'),
                pricing: document.querySelector('[data-filter-input="services-pricing"]'),
                maxPrice: document.querySelector('[data-filter-input="services-max-price"]'),
            },
            match(card, state) {
                const search = normalizeText(card.dataset.search);
                const categories = String(card.dataset.category || '').split(' ').filter(Boolean);
                const priceMode = card.dataset.priceMode || '';
                const price = parseFloat(card.dataset.price || '');
 
                if (state.search && !search.includes(state.search)) return false;
                if (state.category && !categories.includes(state.category)) return false;
                if (state.pricing && priceMode !== state.pricing) return false;
                if (state.maxPrice !== null) { if (!Number.isFinite(price)) return false; if (price > state.maxPrice) return false; }
                return true;
            },
        },
        location: {
            resultWords: ['equipement', 'equipements'],
            cards: Array.from(document.querySelectorAll('.filterable-card[data-filter-group="location"]')),
            empty: document.getElementById('empty-location'),
            results: document.getElementById('results-location'),
            inputs: {
                search: document.querySelector('[data-filter-input="location-search"]'),
                category: document.querySelector('[data-filter-input="location-category"]'),
                condition: document.querySelector('[data-filter-input="location-condition"]'),
                maxPrice: document.querySelector('[data-filter-input="location-max-price"]'),
            },
            match(card, state) {
                const search = normalizeText(card.dataset.search);
                const category = String(card.dataset.category || '');
                const condition = String(card.dataset.condition || '');
                const price = parseFloat(card.dataset.price || '');
 
                if (state.search && !search.includes(state.search)) return false;
                if (state.category && category !== state.category) return false;
                if (state.condition && condition !== state.condition) return false;
                if (state.maxPrice !== null) { if (!Number.isFinite(price)) return false; if (price > state.maxPrice) return false; }
                return true;
            },
        },
        boutique: {
            resultWords: ['annonce', 'annonces'],
            cards: Array.from(document.querySelectorAll('.filterable-card[data-filter-group="boutique"]')),
            empty: document.getElementById('empty-boutique'),
            results: document.getElementById('results-boutique'),
            inputs: {
                search: document.querySelector('[data-filter-input="boutique-search"]'),
                category: document.querySelector('[data-filter-input="boutique-category"]'),
                condition: document.querySelector('[data-filter-input="boutique-condition"]'),
                stock: document.querySelector('[data-filter-input="boutique-stock"]'),
                maxPrice: document.querySelector('[data-filter-input="boutique-max-price"]'),
            },
            match(card, state) {
                const search = normalizeText(card.dataset.search);
                const category = String(card.dataset.category || '');
                const condition = String(card.dataset.condition || '');
                const stock = String(card.dataset.stock || '');
                const price = parseFloat(card.dataset.price || '');
 
                if (state.search && !search.includes(state.search)) return false;
                if (state.category && category !== state.category) return false;
                if (state.condition && condition !== state.condition) return false;
                if (state.stock && stock !== state.stock) return false;
                if (state.maxPrice !== null && price > state.maxPrice) return false;
                return true;
            },
        },
        menu: {
            resultWords: ['plat', 'plats'],
            cards: Array.from(document.querySelectorAll('.filterable-card[data-filter-group="menu"]')),
            empty: document.getElementById('empty-menu'),
            results: document.getElementById('results-menu'),
            inputs: {
                search: document.querySelector('[data-filter-input="menu-search"]'),
                category: document.querySelector('[data-filter-input="menu-category"]'),
                preorder: document.querySelector('[data-filter-input="menu-preorder"]'),
                maxPrice: document.querySelector('[data-filter-input="menu-max-price"]'),
            },
            match(card, state) {
                const search = normalizeText(card.dataset.search);
                const category = String(card.dataset.category || '');
                const preorder = String(card.dataset.preorder || '');
                const price = parseFloat(card.dataset.price || '');
 
                if (state.search && !search.includes(state.search)) return false;
                if (state.category && category !== state.category) return false;
                if (state.preorder && preorder !== state.preorder) return false;
                if (state.maxPrice !== null && price > state.maxPrice) return false;
                return true;
            },
        },
        avis: {
            resultWords: ['avis', 'avis'],
            cards: Array.from(document.querySelectorAll('.filterable-card[data-filter-group="avis"]')),
            empty: document.getElementById('empty-avis'),
            results: document.getElementById('results-avis'),
            inputs: {
                search: document.querySelector('[data-filter-input="avis-search"]'),
                rating: document.querySelector('[data-filter-input="avis-rating"]'),
            },
            match(card, state) {
                const search = normalizeText(card.dataset.search);
                const rating = parseInt(card.dataset.rating || '0', 10);
 
                if (state.search && !search.includes(state.search)) return false;
                if (state.rating !== null && rating < state.rating) return false;
                return true;
            },
        },
    };
 
    function parseNumber(value) {
        const trimmed = String(value || '').trim();
        if (!trimmed) return null;
        const parsed = parseFloat(trimmed.replace(',', '.'));
        return Number.isFinite(parsed) ? parsed : null;
    }
 
    function readFilterState(config) {
        const state = {};
        Object.entries(config.inputs).forEach(([key, input]) => {
            if (!input) { state[key] = null; return; }
            if (input.type === 'number') { state[key] = parseNumber(input.value); return; }
            const value = input.value || '';
            state[key] = key === 'search' ? normalizeText(value) : value;
        });
        return state;
    }
 
    function updateResultsLabel(config, visibleCount) {
        if (!config.results) return;
        const [singular, plural] = config.resultWords;
        config.results.textContent = `${visibleCount} ${visibleCount > 1 ? plural : singular}`;
    }
 
    function applyFilters(groupName) {
        const config = filterConfigs[groupName];
        if (!config || !config.cards.length) return;
        const state = readFilterState(config);
        let visibleCount = 0;
        config.cards.forEach((card) => {
            const shouldShow = config.match(card, state);
            card.classList.toggle('is-filter-hidden', !shouldShow);
            if (shouldShow) visibleCount += 1;
        });
        if (config.empty) config.empty.hidden = visibleCount !== 0;
        updateResultsLabel(config, visibleCount);
    }
 
    Object.entries(filterConfigs).forEach(([groupName, config]) => {
        Object.values(config.inputs).forEach((input) => {
            if (!input) return;
            const eventName = input.tagName === 'SELECT' ? 'change' : 'input';
            input.addEventListener(eventName, () => applyFilters(groupName));
        });
        applyFilters(groupName);
    });
 
    document.querySelectorAll('[data-filter-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const groupName = button.getAttribute('data-filter-toggle');
            const filters = document.getElementById(`filters-${groupName}`);
            if (!filters) return;
            filters.classList.toggle('open');
            button.classList.toggle('active', filters.classList.contains('open'));
        });
    });
 
    document.querySelectorAll('[data-filter-reset]').forEach((button) => {
        button.addEventListener('click', () => {
            const groupName = button.getAttribute('data-filter-reset');
            const config = filterConfigs[groupName];
            if (!config) return;
            Object.values(config.inputs).forEach((input) => { if (!input) return; input.value = ''; });
            applyFilters(groupName);
        });
    });
 
    document.querySelectorAll('.add-to-cart-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const btn = form.querySelector('button');
            const originalHtml = btn.innerHTML;
 
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
 
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(form)),
                });
 
                const data = await response.json();
 
                if (!response.ok) {
                    throw new Error(data.message || 'Erreur');
                }
 
                btn.innerHTML = '<i class="fas fa-check"></i> Ajoute';
                btn.classList.add('added');
 
                const cartCount = document.getElementById('cart-count');
                const cartIndicator = document.getElementById('cart-indicator');
                if (cartCount && data.cartCount !== undefined) {
                    cartCount.textContent = data.cartCount;
                }
                if (cartIndicator) {
                    cartIndicator.classList.add('visible');
                }
 
                const toast = document.createElement('div');
                toast.className = 'cart-toast success';
                toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + (data.message || 'Ajoute au panier');
                document.body.appendChild(toast);
                requestAnimationFrame(() => toast.classList.add('visible'));
                setTimeout(() => {
                    toast.classList.remove('visible');
                    setTimeout(() => toast.remove(), 300);
                }, 2500);
 
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('added');
                    btn.disabled = false;
                }, 2000);
            } catch (error) {
                btn.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                const errorMsg = error.message || 'Une erreur est survenue';
                const toast = document.createElement('div');
                toast.className = 'cart-toast error';
                toast.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + errorMsg;
                document.body.appendChild(toast);
                requestAnimationFrame(() => toast.classList.add('visible'));
                setTimeout(() => {
                    toast.classList.remove('visible');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }, 2000);
            }
        });
    });
 
    document.querySelectorAll('.js-video-card').forEach((card) => {
        card.addEventListener('click', () => {
            const modal = document.getElementById('video-modal');
            const video = document.getElementById('modal-video');
            video.src = card.dataset.videoUrl;
            modal.classList.add('active');
            video.play();
            document.body.style.overflow = 'hidden';
        });
    });
 
    function closeVideoModal() {
        const modal = document.getElementById('video-modal');
        const video = document.getElementById('modal-video');
        modal.classList.remove('active');
        video.pause();
        video.src = '';
        document.body.style.overflow = '';
    }
 
    window.closeVideoModal = closeVideoModal;
 
    document.getElementById('video-modal')?.addEventListener('click', (event) => {
        if (event.target.id === 'video-modal') {
            closeVideoModal();
        }
    });
 
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeVideoModal();
        }
    });
 
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        if (navBar) {
            navBar.classList.toggle('scrolled', currentScroll > 150);
        }
    }, { passive: true });
})();
</script>
@endsection