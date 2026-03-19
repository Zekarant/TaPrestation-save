@extends('layouts.admin-modern')

@section('title', 'Gestion des Commissions')
@section('page-title', 'Gestion des Commissions')

@section('content')
<style>
    .commission-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .commission-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .commission-card-header .icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .commission-card-body {
        padding: 24px;
    }
    .commission-input-group {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f1f5f9;
        border-radius: 10px;
        padding: 4px;
    }
    .commission-input-group input {
        width: 80px;
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        border: none;
        background: white;
        border-radius: 8px;
        padding: 10px;
        color: #1e293b;
    }
    .commission-input-group input:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    .commission-input-group .suffix {
        font-size: 16px;
        font-weight: 600;
        color: #64748b;
        padding-right: 12px;
    }
    .commission-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .commission-row:last-child {
        border-bottom: none;
    }
    .commission-row .label {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .commission-row .label .icon-sm {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .commission-row .label .text {
        font-weight: 500;
        color: #334155;
    }
    .commission-row .label .desc {
        font-size: 12px;
        color: #94a3b8;
    }
    .toggle-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .toggle-table th {
        background: #f8fafc;
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }
    .toggle-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .toggle-table tr:hover td {
        background: #f8fafc;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }
    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    .toggle-btn {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .toggle-btn.activate {
        background: #dcfce7;
        color: #166534;
    }
    .toggle-btn.activate:hover {
        background: #bbf7d0;
    }
    .toggle-btn.deactivate {
        background: #f1f5f9;
        color: #475569;
    }
    .toggle-btn.deactivate:hover {
        background: #e2e8f0;
    }
    .info-banner {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 24px;
    }
    .info-banner .icon {
        color: #3b82f6;
        font-size: 20px;
        margin-top: 2px;
    }
    .info-banner .content h4 {
        font-weight: 600;
        color: #1e40af;
        margin-bottom: 4px;
    }
    .info-banner .content p {
        color: #3b82f6;
        font-size: 14px;
        line-height: 1.5;
    }
    .save-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 14px 32px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
    }
    .save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
    }
    .section-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin-bottom: 16px;
    }
</style>

{{-- Header --}}
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">💰 Gestion des Commissions</h1>
    <p class="text-gray-600">Configurez les taux de commission prélevés sur chaque type de transaction</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl flex items-center gap-3">
        <i class="fas fa-check-circle text-green-500 text-xl"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl">
        <div class="font-medium mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Erreurs:</div>
        <ul class="list-disc pl-5 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Info Banner --}}
<div class="info-banner">
    <i class="fas fa-info-circle icon"></i>
    <div class="content">
        <h4>🔄 Mise à jour en temps réel</h4>
        <p>
            Les modifications effectuées ici sont <strong>appliquées immédiatement</strong> sur toutes les nouvelles transactions.<br>
            <strong>Commission prestataire</strong> = % déduit des revenus du prestataire (ex: 10% → le prestataire reçoit 90%).<br>
            <strong>Frais client</strong> = % ajouté au montant payé par le client (ex: 5% → le client paie 105% du prix).
        </p>
    </div>
</div>

<form action="{{ route('admin.settings.commissions.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        {{-- Commission Prestataire --}}
        <div class="commission-card">
            <div class="commission-card-header">
                <div class="icon" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #b45309;">
                    <i class="fas fa-store"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Commission Prestataire</h3>
                    <p class="text-sm text-gray-500">Prélevée sur les revenus du prestataire</p>
                </div>
            </div>
            <div class="commission-card-body">
                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #dbeafe; color: #2563eb;"><i class="fas fa-briefcase"></i></div>
                        <div>
                            <div class="text">Services / Réservations</div>
                            <div class="desc">Prestations de service</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_services" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_services'] ?? 10 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>

                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #d1fae5; color: #059669;"><i class="fas fa-tools"></i></div>
                        <div>
                            <div class="text">Location Équipement</div>
                            <div class="desc">Matériel à louer</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_rentals" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_rentals'] ?? 8 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>

                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-bolt"></i></div>
                        <div>
                            <div class="text">Vente Flash</div>
                            <div class="desc">Ventes urgentes / occasions</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_urgent_sales" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_urgent_sales'] ?? $paymentSettings['commission_services'] ?? 10 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>

                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #ffedd5; color: #ea580c;"><i class="fas fa-utensils"></i></div>
                        <div>
                            <div class="text">Food / Restauration</div>
                            <div class="desc">Commandes de nourriture</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_food" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_food'] ?? 15 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Frais Client --}}
        <div class="commission-card">
            <div class="commission-card-header">
                <div class="icon" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #4338ca;">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Frais Client</h3>
                    <p class="text-sm text-gray-500">Ajoutés au montant payé par le client</p>
                </div>
            </div>
            <div class="commission-card-body">
                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #dbeafe; color: #2563eb;"><i class="fas fa-briefcase"></i></div>
                        <div>
                            <div class="text">Services / Réservations</div>
                            <div class="desc">Prestations de service</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_client_services" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_client_services'] ?? 0 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>

                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #d1fae5; color: #059669;"><i class="fas fa-tools"></i></div>
                        <div>
                            <div class="text">Location Équipement</div>
                            <div class="desc">Matériel à louer</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_client_rentals" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_client_rentals'] ?? 0 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>

                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-bolt"></i></div>
                        <div>
                            <div class="text">Vente Flash</div>
                            <div class="desc">Ventes urgentes / occasions</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_client_urgent_sales" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_client_urgent_sales'] ?? 0 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>

                <div class="commission-row">
                    <div class="label">
                        <div class="icon-sm" style="background: #ffedd5; color: #ea580c;"><i class="fas fa-utensils"></i></div>
                        <div>
                            <div class="text">Food / Restauration</div>
                            <div class="desc">Commandes de nourriture</div>
                        </div>
                    </div>
                    <div class="commission-input-group">
                        <input type="number" name="commission_client_food" step="0.01" min="0" max="100"
                            value="{{ $paymentSettings['commission_client_food'] ?? 0 }}">
                        <span class="suffix">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bouton Enregistrer --}}
    <div class="flex justify-end mb-10">
        <button type="submit" class="save-btn">
            <i class="fas fa-save"></i>
            Enregistrer les modifications
        </button>
    </div>
