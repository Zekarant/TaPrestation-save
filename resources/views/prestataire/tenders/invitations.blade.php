<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invitations - TapRestation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: #1e293b;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
        }
        
        .header-content {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            opacity: 0.9;
            transition: opacity 0.2s;
        }
        
        .back-link:hover {
            opacity: 1;
        }
        
        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        /* Container */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }
        
        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .page-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.3);
        }
        
        .page-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 0.95rem;
        }
        
        /* How it works */
        .how-it-works {
            background: linear-gradient(135deg, #ede9fe 0%, #dbeafe 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .how-it-works h3 {
            font-size: 1rem;
            color: #5b21b6;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .how-it-works p {
            color: #4c1d95;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }
        
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            background: white;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .filter-tab:hover,
        .filter-tab.active {
            background: #7c3aed;
            color: white;
        }
        
        .filter-tab .star {
            color: #f59e0b;
        }
        
        /* Invitations List */
        .invitations-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .invitation-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .invitation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .invitation-card.unread {
            border-left: 4px solid #7c3aed;
        }
        
        .invitation-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .invitation-title {
            flex: 1;
        }
        
        .invitation-title h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        
        .invitation-title h3 a {
            color: #1e293b;
            text-decoration: none;
        }
        
        .invitation-title h3 a:hover {
            color: #7c3aed;
        }
        
        .invitation-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: #64748b;
        }
        
        .invitation-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .invitation-meta i {
            color: #7c3aed;
        }
        
        .match-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 12px;
            min-width: 60px;
        }
        
        .match-badge .score {
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        .match-badge .label {
            font-size: 0.6rem;
            text-transform: uppercase;
            opacity: 0.9;
        }
        
        .invitation-body {
            padding: 1rem;
        }
        
        .invitation-description {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .invitation-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        @media (min-width: 640px) {
            .invitation-details {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .detail-item {
            text-align: center;
            padding: 0.5rem;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .detail-label {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .detail-value.budget {
            color: #10b981;
        }
        
        .invitation-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            flex: 1;
            min-width: 120px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #e2e8f0;
            color: #64748b;
        }
        
        .btn-outline:hover {
            border-color: #7c3aed;
            color: #7c3aed;
        }
        
        .btn-danger-outline {
            background: transparent;
            border: 2px solid #fecaca;
            color: #dc2626;
        }
        
        .btn-danger-outline:hover {
            background: #fee2e2;
        }
        
        /* Time badge */
        .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: #fef3c7;
            color: #92400e;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .time-badge.new {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .empty-icon i {
            font-size: 2rem;
            color: #94a3b8;
        }
        
        .empty-state h3 {
            font-size: 1.25rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
        
        .tips-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.25rem;
            text-align: left;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .tips-box h4 {
            font-size: 0.9rem;
            color: #1e293b;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .tips-box h4 i {
            color: #f59e0b;
        }
        
        .tips-box ul {
            list-style: none;
            padding: 0;
        }
        
        .tips-box li {
            font-size: 0.85rem;
            color: #64748b;
            padding: 0.35rem 0;
            padding-left: 1.5rem;
            position: relative;
        }
        
        .tips-box li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }
        
        .tips-box a {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 1rem;
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .tips-box a:hover {
            text-decoration: underline;
        }
        
        /* Pagination */
        .pagination-wrapper {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
        
        .pagination-wrapper nav > div:first-child {
            display: none;
        }
        
        .pagination-wrapper a,
        .pagination-wrapper span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0.5rem 0.75rem;
            background: white;
            border-radius: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0 0.125rem;
            border: 1px solid #e2e8f0;
        }
        
        .pagination-wrapper a:hover {
            background: #7c3aed;
            color: white;
        }
        
        .pagination-wrapper span[aria-current="page"] span {
            background: #7c3aed;
            color: white;
        }
        
        .pagination-wrapper svg {
            width: 16px;
            height: 16px;
        }
        
        .pagination-wrapper p {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="{{ route('prestataire.tenders.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Appels d'offres</span>
            </a>
            <span class="header-title">Mes invitations</span>
            <div style="width: 100px;"></div>
        </div>
    </header>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h1>Invitations personnalisées</h1>
            <p class="page-subtitle">Appels d'offres correspondant à votre profil et vos compétences</p>
        </div>
        
        <!-- How it works -->
        <div class="how-it-works">
            <h3><i class="fas fa-magic"></i> Comment ça marche ?</h3>
            <p>Notre algorithme analyse automatiquement les nouvelles demandes et vous invite lorsqu'elles correspondent à vos catégories, votre localisation et vos compétences.</p>
            <div class="filter-tabs">
                <a href="{{ route('prestataire.tenders.invitations') }}" class="filter-tab {{ !request('filter') ? 'active' : '' }}">
                    Toutes
                </a>
                <a href="{{ route('prestataire.tenders.invitations', ['filter' => 'unread']) }}" class="filter-tab {{ request('filter') === 'unread' ? 'active' : '' }}">
                    <i class="fas fa-circle" style="font-size: 0.5rem; color: #7c3aed;"></i> Non lues
                </a>
                <a href="{{ route('prestataire.tenders.invitations', ['filter' => 'high_match']) }}" class="filter-tab {{ request('filter') === 'high_match' ? 'active' : '' }}">
                    <i class="fas fa-star star"></i> Haute correspondance (80%+)
                </a>
            </div>
        </div>
        
        <!-- Invitations List -->
        @if(isset($invitations) && $invitations->count() > 0)
            <div class="invitations-list">
                @foreach($invitations as $invitation)
                    @php
                        $tender = $invitation->tenderRequest ?? $invitation->tender ?? null;
                    @endphp
                    @if($tender)
                        <div class="invitation-card {{ !$invitation->read_at ? 'unread' : '' }}">
                            <div class="invitation-header">
                                <div class="invitation-title">
                                    <h3>
                                        <a href="{{ route('prestataire.tenders.show', $tender) }}">
                                            {{ $tender->title }}
                                        </a>
                                    </h3>
                                    <div class="invitation-meta">
                                        <span><i class="fas fa-map-marker-alt"></i> {{ $tender->city }}</span>
                                        <span><i class="fas fa-calendar"></i> {{ $tender->start_date ? $tender->start_date->format('d/m/Y') : 'Flexible' }}</span>
                                        <span class="time-badge {{ $invitation->created_at->diffInHours() < 24 ? 'new' : '' }}">
                                            <i class="fas fa-clock"></i>
                                            {{ $invitation->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="match-badge">
                                    <span class="score">{{ $invitation->match_score ?? 0 }}%</span>
                                    <span class="label">Match</span>
                                </div>
                            </div>
                            
                            <div class="invitation-body">
                                <p class="invitation-description">{{ Str::limit($tender->description, 150) }}</p>
                                
                                <div class="invitation-details">
                                    <div class="detail-item">
                                        <div class="detail-label">Budget</div>
                                        <div class="detail-value budget">
                                            @if($tender->budget_visible && $tender->budget_max)
                                                {{ number_format($tender->budget_max, 0, ',', ' ') }} €
                                            @else
                                                Sur devis
                                            @endif
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Type</div>
                                        <div class="detail-value">
                                            @switch($tender->budget_type ?? 'fixed')
                                                @case('hourly') Par heure @break
                                                @case('daily') Par jour @break
                                                @default Prix fixe @break
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Urgence</div>
                                        <div class="detail-value">
                                            @switch($tender->urgency ?? 'normal')
                                                @case('urgent') 🔴 Urgent @break
                                                @case('high') 🟠 Prioritaire @break
                                                @default 🟢 Normal @break
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Propositions</div>
                                        <div class="detail-value">{{ $tender->responses_count ?? 0 }}/{{ $tender->max_responses ?? 10 }}</div>
                                    </div>
                                </div>
                                
                                <div class="invitation-actions">
                                    <a href="{{ route('prestataire.tenders.show', $tender) }}" class="btn btn-outline">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </a>
                                    <a href="{{ route('prestataire.tenders.respond', $tender) }}" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Proposer
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if(method_exists($invitations, 'links') && $invitations->hasPages())
                <div class="pagination-wrapper">
                    {{ $invitations->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Aucune invitation</h3>
                <p>Vous n'avez pas encore d'invitation personnalisée.</p>
                
                <div class="tips-box">
                    <h4><i class="fas fa-lightbulb"></i> Conseils pour recevoir plus d'invitations</h4>
                    <ul>
                        <li>Complétez votre profil à 100%</li>
                        <li>Ajoutez plus de catégories de services</li>
                        <li>Précisez votre zone d'intervention</li>
                        <li>Obtenez des avis positifs</li>
                    </ul>
                    <a href="{{ route('prestataire.profile.edit') }}">
                        <i class="fas fa-user-edit"></i> Améliorer mon profil
                    </a>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
