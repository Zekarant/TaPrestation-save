@extends('layouts.app')

@section('title', 'Gestion des livreurs')

@push('styles')
<style>
.drv-page {
    min-height: calc(100vh - var(--site-nav-h, 70px));
    padding: 18px 0 32px;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, .18), transparent 28%),
        radial-gradient(circle at top right, rgba(59, 130, 246, .14), transparent 24%),
        linear-gradient(180deg, #08101d 0%, #0f172a 48%, #111827 100%);
    color: #e2e8f0;
}
.drv-shell {
    width: min(1180px, calc(100% - 24px));
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.drv-card,
.drv-hero,
.drv-toolbar,
.drv-empty,
.drv-pagination,
.drv-note,
.drv-create {
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(15, 23, 42, .88);
    border-radius: 20px;
    box-shadow: 0 18px 45px rgba(2, 6, 23, .22);
}
.drv-hero {
    padding: 18px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}
.drv-hero-main {
    display: flex;
    gap: 14px;
    align-items: flex-start;
}
.drv-hero-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    color: #fff;
    background: linear-gradient(135deg, #f97316, #ea580c);
    box-shadow: 0 12px 24px rgba(249, 115, 22, .28);
}
.drv-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #fdba74;
}
.drv-title {
    margin: 4px 0 0;
    font-size: clamp(1.35rem, 1.1rem + 1vw, 2.2rem);
    line-height: 1.05;
    color: #fff;
    font-weight: 800;
}
.drv-subtitle {
    margin: 8px 0 0;
    max-width: 720px;
    color: #94a3b8;
    font-size: .92rem;
    line-height: 1.55;
}
.drv-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
.drv-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, .16);
    background: rgba(255, 255, 255, .04);
    color: #e2e8f0;
    font-size: .76rem;
    font-weight: 700;
}
.drv-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}
.drv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid transparent;
    text-decoration: none;
    font-size: .82rem;
    font-weight: 700;
    transition: transform .15s, border-color .15s, background .15s, color .15s;
}
.drv-btn:hover {
    transform: translateY(-1px);
}
.drv-btn-primary {
    color: #fff;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}
.drv-btn-primary:hover {
    box-shadow: 0 10px 24px rgba(59, 130, 246, .24);
}
.drv-btn-secondary {
    color: #e2e8f0;
    background: rgba(255, 255, 255, .06);
    border-color: rgba(148, 163, 184, .18);
}
.drv-btn-secondary:hover {
    border-color: rgba(249, 115, 22, .45);
    color: #fff;
}
.drv-alert {
    border-radius: 16px;
    padding: 14px 16px;
    border: 1px solid transparent;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.drv-alert i {
    margin-top: 2px;
}
.drv-alert-success {
    background: rgba(34, 197, 94, .12);
    border-color: rgba(34, 197, 94, .32);
    color: #bbf7d0;
}
.drv-alert-error {
    background: rgba(239, 68, 68, .12);
    border-color: rgba(239, 68, 68, .32);
    color: #fecaca;
}
.drv-create {
    overflow: hidden;
}
.drv-create summary {
    list-style: none;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 16px 18px;
    cursor: pointer;
}
.drv-create summary::-webkit-details-marker {
    display: none;
}
.drv-create-title {
    font-size: 1rem;
    font-weight: 800;
    color: #fff;
}
.drv-create-text {
    margin-top: 4px;
    color: #94a3b8;
    font-size: .82rem;
    line-height: 1.5;
}
.drv-create-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 11px;
    border-radius: 999px;
    background: rgba(168, 85, 247, .16);
    border: 1px solid rgba(168, 85, 247, .28);
    color: #e9d5ff;
    font-size: .72rem;
    font-weight: 800;
    white-space: nowrap;
}
.drv-create-panel {
    padding: 0 18px 18px;
    border-top: 1px solid rgba(148, 163, 184, .12);
}
.drv-errors {
    margin: 14px 0 0;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(239, 68, 68, .28);
    background: rgba(239, 68, 68, .1);
    color: #fecaca;
    font-size: .78rem;
}
.drv-form-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-top: 16px;
}
.drv-field,
.drv-select,
.drv-submit {
    min-width: 0;
}
.drv-input,
.drv-select select {
    width: 100%;
    min-height: 46px;
    padding: 11px 13px;
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(255, 255, 255, .04);
    color: #fff;
    font-size: .84rem;
}
.drv-input::placeholder {
    color: #64748b;
}
.drv-input:focus,
.drv-select select:focus {
    outline: none;
    border-color: rgba(249, 115, 22, .55);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
}
.drv-field-wide {
    grid-column: span 2;
}
.drv-field-full {
    grid-column: span 3;
}
.drv-submit button {
    width: 100%;
    min-height: 46px;
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: .84rem;
    font-weight: 800;
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    cursor: pointer;
}
.drv-submit button:hover {
    box-shadow: 0 10px 24px rgba(168, 85, 247, .24);
}
.drv-stats {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
}
.drv-stat {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 14px;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .16);
    background: rgba(15, 23, 42, .82);
    color: inherit;
    text-decoration: none;
    transition: transform .15s, border-color .15s, background .15s;
}
.drv-stat:hover {
    transform: translateY(-1px);
    border-color: rgba(249, 115, 22, .35);
    background: rgba(255, 255, 255, .04);
}
.drv-stat.is-active {
    border-color: rgba(249, 115, 22, .65);
    background: rgba(249, 115, 22, .08);
}
.drv-stat-label {
    font-size: .68rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 700;
}
.drv-stat-value {
    font-size: clamp(1rem, .85rem + .9vw, 1.65rem);
    font-weight: 800;
    color: #fff;
    line-height: 1;
}
.drv-stat-note {
    font-size: .72rem;
    color: #64748b;
}
.drv-toolbar {
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.drv-search-form {
    display: flex;
    gap: 10px;
    align-items: center;
}
.drv-search-wrap {
    position: relative;
    flex: 1;
}
.drv-search-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}
.drv-search-wrap input {
    width: 100%;
    min-height: 48px;
    padding: 12px 14px 12px 40px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(255, 255, 255, .04);
    color: #fff;
}
.drv-search-actions {
    display: flex;
    gap: 10px;
}
.drv-search-btn,
.drv-clear-btn {
    min-height: 48px;
    padding: 12px 14px;
    border-radius: 14px;
    font-size: .8rem;
    font-weight: 800;
    border: 1px solid rgba(148, 163, 184, .18);
    text-decoration: none;
    cursor: pointer;
}
.drv-search-btn {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: #fff;
    border-color: transparent;
}
.drv-clear-btn {
    background: rgba(255, 255, 255, .04);
    color: #e2e8f0;
}
.drv-filters {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 2px;
    scrollbar-width: none;
}
.drv-filters::-webkit-scrollbar {
    display: none;
}
.drv-filter {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    padding: 10px 12px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(255, 255, 255, .04);
    color: #cbd5e1;
    text-decoration: none;
    font-size: .76rem;
    font-weight: 700;
}
.drv-filter:hover {
    border-color: rgba(249, 115, 22, .35);
    color: #fff;
}
.drv-filter.is-active {
    background: rgba(249, 115, 22, .12);
    border-color: rgba(249, 115, 22, .6);
    color: #fff;
}
.drv-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}
.drv-driver {
    overflow: hidden;
    border-radius: 20px;
}
.drv-driver-head {
    padding: 16px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    background: linear-gradient(135deg, rgba(15, 23, 42, .98), rgba(30, 41, 59, .95));
    border-bottom: 1px solid rgba(148, 163, 184, .12);
}
.drv-driver--available .drv-driver-head {
    background: linear-gradient(135deg, rgba(6, 78, 59, .92), rgba(15, 118, 110, .82));
}
.drv-driver--busy .drv-driver-head {
    background: linear-gradient(135deg, rgba(124, 45, 18, .94), rgba(194, 65, 12, .84));
}
.drv-driver--offline .drv-driver-head {
    background: linear-gradient(135deg, rgba(30, 41, 59, .96), rgba(51, 65, 85, .9));
}
.drv-driver-main {
    display: flex;
    gap: 12px;
    min-width: 0;
}
.drv-avatar {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: rgba(255, 255, 255, .12);
    border: 1px solid rgba(255, 255, 255, .14);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 1rem;
    overflow: hidden;
    flex-shrink: 0;
}
.drv-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.drv-driver-name {
    font-size: 1rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
}
.drv-driver-sub {
    margin-top: 6px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    color: rgba(255, 255, 255, .82);
    font-size: .78rem;
}
.drv-driver-sub span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.drv-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .12);
    color: #fff;
    font-size: .72rem;
    font-weight: 800;
    white-space: nowrap;
}
.drv-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}
.drv-driver-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.drv-badges,
.drv-metrics,
.drv-actions-row,
.drv-pref-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.drv-badge,
.drv-metric,
.drv-pref-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, .14);
    background: rgba(255, 255, 255, .04);
    font-size: .74rem;
    font-weight: 700;
    color: #e2e8f0;
}
.drv-badge-soft {
    background: rgba(59, 130, 246, .12);
    border-color: rgba(59, 130, 246, .22);
    color: #bfdbfe;
}
.drv-badge-pref {
    background: rgba(34, 197, 94, .12);
    border-color: rgba(34, 197, 94, .24);
    color: #bbf7d0;
}
.drv-badge-blocked {
    background: rgba(239, 68, 68, .12);
    border-color: rgba(239, 68, 68, .24);
    color: #fecaca;
}
.drv-info-list {
    display: grid;
    gap: 8px;
}
.drv-info {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #cbd5e1;
    font-size: .82rem;
}
.drv-info i {
    width: 18px;
    color: #64748b;
}
.drv-info a {
    color: inherit;
    text-decoration: none;
}
.drv-info a:hover {
    color: #fff;
}
.drv-actions-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) repeat(2, auto);
    gap: 8px;
}
.drv-action,
.drv-pref-btn {
    min-height: 42px;
    border-radius: 12px;
    justify-content: center;
    text-decoration: none;
}
.drv-action-primary {
    background: linear-gradient(135deg, #f97316, #ea580c);
    border-color: transparent;
    color: #fff;
    font-weight: 800;
}
.drv-action-secondary {
    min-width: 42px;
    padding: 0 12px;
}
.drv-pref-form {
    flex: 1;
}
.drv-pref-btn {
    width: 100%;
    cursor: pointer;
}
.drv-pref-btn.is-active {
    border-color: rgba(249, 115, 22, .5);
    background: rgba(249, 115, 22, .08);
    color: #fff;
}
.drv-empty {
    padding: 36px 20px;
    text-align: center;
}
.drv-empty-icon {
    width: 74px;
    height: 74px;
    margin: 0 auto 14px;
    border-radius: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fdba74;
    background: rgba(249, 115, 22, .1);
    font-size: 1.8rem;
}
.drv-empty-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #fff;
}
.drv-empty-text {
    margin-top: 8px;
    color: #94a3b8;
    font-size: .88rem;
}
.drv-pagination {
    padding: 14px 16px;
}
.drv-pagination nav {
    display: flex;
    justify-content: center;
}
.drv-note {
    padding: 16px 18px;
}
.drv-note-title {
    font-size: .95rem;
    font-weight: 800;
    color: #fff;
}
.drv-note-grid {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}
.drv-note-item {
    padding: 14px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, .14);
    background: rgba(255, 255, 255, .04);
}
.drv-note-item strong {
    display: block;
    margin-top: 8px;
    color: #fff;
    font-size: .82rem;
}
.drv-note-item p {
    margin: 6px 0 0;
    color: #94a3b8;
    font-size: .78rem;
    line-height: 1.5;
}
@media (max-width: 1100px) {
    .drv-stats,
    .drv-grid,
    .drv-note-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .drv-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .drv-field-full,
    .drv-field-wide {
        grid-column: span 2;
    }
}
@media (max-width: 720px) {
    .drv-page {
        padding: 10px 0 26px;
    }
    .drv-shell {
        width: min(100%, calc(100% - 12px));
        gap: 10px;
    }
    .drv-hero,
    .drv-toolbar,
    .drv-create summary,
    .drv-create-panel,
    .drv-note,
    .drv-card,
    .drv-empty,
    .drv-pagination {
        border-radius: 16px;
    }
    .drv-hero {
        padding: 14px;
        flex-direction: column;
    }
    .drv-hero-main {
        gap: 10px;
    }
    .drv-hero-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        font-size: 1.05rem;
    }
    .drv-subtitle {
        font-size: .82rem;
    }
    .drv-actions,
    .drv-search-form,
    .drv-actions-row {
        width: 100%;
    }
    .drv-actions,
    .drv-search-form {
        flex-direction: column;
    }
    .drv-btn,
    .drv-search-btn,
    .drv-clear-btn {
        width: 100%;
    }
    .drv-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }
    .drv-stat {
        padding: 10px;
        border-radius: 14px;
    }
    .drv-stat-label {
        font-size: .58rem;
        letter-spacing: .03em;
    }
    .drv-stat-value {
        font-size: 1.15rem;
    }
    .drv-toolbar {
        padding: 12px;
    }
    .drv-search-actions {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .drv-filter {
        padding: 9px 10px;
        font-size: .7rem;
    }
    .drv-form-grid,
    .drv-grid,
    .drv-note-grid {
        grid-template-columns: 1fr;
    }
    .drv-field-full,
    .drv-field-wide {
        grid-column: span 1;
    }
    .drv-driver-head,
    .drv-driver-body {
        padding: 12px;
    }
    .drv-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
    }
    .drv-driver-name {
        font-size: .95rem;
    }
    .drv-driver-sub,
    .drv-info {
        font-size: .76rem;
    }
    .drv-actions-row {
        grid-template-columns: 1fr 48px 48px;
    }
}

