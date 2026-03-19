@extends('layouts.client')

@section('title', 'Mes Appels d\'Offres')

@section('content')
<div style="max-width: 100%; overflow-x: hidden; padding: 1rem; padding-bottom: 100px;">
    
    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 1.5rem; margin-bottom: 1.5rem; color: white; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-bullhorn" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Mes Appels d'Offres</h1>
                <p style="font-size: 0.85rem; opacity: 0.9; margin: 0.25rem 0 0 0;">Gérez vos demandes et propositions</p>
            </div>
        </div>
        <a href="{{ route('client.tenders.create') }}" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: white; color: #667eea; padding: 0.875rem 1.5rem; border-radius: 12px; font-weight: 600; text-decoration: none; transition: transform 0.2s;">
            <i class="fas fa-plus"></i>
            Nouvel appel d'offre
        </a>
    </div>

    {{-- Statistiques --}}
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1.5rem;">
        <div style="background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-alt" style="color: white; font-size: 1rem;"></i>
            </div>
            <div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #1a1a2e;">{{ $stats['total'] }}</div>
                <div style="font-size: 0.75rem; color: #64748b;">Total</div>
            </div>
        </div>
        <div style="background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-broadcast-tower" style="color: white; font-size: 1rem;"></i>
            </div>
            <div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #1a1a2e;">{{ $stats['published'] }}</div>
                <div style="font-size: 0.75rem; color: #64748b;">Publiés</div>
            </div>
        </div>
        <div style="background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-spinner" style="color: white; font-size: 1rem;"></i>
            </div>
            <div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #1a1a2e;">{{ $stats['in_progress'] }}</div>
                <div style="font-size: 0.75rem; color: #64748b;">En cours</div>
            </div>
        </div>
        <div style="background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-check-circle" style="color: white; font-size: 1rem;"></i>
            </div>
            <div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #1a1a2e;">{{ $stats['completed'] }}</div>
                <div style="font-size: 0.75rem; color: #64748b;">Terminés</div>
            </div>
        </div>
    </div>

    {{-- Filtres de recherche --}}
    <div style="background: white; border-radius: 16px; padding: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('client.tenders.index') }}">
            {{-- Barre de recherche --}}
            <div style="position: relative; margin-bottom: 1rem;">
                <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par titre, ville, référence..." style="width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.875rem; outline: none; transition: border-color 0.2s; box-sizing: border-box;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            {{-- Filtres en ligne --}}
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                {{-- Filtre par statut --}}
                <select name="status" onchange="this.form.submit()" style="flex: 1; min-width: 120px; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.8rem; background: white; cursor: pointer; outline: none;">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tous les statuts</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>📝 Brouillon</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>✅ Publié</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>⏳ En cours</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🎉 Terminé</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Annulé</option>
                </select>

                {{-- Filtre par urgence --}}
                <select name="urgency" onchange="this.form.submit()" style="flex: 1; min-width: 120px; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.8rem; background: white; cursor: pointer; outline: none;">
                    <option value="all" {{ request('urgency') == 'all' ? 'selected' : '' }}>Toutes urgences</option>
                    <option value="normal" {{ request('urgency') == 'normal' ? 'selected' : '' }}>🟢 Normal</option>
                    <option value="high" {{ request('urgency') == 'high' ? 'selected' : '' }}>🟠 Prioritaire</option>
                    <option value="urgent" {{ request('urgency') == 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                </select>

                {{-- Bouton rechercher --}}
                <button type="submit" style="padding: 0.75rem 1.25rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-filter"></i>
                    Filtrer
                </button>

                {{-- Bouton reset --}}
                @if(request()->hasAny(['search', 'status', 'urgency']))
                    <a href="{{ route('client.tenders.index') }}" style="padding: 0.75rem 1.25rem; background: #f1f5f9; color: #64748b; border-radius: 10px; font-weight: 600; font-size: 0.8rem; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-times"></i>
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Résultats de recherche --}}
    @if(request()->hasAny(['search', 'status', 'urgency']) && (request('status') !== 'all' || request('urgency') !== 'all' || request('search')))
        <div style="background: #f0f0ff; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <i class="fas fa-info-circle" style="color: #667eea;"></i>
            <span style="font-size: 0.85rem; color: #475569;">
                {{ $tenders->total() }} résultat{{ $tenders->total() > 1 ? 's' : '' }} trouvé{{ $tenders->total() > 1 ? 's' : '' }}
                @if(request('search'))
                    pour "<strong>{{ request('search') }}</strong>"
                @endif
            </span>
        </div>
    @endif

    {{-- Liste des appels d'offres --}}
    @if($tenders->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($tenders as $tender)
                <div style="background: white; border-radius: 16px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); position: relative; overflow: hidden; {{ $tender->is_expired ? 'opacity: 0.7;' : '' }}">
                    
                    {{-- Header avec statut --}}
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            @php
                                $statusColors = [
                                    'draft' => 'background: #f1f5f9; color: #64748b;',
                                    'published' => 'background: #dcfce7; color: #16a34a;',
                                    'in_progress' => 'background: #fef3c7; color: #d97706;',
                                    'completed' => 'background: #e0e7ff; color: #7c3aed;',
                                    'cancelled' => 'background: #fee2e2; color: #dc2626;',
                                    'expired' => 'background: #f1f5f9; color: #64748b;',
                                ];
                                $statusStyle = $statusColors[$tender->status] ?? 'background: #f1f5f9; color: #64748b;';
                            @endphp
                            <span style="{{ $statusStyle }} padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                {{ $tender->status_label }}
                            </span>
                            @if($tender->urgency === 'urgent')
                                <span style="background: #fee2e2; color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    <i class="fas fa-exclamation-triangle"></i> Urgent
                                </span>
                            @elseif($tender->urgency === 'high')
                                <span style="background: #fef3c7; color: #d97706; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    <i class="fas fa-bolt"></i> Prioritaire
                                </span>
                            @endif
                        </div>
                        <span style="font-size: 0.75rem; color: #94a3b8;">
                            <i class="fas fa-clock"></i> {{ $tender->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Titre --}}
                    <h3 style="font-size: 1.1rem; font-weight: 600; color: #1a1a2e; margin: 0 0 0.5rem 0; word-wrap: break-word; overflow-wrap: break-word;">
                        <a href="{{ route('client.tenders.show', $tender) }}" style="color: inherit; text-decoration: none;">
                            {{ $tender->title }}
                        </a>
                    </h3>

                    {{-- Description --}}
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0 0 1rem 0; line-height: 1.5; word-wrap: break-word; overflow-wrap: break-word;">
                        {{ Str::limit($tender->description, 120) }}
                    </p>

                    {{-- Méta infos --}}
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; font-size: 0.8rem; color: #64748b;">
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fas fa-map-marker-alt" style="color: #667eea;"></i>
                            <span style="word-break: break-word;">{{ $tender->city }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fas fa-calendar" style="color: #667eea;"></i>
                            <span>{{ $tender->start_date ? $tender->start_date->format('d/m/Y') : 'Non définie' }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fas fa-euro-sign" style="color: #667eea;"></i>
                            <span>{{ $tender->budget_display }}</span>
                        </div>
                    </div>

                    {{-- Catégories --}}
                    @if($tender->categories->count() > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                            @foreach($tender->categories->take(3) as $category)
                                <span style="background: #f0f0ff; color: #667eea; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 500;">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                            @if($tender->categories->count() > 3)
                                <span style="background: #f1f5f9; color: #64748b; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 500;">
                                    +{{ $tender->categories->count() - 3 }}
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Footer avec propositions --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: 10px;">
                                <i class="fas fa-comments" style="color: #667eea;"></i>
                                <span style="font-weight: 600; color: #1a1a2e;">{{ $tender->responses->count() }}</span>
                                <span style="font-size: 0.75rem; color: #64748b;">proposition{{ $tender->responses->count() > 1 ? 's' : '' }}</span>
                            </div>
                            @if($tender->responses->where('status', 'shortlisted')->count() > 0)
                                <div style="display: flex; align-items: center; gap: 0.35rem; background: #fef3c7; padding: 0.5rem 0.75rem; border-radius: 10px;">
                                    <i class="fas fa-star" style="color: #f59e0b;"></i>
                                    <span style="font-size: 0.75rem; color: #d97706; font-weight: 500;">
                                        {{ $tender->responses->where('status', 'shortlisted')->count() }} présélection{{ $tender->responses->where('status', 'shortlisted')->count() > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap;">
                        @if($tender->status === 'draft')
                            <a href="{{ route('client.tenders.create') }}?continue={{ $tender->id }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #f1f5f9; color: #475569; padding: 0.75rem 1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none;">
                                <i class="fas fa-edit"></i>
                                Continuer
                            </a>
                        @else
                            <a href="{{ route('client.tenders.show', $tender) }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.75rem 1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; text-decoration: none;">
                                <i class="fas fa-eye"></i>
                                Voir détails
                            </a>
                        @endif
                        
                        {{-- Bouton Annuler - seulement pour published ou in_progress --}}
                        @if(in_array($tender->status, ['published', 'in_progress', 'draft']))
                            <button type="button" onclick="confirmCancel({{ $tender->id }}, {{ Js::from($tender->title) }})" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #fee2e2; color: #dc2626; padding: 0.75rem 1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer;">
                                <i class="fas fa-times"></i>
                                Annuler
                            </button>
                        @endif
                        
                        {{-- Bouton Supprimer --}}
                        <button type="button" onclick="confirmDelete({{ $tender->id }}, {{ Js::from($tender->title) }})" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #7f1d1d; color: white; padding: 0.75rem 1rem; border-radius: 10px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer;">
                            <i class="fas fa-trash"></i>
                            Supprimer
                        </button>
                    </div>

                    {{-- Badge expiré --}}
                    @if($tender->is_expired)
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-15deg); background: rgba(0,0,0,0.8); color: white; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 700; font-size: 0.875rem; text-transform: uppercase;">
                            Expiré
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($tenders->hasPages())
            <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; justify-content: center;">
                    @if($tenders->onFirstPage())
                        <span style="padding: 0.5rem 0.75rem; background: #f1f5f9; color: #94a3b8; border-radius: 8px; font-size: 0.875rem;">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $tenders->previousPageUrl() }}" style="padding: 0.5rem 0.75rem; background: white; color: #667eea; border-radius: 8px; font-size: 0.875rem; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    <span style="padding: 0.5rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; font-size: 0.875rem; font-weight: 600;">
                        {{ $tenders->currentPage() }} / {{ $tenders->lastPage() }}
                    </span>

                    @if($tenders->hasMorePages())
                        <a href="{{ $tenders->nextPageUrl() }}" style="padding: 0.5rem 0.75rem; background: white; color: #667eea; border-radius: 8px; font-size: 0.875rem; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span style="padding: 0.5rem 0.75rem; background: #f1f5f9; color: #94a3b8; border-radius: 8px; font-size: 0.875rem;">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    @else
        {{-- État vide --}}
        <div style="background: white; border-radius: 20px; padding: 3rem 1.5rem; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i class="fas fa-bullhorn" style="font-size: 2rem; color: white;"></i>
            </div>
            <h2 style="font-size: 1.25rem; color: #1a1a2e; margin: 0 0 0.5rem 0;">Aucun appel d'offre</h2>
            <p style="color: #64748b; font-size: 0.875rem; margin: 0 0 1.5rem 0; line-height: 1.5;">
                Créez votre premier appel d'offre pour recevoir des propositions de prestataires qualifiés
            </p>
            <a href="{{ route('client.tenders.create') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.875rem 1.5rem; border-radius: 12px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-plus"></i>
                Créer un appel d'offre
            </a>
        </div>
    @endif
</div>

{{-- Modal de confirmation d'annulation --}}
<div id="cancelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 20px; padding: 1.5rem; max-width: 400px; width: 100%;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; color: #dc2626;"></i>
            </div>
            <h3 style="font-size: 1.125rem; color: #1a1a2e; margin: 0 0 0.5rem 0;">Annuler cet appel d'offre ?</h3>
            <p style="color: #64748b; font-size: 0.875rem; margin: 0;" id="cancelModalText">
                Cette action est irréversible.
            </p>
        </div>
        <form id="cancelForm" method="POST">
            @csrf
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" onclick="closeCancelModal()" style="flex: 1; padding: 0.875rem; background: #f1f5f9; color: #475569; border: none; border-radius: 12px; font-weight: 600; cursor: pointer;">
                    Non, garder
                </button>
                <button type="submit" style="flex: 1; padding: 0.875rem; background: #dc2626; color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer;">
                    Oui, annuler
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de confirmation de suppression --}}
<div id="deleteModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 20px; padding: 1.5rem; max-width: 400px; width: 100%;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 60px; height: 60px; background: #7f1d1d; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-trash" style="font-size: 1.5rem; color: white;"></i>
            </div>
            <h3 style="font-size: 1.125rem; color: #1a1a2e; margin: 0 0 0.5rem 0;">Supprimer définitivement ?</h3>
            <p style="color: #64748b; font-size: 0.875rem; margin: 0;" id="deleteModalText">
                Cette action est irréversible et supprimera toutes les propositions associées.
            </p>
        </div>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" onclick="closeDeleteModal()" style="flex: 1; padding: 0.875rem; background: #f1f5f9; color: #475569; border: none; border-radius: 12px; font-weight: 600; cursor: pointer;">
                    Non, garder
                </button>
                <button type="submit" style="flex: 1; padding: 0.875rem; background: #7f1d1d; color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer;">
                    Oui, supprimer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCancel(tenderId, tenderTitle) {
    document.getElementById('cancelModal').style.display = 'flex';
    document.getElementById('cancelModalText').textContent = 'Voulez-vous vraiment annuler "' + tenderTitle + '" ?';
    document.getElementById('cancelForm').action = '/client/tenders/' + tenderId + '/cancel';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

function confirmDelete(tenderId, tenderTitle) {
    document.getElementById('deleteModal').style.display = 'flex';
    document.getElementById('deleteModalText').textContent = 'Supprimer "' + tenderTitle + '" définitivement ? Cette action est irréversible.';
    document.getElementById('deleteForm').action = '/client/tenders/' + tenderId;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection
