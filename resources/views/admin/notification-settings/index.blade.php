@extends('layouts.admin-modern')

@section('title', 'Paramètres de Notifications')
@section('page-title', 'Paramètres de Notifications')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-sliders-h me-2"></i>Paramètres de Notifications
        </h1>
        <p class="page-subtitle">Configurez les notifications système pour tous les utilisateurs</p>
    </div>

    <div class="row">
        <!-- Global Settings -->
        <div class="col-lg-8">
            <div class="card-base mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-globe me-2"></i>Paramètres Globaux
                    </h5>
                </div>
                <div class="p-4">
                    <form>
                        <!-- Email Notifications -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-envelope me-2"></i>Notifications par Email
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailNewBooking" checked>
                                        <label class="form-check-label" for="emailNewBooking">
                                            Nouvelle réservation
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailNewMessage" checked>
                                        <label class="form-check-label" for="emailNewMessage">
                                            Nouveau message
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailPayment" checked>
                                        <label class="form-check-label" for="emailPayment">
                                            Confirmation de paiement
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailDelivery" checked>
                                        <label class="form-check-label" for="emailDelivery">
                                            Mise à jour de livraison
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailMarketing">
                                        <label class="form-check-label" for="emailMarketing">
                                            Emails marketing (newsletter)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- SMS Notifications -->
                        <div class="mb-4">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-sms me-2"></i>Notifications SMS
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="smsBookingConfirm" checked>
                                        <label class="form-check-label" for="smsBookingConfirm">
                                            Confirmation de réservation
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="smsDeliveryAlert">
                                        <label class="form-check-label" for="smsDeliveryAlert">
                                            Alerte de livraison
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="smsSecurityAlert" checked>
                                        <label class="form-check-label" for="smsSecurityAlert">
                                            Alertes de sécurité
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Push Notifications -->
                        <div class="mb-4">
                            <h6 class="text-warning mb-3">
                                <i class="fas fa-bell me-2"></i>Notifications Push
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pushNewBooking" checked>
                                        <label class="form-check-label" for="pushNewBooking">
                                            Nouvelle réservation
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pushNewMessage" checked>
                                        <label class="form-check-label" for="pushNewMessage">
                                            Nouveau message
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pushAuction">
                                        <label class="form-check-label" for="pushAuction">
                                            Fin d'enchère
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pushPromotion">
                                        <label class="form-check-label" for="pushPromotion">
                                            Promotions et offres
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Admin Alerts -->
                        <div class="mb-4">
                            <h6 class="text-danger mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>Alertes Administrateur
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="adminNewReport" checked>
                                        <label class="form-check-label" for="adminNewReport">
                                            Nouveau signalement
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="adminHighValueTransaction" checked>
                                        <label class="form-check-label" for="adminHighValueTransaction">
                                            Transaction de valeur élevée (> 1000€)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="adminNewPrestataire" checked>
                                        <label class="form-check-label" for="adminNewPrestataire">
                                            Nouvelle inscription prestataire
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="adminSystemAlert" checked>
                                        <label class="form-check-label" for="adminSystemAlert">
                                            Alertes système
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                            </button>
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div class="col-lg-4">
            <!-- Stats -->
            <div class="card-base mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statistiques
                    </h5>
                </div>
                <div class="p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Emails envoyés (30j)</span>
                            <strong>{{ number_format($stats['emails_sent'] ?? 12456) }}</strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 78%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>SMS envoyés (30j)</span>
                            <strong>{{ number_format($stats['sms_sent'] ?? 2345) }}</strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 45%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Push envoyés (30j)</span>
                            <strong>{{ number_format($stats['push_sent'] ?? 8765) }}</strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: 62%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Taux d'ouverture</span>
                            <strong>{{ $stats['open_rate'] ?? '68.5' }}%</strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 68.5%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates -->
            <div class="card-base mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Templates
                    </h5>
                </div>
                <div class="p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Email de bienvenue</span>
                            <a href="#" class="btn btn-sm btn-outline-primary">Modifier</a>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Confirmation réservation</span>
                            <a href="#" class="btn btn-sm btn-outline-primary">Modifier</a>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Notification paiement</span>
                            <a href="#" class="btn btn-sm btn-outline-primary">Modifier</a>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2">
                            <span>Rappel de livraison</span>
                            <a href="#" class="btn btn-sm btn-outline-primary">Modifier</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Help -->
            <div class="card-base" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="p-4 text-white">
                    <h5 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Conseil</h5>
                    <p class="mb-0 opacity-90" style="font-size: 0.9rem;">
                        Personnalisez les notifications pour améliorer l'engagement utilisateur tout en évitant le spam. Un bon équilibre augmente la satisfaction client.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