/* Mode clair force pour la page livreurs */
.drv-page {
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, .14), transparent 28%),
        radial-gradient(circle at top right, rgba(59, 130, 246, .1), transparent 24%),
        linear-gradient(180deg, #fff7ed 0%, #ffffff 46%, #f8fafc 100%);
    color: #0f172a;
}
.drv-card,
.drv-hero,
.drv-toolbar,
.drv-empty,
.drv-pagination,
.drv-note,
.drv-create {
    border-color: rgba(226, 232, 240, .95);
    background: rgba(255, 255, 255, .94);
    box-shadow: 0 18px 45px rgba(148, 163, 184, .14);
}
.drv-title,
.drv-create-title,
.drv-stat-value,
.drv-driver-name,
.drv-empty-title,
.drv-note-title,
.drv-note-item strong {
    color: #0f172a;
}
.drv-subtitle,
.drv-create-text,
.drv-stat-label,
.drv-empty-text,
.drv-note-item p,
.drv-info,
.drv-search-wrap i {
    color: #64748b;
}
.drv-meta-pill,
.drv-btn-secondary,
.drv-input,
.drv-select select,
.drv-stat,
.drv-search-wrap input,
.drv-clear-btn,
.drv-filter,
.drv-badge,
.drv-metric,
.drv-pref-btn,
.drv-note-item {
    background: #ffffff;
    color: #334155;
    border-color: rgba(203, 213, 225, .9);
}
.drv-meta-pill {
    background: rgba(255, 247, 237, .95);
}
.drv-btn-secondary:hover,
.drv-filter:hover,
.drv-info a:hover {
    color: #0f172a;
}
.drv-input::placeholder,
.drv-search-wrap input::placeholder,
.drv-stat-note {
    color: #94a3b8;
}
.drv-input:focus,
.drv-select select:focus,
.drv-search-wrap input:focus {
    outline: none;
    border-color: rgba(249, 115, 22, .45);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
}
.drv-stat:hover {
    border-color: rgba(249, 115, 22, .35);
    background: #fff7ed;
}
.drv-stat.is-active,
.drv-pref-btn.is-active,
.drv-filter.is-active {
    background: #fff7ed;
    border-color: rgba(249, 115, 22, .48);
    color: #c2410c;
}
.drv-filter.is-active,
.drv-pref-btn.is-active {
    font-weight: 800;
}
.drv-driver-head {
    background: linear-gradient(135deg, #fff7ed, #fffbeb);
    border-bottom-color: rgba(226, 232, 240, .95);
}
.drv-driver--available .drv-driver-head {
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
}
.drv-driver--busy .drv-driver-head {
    background: linear-gradient(135deg, #fff7ed, #fef3c7);
}
.drv-driver--offline .drv-driver-head {
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
}
.drv-avatar {
    background: rgba(255, 255, 255, .92);
    border-color: rgba(203, 213, 225, .9);
    color: #0f172a;
}
.drv-driver-sub,
.drv-status {
    color: #475569;
}
.drv-status {
    background: rgba(255, 255, 255, .74);
    border: 1px solid rgba(203, 213, 225, .9);
}
.drv-info i {
    color: #94a3b8;
}
.drv-info a {
    color: #2563eb;
}
.drv-badge-soft {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}
.drv-badge-pref {
    background: #ecfdf5;
    border-color: #bbf7d0;
    color: #15803d;
}
.drv-badge-blocked {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}
.drv-empty-icon {
    background: #fff7ed;
    color: #ea580c;
}
.drv-alert-success {
    background: #ecfdf5;
    border-color: #86efac;
    color: #166534;
}
.drv-alert-error,
.drv-errors {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #b91c1c;
}
</style>
@endpush

@section('content')
@php
    $filter = $filter ?? request('filter', 'all');
    $search = $search ?? request('search', '');
    $preferences = $preferences ?? collect();
    $createPanelOpen = $errors->any() || request()->boolean('create');
    $vehicleIcons = [
        'scooter' => '🛵',
        'moto' => '🏍️',
        'motorcycle' => '🏍️',
        'velo' => '🚴',
        'bike' => '🚴',
        'voiture' => '🚗',
        'car' => '🚗',
        'van' => '🚐',
        'truck' => '🚚',
    ];
    $filters = [
        'all' => ['Tous', 'fas fa-layer-group'],
        'available' => ['Disponibles', 'fas fa-circle'],
        'busy' => ['En mission', 'fas fa-route'],
        'linked' => ['Compte lie', 'fas fa-link'],
        'offline' => ['Hors ligne', 'fas fa-power-off'],
        'preferred' => ['Favoris', 'fas fa-star'],
        'blocked' => ['Bloques', 'fas fa-ban'],
    ];
@endphp

<div class="drv-page">
    <div class="drv-shell">
        <section class="drv-hero">
            <div class="drv-hero-main">
                <div class="drv-hero-icon"><i class="fas fa-motorcycle"></i></div>
                <div>
                    <span class="drv-eyebrow"><i class="fas fa-sparkles"></i> Hub livreurs</span>
                    <h1 class="drv-title">Equipe interne livreurs</h1>
                    <p class="drv-subtitle">La page reprend une presentation compacte comme les autres modules prestataire: moins chargee, lisible sur mobile, et centree sur l’equipe interne, la disponibilite et l’acces rapide au profil.</p>
                    <div class="drv-meta">
                        <span class="drv-meta-pill"><i class="fas fa-users"></i> {{ $stats['total'] ?? 0 }} livreur(s)</span>
                        <span class="drv-meta-pill"><i class="fas fa-circle"></i> {{ $stats['available'] ?? 0 }} disponible(s)</span>
                        <span class="drv-meta-pill"><i class="fas fa-link"></i> {{ $stats['linked'] ?? 0 }} compte(s) lies</span>
                    </div>
                </div>
            </div>

            <div class="drv-actions">
                <a href="{{ route('prestataire.food-orders.internal-map') }}" class="drv-btn drv-btn-primary">
                    <i class="fas fa-route"></i>
                    <span>Carte interne</span>
                </a>
                <a href="{{ route('prestataire.food-delivery.index') }}" class="drv-btn drv-btn-secondary">
                    <i class="fas fa-sliders-h"></i>
                    <span>Retour livraison</span>
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="drv-alert drv-alert-success">
                <i class="fas fa-check-circle"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="drv-alert drv-alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <details class="drv-create" @if($createPanelOpen) open @endif>
            <summary>
                <div>
                    <div class="drv-create-title">Ajouter un livreur interne</div>
                    <div class="drv-create-text">Creation rapide sans compte prestataire. Le livreur peut ensuite etre lie a un compte utilisateur standard depuis son profil.</div>
                </div>
                <span class="drv-create-badge"><i class="fas fa-user-plus"></i> Nouveau livreur</span>
            </summary>

            <div class="drv-create-panel">
                @if($errors->any())
                    <div class="drv-errors">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('prestataire.drivers.store-internal') }}" method="POST" class="drv-form-grid">
                    @csrf
                    <div class="drv-field">
                        <input class="drv-input" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Prenom" required>
                    </div>
                    <div class="drv-field">
                        <input class="drv-input" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Nom" required>
                    </div>
                    <div class="drv-field">
                        <input class="drv-input" type="text" name="phone" value="{{ old('phone') }}" placeholder="Telephone" required>
                    </div>
                    <div class="drv-select">
                        <select name="vehicle_type" required>
                            <option value="scooter" {{ old('vehicle_type') === 'scooter' ? 'selected' : '' }}>Scooter</option>
                            <option value="bike" {{ old('vehicle_type') === 'bike' ? 'selected' : '' }}>Velo</option>
                            <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>Voiture</option>
                            <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Van</option>
                            <option value="truck" {{ old('vehicle_type') === 'truck' ? 'selected' : '' }}>Camion</option>
                        </select>
                    </div>
                    <div class="drv-field drv-field-wide">
                        <input class="drv-input" type="email" name="email" value="{{ old('email') }}" placeholder="Email optionnel">
                    </div>
                    <div class="drv-field">
                        <input class="drv-input" type="text" name="city" value="{{ old('city') }}" placeholder="Ville optionnelle">
                    </div>
                    <div class="drv-field">
                        <input class="drv-input" type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="Code postal optionnel">
                    </div>
                    <div class="drv-field drv-field-full">
                        <input class="drv-input" type="text" name="address" value="{{ old('address') }}" placeholder="Adresse optionnelle">
                    </div>
                    <div class="drv-submit">
                        <button type="submit"><i class="fas fa-plus"></i> Creer le profil</button>
                    </div>
                </form>
            </div>
        </details>

        <section class="drv-stats">
            <a class="drv-stat {{ $filter === 'all' ? 'is-active' : '' }}" href="{{ route('prestataire.drivers.index', ['search' => $search ?: null]) }}">
                <span class="drv-stat-label">Equipe</span>
                <span class="drv-stat-value">{{ $stats['total'] ?? 0 }}</span>
                <span class="drv-stat-note">Interne</span>
            </a>
            <a class="drv-stat {{ $filter === 'available' ? 'is-active' : '' }}" href="{{ route('prestataire.drivers.index', ['filter' => 'available', 'search' => $search ?: null]) }}">
                <span class="drv-stat-label">Disponibles</span>
                <span class="drv-stat-value">{{ $stats['available'] ?? 0 }}</span>
                <span class="drv-stat-note">Pret a partir</span>
            </a>
            <a class="drv-stat {{ $filter === 'busy' ? 'is-active' : '' }}" href="{{ route('prestataire.drivers.index', ['filter' => 'busy', 'search' => $search ?: null]) }}">
                <span class="drv-stat-label">En mission</span>
                <span class="drv-stat-value">{{ $stats['busy'] ?? 0 }}</span>
                <span class="drv-stat-note">Tournee active</span>
            </a>
            <a class="drv-stat {{ $filter === 'linked' ? 'is-active' : '' }}" href="{{ route('prestataire.drivers.index', ['filter' => 'linked', 'search' => $search ?: null]) }}">
                <span class="drv-stat-label">Comptes lies</span>
                <span class="drv-stat-value">{{ $stats['linked'] ?? 0 }}</span>
                <span class="drv-stat-note">Connexion possible</span>
            </a>
            <a class="drv-stat {{ $filter === 'offline' ? 'is-active' : '' }}" href="{{ route('prestataire.drivers.index', ['filter' => 'offline', 'search' => $search ?: null]) }}">
                <span class="drv-stat-label">Hors ligne</span>
                <span class="drv-stat-value">{{ $stats['offline'] ?? 0 }}</span>
                <span class="drv-stat-note">A relancer</span>
            </a>
        </section>

        <section class="drv-toolbar">
            <form action="{{ route('prestataire.drivers.index') }}" method="GET" class="drv-search-form">
                <input type="hidden" name="filter" value="{{ $filter === 'all' ? '' : $filter }}">
                <div class="drv-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Rechercher un livreur, un telephone, une ville...">
                </div>
                <div class="drv-search-actions">
                    <button type="submit" class="drv-search-btn">Rechercher</button>
                    @if($search !== '')
                        <a href="{{ route('prestataire.drivers.index', ['filter' => $filter !== 'all' ? $filter : null]) }}" class="drv-clear-btn">Effacer</a>
                    @endif
                </div>
            </form>

            <div class="drv-filters">
                @foreach($filters as $key => [$label, $icon])
                    <a href="{{ route('prestataire.drivers.index', ['filter' => $key !== 'all' ? $key : null, 'search' => $search !== '' ? $search : null]) }}" class="drv-filter {{ $filter === $key ? 'is-active' : '' }}">
                        <i class="{{ $icon }}"></i>
                        <span>{{ $label }}</span>
                        @switch($key)
                            @case('available') <span>{{ $stats['available'] ?? 0 }}</span> @break
                            @case('busy') <span>{{ $stats['busy'] ?? 0 }}</span> @break
                            @case('linked') <span>{{ $stats['linked'] ?? 0 }}</span> @break
                            @case('offline') <span>{{ $stats['offline'] ?? 0 }}</span> @break
                            @case('preferred') <span>{{ $stats['preferred'] ?? 0 }}</span> @break
                            @case('blocked') <span>{{ $stats['blocked'] ?? 0 }}</span> @break
                            @default <span>{{ $stats['total'] ?? 0 }}</span>
                        @endswitch
                    </a>
                @endforeach
            </div>
        </section>

        @if($drivers->count() > 0)
            <section class="drv-grid">
                @foreach($drivers as $driver)
                    @php
                        $vehicleType = strtolower((string) ($driver->vehicle_type ?? 'scooter'));
                        $vehicleIcon = $vehicleIcons[$vehicleType] ?? '🛵';
                        $isAvailable = (bool) ($driver->is_available ?? false);
                        $isBusy = (($driver->active_orders_count ?? 0) > 0) || (($driver->status ?? null) === \App\Models\DeliveryDriver::STATUS_BUSY);
                        $statusTone = $isBusy ? 'busy' : ($isAvailable ? 'available' : 'offline');
                        $statusLabel = $isBusy ? 'En mission' : ($isAvailable ? 'Disponible' : 'Hors ligne');
                        $isLinked = !empty($driver->user_id);
                        $prefStatus = optional($preferences->get($driver->id))->status;
                        $phoneDigits = preg_replace('/[^0-9]/', '', (string) ($driver->phone ?? ''));
                        $distance = $driver->max_distance_km ?? $driver->max_distance ?? 10;
                    @endphp

                    <article class="drv-card drv-driver drv-driver--{{ $statusTone }}">
                        <div class="drv-driver-head">
                            <div class="drv-driver-main">
                                <div class="drv-avatar">
                                    @if($driver->photo)
                                        <img src="{{ asset('storage/' . $driver->photo) }}" alt="{{ $driver->full_name ?? $driver->first_name }}">
                                    @else
                                        {{ strtoupper(substr((string) ($driver->first_name ?? ''), 0, 1) . substr((string) ($driver->last_name ?? ''), 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="drv-driver-name">{{ $driver->full_name ?? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')) }}</div>
                                    <div class="drv-driver-sub">
                                        <span>{{ $vehicleIcon }} {{ ucfirst($vehicleType) }}</span>
                                        @if(!empty($driver->city))<span><i class="fas fa-location-dot"></i> {{ $driver->city }}</span>@endif
                                    </div>
                                </div>
                            </div>
                            <span class="drv-status"><span class="drv-status-dot"></span>{{ $statusLabel }}</span>
                        </div>

                        <div class="drv-driver-body">
                            <div class="drv-badges">
                                <span class="drv-badge drv-badge-soft"><i class="fas fa-id-badge"></i> Interne</span>
                                <span class="drv-badge {{ $isLinked ? 'drv-badge-pref' : '' }}"><i class="fas {{ $isLinked ? 'fa-link' : 'fa-unlink' }}"></i> {{ $isLinked ? 'Compte lie' : 'Compte a lier' }}</span>
                                @if($prefStatus === \App\Models\PrestataireDriverPreference::STATUS_PREFERRED)
                                    <span class="drv-badge drv-badge-pref"><i class="fas fa-star"></i> Favori</span>
                                @elseif($prefStatus === \App\Models\PrestataireDriverPreference::STATUS_BLOCKED)
                                    <span class="drv-badge drv-badge-blocked"><i class="fas fa-ban"></i> Bloque</span>
                                @endif
                            </div>

                            <div class="drv-metrics">
                                <span class="drv-metric"><i class="fas fa-star"></i> {{ number_format((float) ($driver->rating ?? 0), 1) }}/5</span>
                                <span class="drv-metric"><i class="fas fa-box"></i> {{ (int) ($driver->completed_deliveries ?? $driver->total_deliveries ?? 0) }} livraison(s)</span>
                                <span class="drv-metric"><i class="fas fa-route"></i> {{ (int) ($driver->active_orders_count ?? 0) }} active(s)</span>
                            </div>

                            <div class="drv-info-list">
                                @if($driver->phone)
                                    <div class="drv-info"><i class="fas fa-phone"></i><a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a></div>
                                @endif
                                @if($driver->email)
                                    <div class="drv-info"><i class="fas fa-envelope"></i><span>{{ $driver->email }}</span></div>
                                @endif
                                <div class="drv-info"><i class="fas fa-road"></i><span>Rayon max {{ is_numeric($distance) ? rtrim(rtrim(number_format((float) $distance, 1, '.', ''), '0'), '.') : $distance }} km</span></div>
                                @if($driver->created_at)
                                    <div class="drv-info"><i class="fas fa-clock"></i><span>Ajoute {{ $driver->created_at->diffForHumans() }}</span></div>
                                @endif
                            </div>

                            <div class="drv-actions-row">
                                <a href="{{ route('prestataire.drivers.show', $driver) }}" class="drv-badge drv-action drv-action-primary">
                                    <i class="fas fa-user"></i>
                                    <span>Voir le profil</span>
                                </a>
                                @if($phoneDigits !== '')
                                    <a href="https://wa.me/{{ $phoneDigits }}" target="_blank" rel="noopener" class="drv-badge drv-action drv-action-secondary" title="WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @else
                                    <span class="drv-badge drv-action drv-action-secondary"><i class="fab fa-whatsapp"></i></span>
                                @endif
                                @if($driver->phone)
                                    <a href="tel:{{ $driver->phone }}" class="drv-badge drv-action drv-action-secondary" title="Appeler">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                @else
                                    <span class="drv-badge drv-action drv-action-secondary"><i class="fas fa-phone"></i></span>
                                @endif
                            </div>

                            <div class="drv-pref-row">
                                <form class="drv-pref-form" method="POST" action="{{ route('prestataire.drivers.preference', $driver) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $prefStatus === \App\Models\PrestataireDriverPreference::STATUS_PREFERRED ? 'none' : 'preferred' }}">
                                    <button type="submit" class="drv-pref-btn {{ $prefStatus === \App\Models\PrestataireDriverPreference::STATUS_PREFERRED ? 'is-active' : '' }}">
                                        <i class="fas fa-star"></i>
                                        <span>{{ $prefStatus === \App\Models\PrestataireDriverPreference::STATUS_PREFERRED ? 'Retirer favori' : 'Mettre en favori' }}</span>
                                    </button>
                                </form>
                                <form class="drv-pref-form" method="POST" action="{{ route('prestataire.drivers.preference', $driver) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $prefStatus === \App\Models\PrestataireDriverPreference::STATUS_BLOCKED ? 'none' : 'blocked' }}">
                                    <button type="submit" class="drv-pref-btn {{ $prefStatus === \App\Models\PrestataireDriverPreference::STATUS_BLOCKED ? 'is-active' : '' }}">
                                        <i class="fas fa-ban"></i>
                                        <span>{{ $prefStatus === \App\Models\PrestataireDriverPreference::STATUS_BLOCKED ? 'Debloquer' : 'Bloquer' }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            @if(method_exists($drivers, 'links'))
                <div class="drv-pagination">
                    {{ $drivers->links() }}
                </div>
            @endif
        @else
            <div class="drv-empty">
                <div class="drv-empty-icon"><i class="fas fa-users"></i></div>
                <div class="drv-empty-title">Aucun livreur ne correspond a ce filtre</div>
                <div class="drv-empty-text">Essayez un autre filtre, effacez la recherche, ou ajoutez directement un livreur interne depuis le panneau de creation.</div>
            </div>
        @endif

        <section class="drv-note">
            <div class="drv-note-title">Repere rapide</div>
            <div class="drv-note-grid">
                <div class="drv-note-item">
                    <i class="fas fa-link"></i>
                    <strong>Compte lie</strong>
                    <p>Le livreur peut utiliser son acces plateforme et recevoir sa route depuis le module livreur.</p>
                </div>
                <div class="drv-note-item">
                    <i class="fas fa-route"></i>
                    <strong>En mission</strong>
                    <p>Le livreur a deja une ou plusieurs commandes actives. Le compteur reste visible sur sa carte.</p>
                </div>
                <div class="drv-note-item">
                    <i class="fas fa-user-plus"></i>
                    <strong>Ajout interne</strong>
                    <p>Creation immediate sans compte prestataire supplementaire, puis liaison optionnelle depuis le profil detaille.</p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