</form>

{{-- Exemptions individuelles --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Exemptions Prestataires --}}
    <div class="commission-card">
        <div class="commission-card-header">
            <div class="icon" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #b45309;">
                <i class="fas fa-user-slash"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-lg">Exemptions Prestataires</h3>
                <p class="text-sm text-gray-500">Désactiver la commission pour certains prestataires</p>
            </div>
        </div>
        <div class="commission-card-body p-0 overflow-x-auto">
            <table class="toggle-table">
                <thead>
                    <tr>
                        <th>Prestataire</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestataires ?? [] as $p)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $p->company_name ?? ($p->user->name ?? 'Prestataire #'.$p->id) }}</div>
                                <div class="text-xs text-gray-500">{{ $p->user->email ?? '' }}</div>
                            </td>
                            <td>
                                @if($p->commission_prestataire_disabled ?? false)
                                    <span class="status-badge inactive"><i class="fas fa-times-circle"></i> Désactivée</span>
                                @else
                                    <span class="status-badge active"><i class="fas fa-check-circle"></i> Active</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.settings.commissions.prestataires.toggle', $p) }}" class="inline">
                                    @csrf
                                    @if($p->commission_prestataire_disabled ?? false)
                                        <button type="submit" class="toggle-btn activate">
                                            <i class="fas fa-check mr-1"></i> Activer
                                        </button>
                                    @else
                                        <button type="submit" class="toggle-btn deactivate">
                                            <i class="fas fa-ban mr-1"></i> Désactiver
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-500 py-8">
                                <i class="fas fa-users text-4xl text-gray-300 mb-3 block"></i>
                                Aucun prestataire trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($prestataires ?? collect(), 'links'))
                <div class="p-4 border-t border-gray-100">{{ $prestataires->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Exemptions Clients --}}
    <div class="commission-card">
        <div class="commission-card-header">
            <div class="icon" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #4338ca;">
                <i class="fas fa-user-times"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-lg">Exemptions Clients</h3>
                <p class="text-sm text-gray-500">Désactiver les frais client pour certains utilisateurs</p>
            </div>
        </div>
        <div class="commission-card-body p-0 overflow-x-auto">
            <table class="toggle-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients ?? [] as $c)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $c->name ?? 'Client #'.$c->id }}</div>
                                <div class="text-xs text-gray-500">{{ $c->email ?? '' }}</div>
                            </td>
                            <td>
                                @if($c->commission_client_disabled ?? false)
                                    <span class="status-badge inactive"><i class="fas fa-times-circle"></i> Désactivés</span>
                                @else
                                    <span class="status-badge active"><i class="fas fa-check-circle"></i> Actifs</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.settings.commissions.clients.toggle', $c) }}" class="inline">
                                    @csrf
                                    @if($c->commission_client_disabled ?? false)
                                        <button type="submit" class="toggle-btn activate">
                                            <i class="fas fa-check mr-1"></i> Activer
                                        </button>
                                    @else
                                        <button type="submit" class="toggle-btn deactivate">
                                            <i class="fas fa-ban mr-1"></i> Désactiver
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-500 py-8">
                                <i class="fas fa-users text-4xl text-gray-300 mb-3 block"></i>
                                Aucun client trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($clients ?? collect(), 'links'))
                <div class="p-4 border-t border-gray-100">{{ $clients->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
