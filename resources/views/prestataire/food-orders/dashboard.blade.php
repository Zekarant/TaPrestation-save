@extends('layouts.app')

@section('title', 'Gestion Commandes')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #f97316;
        --primary-dark: #ea580c;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --purple: #8b5cf6;
    }

    body {
        background: #f8fafc;
    }

    /* Header Pro */
    .main-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-logo {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    }

    .header-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .header-info h1 {
        font-size: 1.25rem;
        font-weight: 800;
        color: white;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .header-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .live-indicator .dot {
        width: 8px;
        height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse-glow 2s infinite;
    }

    @keyframes pulse-glow {
        0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        50% { opacity: 0.8; box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    }

    .header-clock {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        font-size: 0.85rem;
        color: white;
        background: rgba(255,255,255,0.1);
        padding: 4px 10px;
        border-radius: 6px;
    }

    /* Boutons d'action du header */
    .header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        color: white !important;
        text-decoration: none;
    }

    .action-btn:link,
    .action-btn:visited,
    .action-btn:hover,
    .action-btn:active {
        color: white !important;
        text-decoration: none;
    }

    .action-btn i,
    .action-btn span {
        color: inherit !important;
    }

    .action-btn i {
        font-size: 1rem;
    }

    .action-btn.sound-btn {
        background: rgba(255,255,255,0.1);
        color: white;
        padding: 10px 12px;
    }

    .action-btn.sound-btn:hover {
        background: rgba(255,255,255,0.2);
    }

    .action-btn.sound-btn.muted {
        color: #64748b !important;
    }

    .action-btn.refresh-btn {
        background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
        color: white;
    }

    .action-btn.refresh-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    }

    .action-btn.delivery-btn {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
    }

    .action-btn.delivery-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .action-btn.menu-btn {
        background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
        color: white;
    }

    .action-btn.menu-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .action-btn.home-btn {
        background: rgba(255,255,255,0.15);
        color: white;
    }

    .action-btn.home-btn:hover {
        background: rgba(255,255,255,0.25);
    }

    .label-mobile {
        display: none;
    }

    /* Desktop: design plus spacieux */
    @media (min-width: 1024px) {
        .main-header {
            padding: 1.25rem 2rem;
        }
        
        .header-logo {
            width: 56px;
            height: 56px;
            font-size: 1.75rem;
        }
        
        .header-info h1 {
            font-size: 1.5rem;
        }
        
        .header-actions {
            gap: 12px;
        }
        
        .action-btn {
            padding: 12px 20px;
            font-size: 0.9rem;
            border-radius: 14px;
        }
        
        .action-btn i {
            font-size: 1.1rem;
        }
        
        .action-btn.sound-btn {
            padding: 12px 14px;
        }
        
        .header-clock {
            font-size: 1rem;
            padding: 6px 14px;
        }
    }

    /* Mobile: icônes seulement */
    @media (max-width: 768px) {
        .action-btn span {
            display: none;
        }
        .action-btn {
            padding: 10px 12px;
        }
        .header-info h1 {
            font-size: 1.1rem;
        }
        .header-clock {
            display: none;
        }
    }

    /* Animation refresh */
    .header-btn.refreshing i {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Tabs améliorés */
    .tabs-container {
        display: flex;
        background: white;
        border-bottom: 1px solid #e5e7eb;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tab-btn {
        flex: 1;
        min-width: 100px;
        padding: 0.875rem 0.75rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.875rem;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .tab-btn i {
        font-size: 1rem;
    }

    .tab-btn:hover {
        background: #f9fafb;
        color: #374151;
    }

    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: #fff7ed;
    }

    .tab-badge {
        background: var(--danger);
        color: white;
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        border-radius: 50px;
        font-weight: 700;
        min-width: 20px;
    }

    .tab-badge.green { background: var(--success); }
    .tab-badge.orange { background: var(--warning); }
    .tab-badge.purple { background: var(--purple); }

    /* Tab content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Stats bar amélioré */
    .stats-bar {
        display: flex;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        background: white;
        border-bottom: 1px solid #e5e7eb;
    }

    .stat-chip {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.875rem;
        background: #f8fafc;
        border-radius: 10px;
        font-size: 0.8rem;
        border: 1px solid #e2e8f0;
    }

    .stat-chip i {
        font-size: 0.875rem;
        width: 18px;
        text-align: center;
    }

    .stat-chip .value {
        font-weight: 700;
        font-size: 0.9rem;
    }

    .stat-chip .label {
        color: #64748b;
        font-size: 0.75rem;
    }

    .stat-chip.revenue { background: #ecfdf5; border-color: #a7f3d0; }
    .stat-chip.revenue i { color: var(--success); }
    .stat-chip.revenue .value { color: var(--success); }

    .stat-chip.pending { background: #fffbeb; border-color: #fde68a; }
    .stat-chip.pending i { color: var(--warning); }
    .stat-chip.pending .value { color: var(--warning); }

    .stat-chip.cooking { background: #f5f3ff; border-color: #ddd6fe; }
    .stat-chip.cooking i { color: var(--purple); }
    .stat-chip.cooking .value { color: var(--purple); }

    .stat-chip.ready { background: #ecfdf5; border-color: #a7f3d0; }
    .stat-chip.ready i { color: var(--success); }
    .stat-chip.ready .value { color: var(--success); }

    /* Kanban columns */
    .kanban-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        padding: 1rem;
        min-height: calc(100vh - 280px);
    }

    @media (max-width: 1200px) {
        .kanban-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .kanban-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    .kanban-column {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 0.875rem;
        min-height: 250px;
    }

    .kanban-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        padding-bottom: 0.625rem;
        border-bottom: 3px solid;
    }

    .kanban-header.pending { border-color: var(--warning); }
    .kanban-header.accepted { border-color: var(--info); }
    .kanban-header.preparing { border-color: var(--purple); }
    .kanban-header.ready { border-color: var(--success); }

    .kanban-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        font-size: 0.875rem;
        color: #374151;
    }

    .kanban-title i {
        font-size: 1rem;
    }

    .kanban-header.pending .kanban-title i { color: var(--warning); }
    .kanban-header.accepted .kanban-title i { color: var(--info); }
    .kanban-header.preparing .kanban-title i { color: var(--purple); }
    .kanban-header.ready .kanban-title i { color: var(--success); }

    .kanban-count {
        background: #374151;
        color: white;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 50px;
        font-weight: 700;
        min-width: 24px;
        text-align: center;
    }

    /* Order card */
    .order-card {
        background: white;
        border-radius: 10px;
        padding: 0.875rem;
        margin-bottom: 0.625rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: all 0.2s;
        border-left: 4px solid transparent;
    }

    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .order-card.urgent {
        border-left-color: #ef4444;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }

    .order-number {
        font-weight: 700;
        font-size: 1.1rem;
    }

    .order-time {
        font-size: 0.75rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .order-client {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .order-items {
        font-size: 0.8rem;
        color: #374151;
        background: #f9fafb;
        padding: 0.5rem;
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }

    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-total {
        font-weight: 700;
        color: var(--primary);
    }

    .order-type {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 50px;
        background: #e5e7eb;
        color: #374151;
    }

    .order-type.delivery { background: #dbeafe; color: #1d4ed8; }
    .order-type.pickup { background: #d1fae5; color: #059669; }

    /* Action buttons */
    .action-btns {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }

    .action-btns .action-btn {
        flex: 1;
        padding: 0.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-btns .action-btn.accept {
        background: #10b981;
        color: white;
    }

    .action-btns .action-btn.reject {
        background: #fee2e2;
        color: #dc2626;
    }

    .action-btns .action-btn.prepare {
        background: #8b5cf6;
        color: white;
    }

    .action-btns .action-btn.ready {
        background: #10b981;
        color: white;
    }

    .action-btns .action-btn.deliver {
        background: #3b82f6;
        color: white;
    }

    /* History tab */
    .history-filters {
        display: flex;
        gap: 0.75rem;
        padding: 1rem;
        background: white;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 0.5rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .history-list {
        padding: 1rem;
    }

    .history-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .history-card .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-dot.completed { background: #10b981; }
    .status-dot.cancelled { background: #ef4444; }
    .status-dot.delivered { background: #3b82f6; }

    .history-info {
        flex: 1;
    }

    .history-info .number {
        font-weight: 600;
    }

    .history-info .date {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .history-total {
        font-weight: 700;
        color: #10b981;
    }

    /* Deliveries Grid */
    .deliveries-grid {
        padding: 1rem;
        display: grid;
        gap: 1rem;
    }

    .delivery-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .delivery-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .delivery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .delivery-header .order-number {
        font-weight: 700;
        font-size: 1.1rem;
    }

    .delivery-status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255,255,255,0.2);
    }

    .delivery-status-badge.assigned {
        background: #f59e0b;
        color: white;
    }

    .delivery-status-badge.picked_up {
        background: #3b82f6;
        color: white;
    }

    .delivery-status-badge.in_transit {
        background: #10b981;
        color: white;
    }

    .delivery-body {
        padding: 1rem;
    }

    .client-info {
        margin-bottom: 1rem;
    }

    .client-name {
        font-weight: 600;
        font-size: 1rem;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .client-address {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .driver-info-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-radius: 12px;
        margin-bottom: 1rem;
    }

    .driver-avatar {
        width: 50px;
        height: 50px;
        background: #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .driver-details {
        flex: 1;
    }

    .driver-name {
        font-weight: 600;
        color: #065f46;
    }

    .driver-phone a {
        color: #047857;
        font-size: 0.85rem;
        text-decoration: none;
    }

    .driver-vehicle {
        font-size: 1.5rem;
    }

    .no-driver-box {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: #fef3c7;
        border-radius: 12px;
        margin-bottom: 1rem;
        color: #92400e;
        font-size: 0.9rem;
    }

    .btn-deliver-myself {
        width: 100%;
        padding: 12px 16px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .btn-deliver-myself:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .btn-deliver-myself:active {
        transform: translateY(0);
    }

    .delivery-fallback-buttons {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .delivery-fallback-buttons form {
        flex: 1;
    }

    .btn-convert-pickup {
        width: 100%;
        padding: 12px 16px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }

    .btn-convert-pickup:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }

    .btn-convert-pickup:active {
        transform: translateY(0);
    }

    .waiting-driver-box {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: #fef3c7;
        border-radius: 8px;
        color: #92400e;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .driver-search-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.5rem;
        background: #e0f2fe;
        border-radius: 6px;
        color: #0369a1;
        font-size: 0.75rem;
        margin-top: 8px;
    }

    .action-btns .action-btn.cancel {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        font-size: 0.85rem;
    }

    .pulse-icon {
        animation: pulse-opacity 1.5s infinite;
    }

    @keyframes pulse-opacity {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .delivery-meta {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }

    .meta-item {
        text-align: center;
        padding: 0.5rem;
        background: #f9fafb;
        border-radius: 8px;
    }

    .meta-label {
        display: block;
        font-size: 0.7rem;
        color: #9ca3af;
        margin-bottom: 0.2rem;
    }

    .meta-value {
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
    }

    .delivery-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .order-total {
        font-size: 1.2rem;
        font-weight: 700;
        color: #10b981;
    }

    .delivery-fee {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #9ca3af;
    }

    .empty-state .icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    /* Sound toggle */
    .sound-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
        cursor: pointer;
    }

    /* Live indicator */
    .live-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    /* Refresh animation */
    .refreshing .refresh-icon {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Empty state amélioré */
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 0.85rem;
    }

    @media (max-width: 640px) {
        .label-mobile {
            display: inline;
        }

        .label-desktop {
            display: none;
        }

        .main-header {
            padding: 0.75rem 0.875rem;
        }

        .header-content {
            gap: 0.75rem;
        }

        .header-left {
            width: 100%;
            gap: 0.75rem;
        }

        .header-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            font-size: 1.15rem;
        }

        .header-info h1 {
            font-size: 1rem;
            line-height: 1.1;
        }

        .header-meta {
            gap: 0.5rem;
        }

        .live-indicator {
            font-size: 0.72rem;
        }

        .header-actions {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.4rem;
        }

        .action-btn {
            justify-content: center;
            padding: 0.7rem 0.35rem;
            border-radius: 10px;
        }

        .tabs-container {
            padding: 0 0.25rem;
        }

        .tab-btn {
            min-width: 0;
            padding: 0.7rem 0.35rem;
            font-size: 0.72rem;
            gap: 0.3rem;
        }

        .tab-btn i {
            font-size: 0.85rem;
        }

        .tab-badge {
            font-size: 0.6rem;
            padding: 0.12rem 0.35rem;
            min-width: 18px;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.3rem;
            padding: 0.5rem;
            overflow: visible;
        }

        .stat-chip {
            flex-direction: column;
            justify-content: center;
            text-align: center;
            gap: 0.12rem;
            padding: 0.35rem 0.2rem;
            border-radius: 9px;
        }

        .stat-chip i {
            width: auto;
            font-size: 0.75rem;
        }

        .stat-chip .value {
            font-size: 0.95rem;
            line-height: 1;
        }

        .stat-chip .label {
            font-size: 0.55rem;
            line-height: 1;
            text-align: center;
        }

        .kanban-container {
            padding: 0.65rem;
            gap: 0.65rem;
            min-height: auto;
        }

        .kanban-column {
            padding: 0.7rem;
            border-radius: 10px;
            min-height: auto;
        }

        .kanban-header {
            margin-bottom: 0.6rem;
            padding-bottom: 0.5rem;
        }

        .kanban-title {
            gap: 0.35rem;
            font-size: 0.8rem;
        }

        .kanban-title i {
            font-size: 0.9rem;
        }

        .kanban-count {
            font-size: 0.65rem;
            padding: 0.15rem 0.45rem;
            min-width: 22px;
        }

        .order-card {
            padding: 0.72rem;
            margin-bottom: 0.55rem;
            border-radius: 10px;
        }

        .order-header {
            margin-bottom: 0.55rem;
            gap: 0.4rem;
        }

        .order-number {
            font-size: 0.95rem;
        }

        .order-time {
            font-size: 0.66rem;
        }

        .order-client {
            font-size: 0.78rem;
            margin-bottom: 0.4rem;
        }

        .order-items {
            font-size: 0.74rem;
            padding: 0.45rem;
            border-radius: 7px;
            margin-bottom: 0.6rem;
        }

        .order-total {
            font-size: 0.95rem;
        }

        .order-type {
            font-size: 0.62rem;
            padding: 0.18rem 0.4rem;
        }

        .action-btns {
            gap: 0.35rem;
            margin-top: 0.6rem;
            padding-top: 0.6rem;
        }

        .action-btns .action-btn {
            min-height: 34px;
            padding: 0.45rem;
            font-size: 0.72rem;
        }

        .deliveries-grid {
            padding: 0.65rem;
            gap: 0.65rem;
        }

        .delivery-card {
            border-radius: 12px;
        }

        .delivery-header,
        .delivery-body,
        .delivery-footer {
            padding: 0.75rem;
        }

        .delivery-header .order-number {
            font-size: 0.96rem;
        }

        .delivery-status-badge {
            font-size: 0.62rem;
            padding: 0.26rem 0.5rem;
        }

        .client-info,
        .driver-info-box,
        .no-driver-box {
            margin-bottom: 0.75rem;
        }

        .client-name {
            font-size: 0.88rem;
        }

        .client-address {
            font-size: 0.75rem;
        }

        .driver-info-box,
        .no-driver-box {
            padding: 0.75rem;
            gap: 0.65rem;
        }

        .driver-avatar {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }

        .driver-name {
            font-size: 0.82rem;
        }

        .driver-phone a {
            font-size: 0.76rem;
        }

        .delivery-fallback-buttons {
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn-deliver-myself,
        .btn-convert-pickup {
            padding: 10px 12px;
            font-size: 0.82rem;
        }

        .delivery-meta {
            gap: 0.35rem;
        }

        .meta-item {
            padding: 0.4rem 0.25rem;
        }

        .meta-label {
            font-size: 0.58rem;
            margin-bottom: 0.15rem;
        }

        .meta-value {
            font-size: 0.72rem;
        }

        .delivery-footer {
            align-items: flex-start;
            gap: 0.5rem;
        }

        .delivery-fee {
            font-size: 0.68rem;
        }

        .history-filters {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            padding: 0.75rem;
        }

        .filter-select {
            width: 100%;
            padding: 0.55rem 0.7rem;
            font-size: 0.8rem;
        }

        .history-list {
            padding: 0.75rem;
        }

        .history-card {
            padding: 0.75rem;
            gap: 0.6rem;
            border-radius: 10px;
            margin-bottom: 0.55rem;
        }

        .history-info .number {
            font-size: 0.82rem;
        }

        .history-info .date {
            font-size: 0.68rem;
            line-height: 1.2;
        }

        .history-total {
            font-size: 0.88rem;
        }
    }
</style>
@endpush

@section('content')
{{-- Section d'aide --}}
<div class="px-3 pt-2 sm:px-4 sm:pt-4 bg-gray-50">
    <x-help-section page="prestataire.food-orders.unified" />
</div>

<!-- Header Pro -->
<div class="main-header">
    <div class="header-content">
        <div class="header-left">
            <div class="header-logo">
                <i class="fas fa-fire-burner"></i>
            </div>
            <div class="header-info">
                <h1>🍳 Cuisine Live</h1>
                <div class="header-meta">
                    <div class="live-indicator">
                        <span class="dot"></span>
                        <span>En direct</span>
                    </div>
                    <div class="header-clock" id="clock">--:--:--</div>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <button class="action-btn sound-btn" id="soundBtn" title="Son des notifications" type="button">
                <i class="fas fa-bell" id="soundIcon"></i>
            </button>
            <button class="action-btn refresh-btn" id="refreshBtn" title="Actualiser" type="button">
                <i class="fas fa-sync-alt"></i>
                <span>Actualiser</span>
            </button>
            <a href="{{ route('prestataire.food-delivery.index') }}" class="action-btn delivery-btn" title="Gestion livraisons">
                <i class="fas fa-motorcycle"></i>
                <span>Livraisons</span>
            </a>
            <a href="{{ route('prestataire.drivers.index') }}" class="action-btn menu-btn" title="Gestion des livreurs">
                <i class="fas fa-users"></i>
                <span>Livreurs</span>
            </a>
            <a href="{{ route('prestataire.food-products.index') }}" class="action-btn menu-btn" title="Gérer le menu">
                <i class="fas fa-utensils"></i>
                <span>Menu</span>
            </a>
            <a href="{{ route('prestataire.dashboard') }}" class="action-btn home-btn" title="Tableau de bord">
                <i class="fas fa-home"></i>
            </a>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-container">
    <div class="tab-btn active" data-tab="live" role="button" tabindex="0">
        <i class="fas fa-fire"></i>
        <span>Cuisine</span>
        @php
            $totalActive = $orders['pending']->count() + $orders['accepted']->count() + $orders['preparing']->count() + $orders['ready']->count();
        @endphp
        @if($totalActive > 0)
            <span class="tab-badge orange">{{ $totalActive }}</span>
        @endif
    </div>
    <div class="tab-btn" data-tab="deliveries" role="button" tabindex="0">
        <i class="fas fa-motorcycle"></i>
        <span>Livraisons</span>
        @php
            $totalDeliveries = ($deliveryOrders ?? collect())->count();
        @endphp
        @if($totalDeliveries > 0)
            <span class="tab-badge green">{{ $totalDeliveries }}</span>
        @endif
    </div>
    <div class="tab-btn" data-tab="history" role="button" tabindex="0">
        <i class="fas fa-history"></i>
        <span>Historique</span>
    </div>
</div>

<!-- Stats Bar -->
<div class="stats-bar">
    <div class="stat-chip revenue">
        <i class="fas fa-euro-sign"></i>
        <span class="value">{{ number_format($paymentStats['revenue_today'] ?? 0, 2) }}€</span>
        <span class="label"><span class="label-mobile">Jour</span><span class="label-desktop">aujourd'hui</span></span>
    </div>
    <div class="stat-chip pending">
        <i class="fas fa-clock"></i>
        <span class="value">{{ $orders['pending']->count() }}</span>
        <span class="label"><span class="label-mobile">Att.</span><span class="label-desktop">attente</span></span>
    </div>
    <div class="stat-chip cooking">
        <i class="fas fa-fire-alt"></i>
        <span class="value">{{ $orders['preparing']->count() }}</span>
        <span class="label"><span class="label-mobile">Cuisine</span><span class="label-desktop">en cuisine</span></span>
    </div>
    <div class="stat-chip ready">
        <i class="fas fa-check-circle"></i>
        <span class="value">{{ $orders['ready']->count() }}</span>
        <span class="label"><span class="label-mobile">Prêtes</span><span class="label-desktop">prêtes</span></span>
    </div>
</div>

<!-- Tab Content: Live -->
<div class="tab-content active" id="tab-live">
    <div class="kanban-container">
        <!-- Colonne En attente -->
        <div class="kanban-column">
            <div class="kanban-header pending">
                <div class="kanban-title">
                    <i class="fas fa-clock"></i>
                    <span>En attente</span>
                </div>
                <span class="kanban-count">{{ $orders['pending']->count() }}</span>
            </div>
            @forelse($orders['pending'] as $order)
                <div class="order-card {{ $order->created_at->diffInMinutes(now()) > 5 ? 'urgent' : '' }}"
                     data-food-dashboard-card
                     data-href="{{ route('prestataire.food-orders.show', $order) }}"
                     role="link"
                     tabindex="0">
                    <div class="order-header">
                        <span class="order-number">#{{ $order->order_number }}</span>
                        <span class="order-time"><i class="far fa-clock"></i> {{ $order->created_at->diffForHumans(null, true) }}</span>
                    </div>
                    <div class="order-client"><i class="far fa-user"></i> {{ $order->client->name ?? 'Client' }}</div>
                    <div class="order-items">
                        @foreach($order->items->take(3) as $item)
                            <div>{{ $item->quantity }}x {{ $item->product_name }}</div>
                        @endforeach
                        @if($order->items->count() > 3)
                            <div class="text-gray-400">+{{ $order->items->count() - 3 }} autre(s)</div>
                        @endif
                    </div>
                    <div class="order-footer">
                        <span class="order-total">{{ number_format($order->total, 2) }}€</span>
                        <span class="order-type {{ $order->delivery_type }}">
                            @if($order->delivery_type === 'delivery')
                                <i class="fas fa-motorcycle"></i> Livraison
                            @else
                                <i class="fas fa-store"></i> À emporter
                            @endif
                        </span>
                    </div>
                    <div class="action-btns">
                        <form action="{{ route('prestataire.food-orders.accept', $order) }}" method="POST" style="flex:1">
                            @csrf
                            <button type="submit" class="action-btn accept w-full"><i class="fas fa-check"></i> Accepter</button>
                        </form>
                        <form action="{{ route('prestataire.food-orders.reject', $order) }}" method="POST" style="flex-shrink:0">
                            @csrf
                            <button type="submit" class="action-btn reject"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-mug-hot"></i>
                    <p>Aucune commande en attente</p>
                </div>
            @endforelse
        </div>

        <!-- Colonne Acceptées -->
        <div class="kanban-column">
            <div class="kanban-header accepted">
                <div class="kanban-title">
                    <i class="fas fa-check"></i>
                    <span>Acceptées</span>
                </div>
                <span class="kanban-count">{{ $orders['accepted']->count() }}</span>
            </div>
            @forelse($orders['accepted'] as $order)
                <div class="order-card"
                     data-food-dashboard-card
                     data-href="{{ route('prestataire.food-orders.show', $order) }}"
                     role="link"
                     tabindex="0">
                    <div class="order-header">
                        <span class="order-number">#{{ $order->order_number }}</span>
                        <span class="order-time"><i class="far fa-clock"></i> {{ $order->accepted_at?->diffForHumans(null, true) ?? '-' }}</span>
                    </div>
                    <div class="order-client"><i class="far fa-user"></i> {{ $order->client->name ?? 'Client' }}</div>
                    <div class="order-items">
                        @foreach($order->items->take(2) as $item)
                            <div>{{ $item->quantity }}x {{ $item->product_name }}</div>
                        @endforeach
                    </div>
                    <div class="order-footer">
                        <span class="order-total">{{ number_format($order->total, 2) }}€</span>
                        <span class="order-type {{ $order->delivery_type }}">
                            @if($order->delivery_type === 'delivery')
                                <i class="fas fa-motorcycle"></i>
                            @else
                                <i class="fas fa-store"></i>
                            @endif
                        </span>
                    </div>
                    <div class="action-btns">
                        <form action="{{ route('prestataire.food-orders.start-preparing', $order) }}" method="POST" style="flex:1">
                            @csrf
                            <button type="submit" class="action-btn prepare w-full"><i class="fas fa-fire-alt"></i> Préparer</button>
                        </form>
                        @if($order->delivery_type === 'delivery' && !$order->driver_id)
                            <div class="driver-search-info">
                                <i class="fas fa-search pulse-icon"></i> Recherche livreur
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>Aucune commande acceptée</p>
                </div>
            @endforelse
        </div>

        <!-- Colonne En préparation -->
        <div class="kanban-column">
            <div class="kanban-header preparing">
                <div class="kanban-title">
                    <i class="fas fa-fire-alt"></i>
                    <span>En cuisine</span>
                </div>
                <span class="kanban-count">{{ $orders['preparing']->count() }}</span>
            </div>
            @forelse($orders['preparing'] as $order)
                <div class="order-card"
                     data-food-dashboard-card
                     data-href="{{ route('prestataire.food-orders.show', $order) }}"
                     role="link"
                     tabindex="0">
                    <div class="order-header">
                        <span class="order-number">#{{ $order->order_number }}</span>
                        <span class="order-time"><i class="far fa-clock"></i> {{ $order->updated_at->diffForHumans(null, true) }}</span>
                    </div>
                    <div class="order-client"><i class="far fa-user"></i> {{ $order->client->name ?? 'Client' }}</div>
                    <div class="order-items">
                        @foreach($order->items as $item)
                            <div>{{ $item->quantity }}x {{ $item->product_name }}</div>
                        @endforeach
                    </div>
                    <div class="order-footer">
                        <span class="order-total">{{ number_format($order->total, 2) }}€</span>
                        <span class="order-type {{ $order->delivery_type }}">
                            @if($order->delivery_type === 'delivery')
                                <i class="fas fa-motorcycle"></i>
                            @else
                                <i class="fas fa-store"></i>
                            @endif
                        </span>
                    </div>
                    <div class="action-btns">
                        <form action="{{ route('prestataire.food-orders.ready', $order) }}" method="POST" style="flex:1">
                            @csrf
                            <button type="submit" class="action-btn ready w-full"><i class="fas fa-check"></i> Prête !</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-user-chef"></i>
                    <p>Aucune commande en préparation</p>
                </div>
            @endforelse
        </div>

        <!-- Colonne Prêtes -->
        <div class="kanban-column">
            <div class="kanban-header ready">
                <div class="kanban-title">
                    <i class="fas fa-check-circle"></i>
                    <span>Prêtes</span>
                </div>
                <span class="kanban-count">{{ $orders['ready']->count() }}</span>
            </div>
            @forelse($orders['ready'] as $order)
                <div class="order-card"
                     data-food-dashboard-card
                     data-href="{{ route('prestataire.food-orders.show', $order) }}"
                     role="link"
                     tabindex="0">
                    <div class="order-header">
                        <span class="order-number">#{{ $order->order_number }}</span>
                        <span class="order-time"><i class="fas fa-check text-green-500"></i> {{ $order->ready_at?->diffForHumans(null, true) ?? '-' }}</span>
                    </div>
                    <div class="order-client"><i class="far fa-user"></i> {{ $order->client->name ?? 'Client' }}</div>
                    <div class="order-items">
                        @foreach($order->items->take(2) as $item)
                            <div>{{ $item->quantity }}x {{ $item->product_name }}</div>
                        @endforeach
                    </div>
                    <div class="order-footer">
                        <span class="order-total">{{ number_format($order->total, 2) }}€</span>
                        <span class="order-type {{ $order->delivery_type }}">
                            @if($order->delivery_type === 'delivery')
                                <i class="fas fa-motorcycle"></i> Livraison
                            @else
                                <i class="fas fa-store"></i> À récupérer
                            @endif
                        </span>
                    </div>
                    <div class="action-btns">
                        @if(($order->delivery_type ?? '') === 'pickup')
                            <form action="{{ route('prestataire.food-orders.delivered', $order) }}" method="POST" style="flex:1">
                                @csrf
                                <button type="submit" class="action-btn deliver w-full">
                                    <i class="fas fa-handshake"></i> Récupérée
                                </button>
                            </form>
                        @else
                            <button type="button" class="action-btn w-full" disabled style="background:#94a3b8;cursor:not-allowed;">
                                <i class="fas fa-key"></i> Validation livreur (code)
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-party-horn"></i>
                    <p>Aucune commande prête</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Tab Content: Deliveries -->
<div class="tab-content" id="tab-deliveries">
    <div class="deliveries-grid">
        @forelse($deliveryOrders ?? [] as $order)
            <div class="delivery-card"
                 data-food-dashboard-card
                 data-href="{{ route('prestataire.food-orders.show', $order) }}"
                 role="link"
                 tabindex="0">
                <div class="delivery-header">
                    <span class="order-number">#{{ $order->order_number }}</span>
                    <span class="delivery-status-badge {{ $order->delivery_status ?? 'pending' }}">
                        @switch($order->delivery_status ?? 'pending')
                            @case('assigned')
                                <i class="fas fa-user-check"></i> Livreur assigné
                                @break
                            @case('picked_up')
                                <i class="fas fa-box"></i> Récupérée
                                @break
                            @case('in_transit')
                                <i class="fas fa-shipping-fast"></i> En route
                                @break
                            @default
                                <i class="fas fa-hourglass-half"></i> En attente livreur
                        @endswitch
                    </span>
                </div>
                
                <div class="delivery-body">
                    <div class="client-info">
                        <div class="client-name"><i class="far fa-user"></i> {{ $order->client->name ?? 'Client' }}</div>
                        <div class="client-address"><i class="fas fa-map-marker-alt"></i> {{ $order->delivery_address ?? 'Adresse non renseignée' }}</div>
                    </div>
                    
                    @if($order->driver)
                        <div class="driver-info-box">
                            <div class="driver-avatar"><i class="fas fa-biking"></i></div>
                            <div class="driver-details">
                                <div class="driver-name">{{ $order->driver->user->name ?? 'Livreur' }}</div>
                                <div class="driver-phone">
                                    @if($order->driver->user->phone)
                                        <a href="tel:{{ $order->driver->user->phone }}">
                                            <i class="fas fa-phone"></i> {{ $order->driver->user->phone }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="driver-vehicle">
                                @if($order->driver->vehicle_type === 'bike')
                                    <i class="fas fa-bicycle"></i>
                                @elseif($order->driver->vehicle_type === 'scooter')
                                    <i class="fas fa-motorcycle"></i>
                                @elseif($order->driver->vehicle_type === 'car')
                                    <i class="fas fa-car"></i>
                                @else
                                    <i class="fas fa-biking"></i>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="no-driver-box">
                            <i class="fas fa-search pulse-icon"></i>
                            <span>Recherche d'un livreur...</span>
                        </div>
                        <div class="delivery-fallback-buttons">
                            <form action="{{ route('food-orders.deliver-myself', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-deliver-myself">
                                    <i class="fas fa-car"></i> Je livre moi-même
                                </button>
                            </form>
                            <form action="{{ route('food-orders.convert-to-pickup', $order) }}" method="POST">
                                @csrf
                                <button
                                    type="submit"
                                    class="btn-convert-pickup"
                                    data-confirm="Convertir en retrait sur place ? Le client sera notifié et les frais de livraison remboursés."
                                >
                                    <i class="fas fa-store"></i> Convertir en retrait
                                </button>
                            </form>
                        </div>
                    @endif
                    
                    <div class="delivery-meta">
                        <div class="meta-item">
                            <span class="meta-label">Prête depuis</span>
                            <span class="meta-value">{{ $order->ready_at ? $order->ready_at->diffForHumans() : '-' }}</span>
                        </div>
                        @if($order->delivery_distance)
                            <div class="meta-item">
                                <span class="meta-label">Distance</span>
                                <span class="meta-value">{{ number_format($order->delivery_distance, 1) }} km</span>
                            </div>
                        @endif
                        @if($order->estimated_delivery_time)
                            <div class="meta-item">
                                <span class="meta-label">Livraison estimée</span>
                                <span class="meta-value">~{{ $order->estimated_delivery_time }} min</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="delivery-footer">
                    <span class="order-total">{{ number_format($order->total, 2) }}€</span>
                    @if($order->delivery_fee > 0)
                        <span class="delivery-fee">dont {{ number_format($order->delivery_fee, 2) }}€ livraison</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-truck"></i>
                <h3>Aucune livraison en cours</h3>
                <p>Les commandes en livraison apparaîtront ici</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Tab Content: History -->
<div class="tab-content" id="tab-history">
    <div class="history-filters">
        <select class="filter-select" id="statusFilter">
            <option value="">Tous les statuts</option>
            <option value="completed">Terminées</option>
            <option value="delivered">Livrées</option>
            <option value="cancelled">Annulées</option>
        </select>
        <input type="date" class="filter-select" id="dateFilter" value="{{ date('Y-m-d') }}">
    </div>
    <div class="history-list" id="historyList">
        @foreach($historyOrders ?? [] as $order)
            <div class="history-card"
                 data-food-dashboard-card
                 data-href="{{ route('prestataire.food-orders.show', $order) }}"
                 role="link"
                 tabindex="0">
                <div class="status-dot {{ $order->status }}"></div>
                <div class="history-info">
                    <div class="number">#{{ $order->order_number }}</div>
                    <div class="date">{{ $order->created_at->format('d/m/Y H:i') }} • {{ $order->client->name ?? 'Client' }}</div>
                </div>
                <div class="history-total">{{ number_format($order->total, 2) }}€</div>
            </div>
        @endforeach
        @if(empty($historyOrders) || count($historyOrders) === 0)
            <div class="empty-state">
                <div class="icon">📜</div>
                <p>Chargez l'historique avec les filtres</p>
            </div>
        @endif
    </div>
</div>

<!-- Audio pour notifications -->
<audio id="notificationSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleBoAQdff5tJVITBjnNbc4rFSFyVvqN/j3KFGDit5sOPj35VBDD+ByuXg14w3ETCKzujf0YAqFT2O0endzXQfGEKT1evax2kVG0yZ2uvVvFsRHVWe3OvQuU4NH2Oi3+rMrUIKImet4OfGoTYHKLC27uO5oygEL7u+7t2qkRkBOcTJ7M2YeAQ" type="audio/wav">
</audio>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
let soundEnabled = localStorage.getItem('orderSound') !== 'false';
let lastOrderCount = {{ $orders['pending']->count() }};

// Clock avec secondes
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
updateClock();
setInterval(updateClock, 1000);

// Tab switching
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(`tab-${tab}`).classList.add('active');

    if (tab === 'history') {
        filterHistory();
    }
}

// Sound toggle
function toggleSound() {
    soundEnabled = !soundEnabled;
    localStorage.setItem('orderSound', soundEnabled);
    updateSoundIcon();
}

function updateSoundIcon() {
    const icon = document.getElementById('soundIcon');
    const btn = document.getElementById('soundBtn');
    if (soundEnabled) {
        icon.className = 'fas fa-bell';
        btn.classList.remove('muted');
    } else {
        icon.className = 'fas fa-bell-slash';
        btn.classList.add('muted');
    }
}
updateSoundIcon();

// Play notification
function playNotification() {
    if (soundEnabled) {
        try {
            document.getElementById('notificationSound').play();
        } catch(e) {}
    }
}

// Refresh data
function refreshData() {
    const btn = document.getElementById('refreshBtn');
    btn.classList.add('refreshing');
    location.reload();
}

function initializeDashboardInteractions() {
    document.querySelectorAll('.tab-btn[data-tab]').forEach((tabButton) => {
        const activate = () => switchTab(tabButton.dataset.tab);

        tabButton.addEventListener('click', activate);
        tabButton.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            activate();
        });
    });

    document.querySelectorAll('[data-food-dashboard-card]').forEach((card) => {
        const navigate = () => {
            const href = card.dataset.href;

            if (href) {
                window.location.href = href;
            }
        };

        card.addEventListener('click', (event) => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            if (target.closest('a, button, input, select, textarea, label, form')) {
                return;
            }

            navigate();
        });

        card.addEventListener('keydown', (event) => {
            if ((event.key !== 'Enter' && event.key !== ' ') || event.target !== card) {
                return;
            }

            event.preventDefault();
            navigate();
        });
    });

    document.getElementById('soundBtn')?.addEventListener('click', toggleSound);
    document.getElementById('refreshBtn')?.addEventListener('click', refreshData);
    document.getElementById('statusFilter')?.addEventListener('change', filterHistory);
    document.getElementById('dateFilter')?.addEventListener('change', filterHistory);

    document.querySelectorAll('[data-confirm]').forEach((button) => {
        button.addEventListener('click', (event) => {
            if (!window.confirm(button.dataset.confirm || 'Confirmer cette action ?')) {
                event.preventDefault();
            }
        });
    });

    const initialTab = new URLSearchParams(window.location.search).get('tab');
    const normalizedTab = initialTab === 'delivery' ? 'deliveries' : initialTab;

    if (normalizedTab && document.querySelector(`.tab-btn[data-tab="${normalizedTab}"]`) && document.getElementById(`tab-${normalizedTab}`)) {
        switchTab(normalizedTab);
    }
}

initializeDashboardInteractions();

// Auto refresh every 30s
setInterval(() => {
    fetch('{{ route('prestataire.food-orders.new') }}')
        .then(r => r.json())
        .then(data => {
            if (data.count > lastOrderCount) {
                playNotification();
                // Afficher une notification
                if (Notification.permission === 'granted') {
                    new Notification('Nouvelle commande !', {
                        body: 'Vous avez une nouvelle commande à traiter',
                        icon: '/icons/icon-192x192.png'
                    });
                }
            }
            lastOrderCount = data.count;
        })
        .catch(console.error);
}, 30000);

// Filter history
async function filterHistory() {
    const status = document.getElementById('statusFilter').value;
    const date = document.getElementById('dateFilter').value;
    
    try {
        const response = await fetch(`{{ route('prestataire.food-orders.index') }}?status=${status}&date=${date}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        // For now, just reload the page with filters
        // In production, you'd parse the response and update the DOM
    } catch (error) {
        console.error('Filter error:', error);
    }
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
</script>
@endpush
