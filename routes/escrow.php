<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\EscrowController;

/*
|--------------------------------------------------------------------------
| Routes pour le système Escrow (Paiement Sécurisé)
|--------------------------------------------------------------------------
|
| Ces routes gèrent :
| - Transactions escrow côté client (voir, confirmer, noter, litiges)
| - Transactions escrow côté prestataire (voir, confirmer, noter, expédier)
| - Paramètres de mode de paiement prestataire
| - API transporteurs et points relais
|
*/

// Routes client authentifié (accessible aux clients ET prestataires en mode client)
Route::middleware(['auth', 'role:client,prestataire', 'profile.complete'])
    ->prefix('client/escrow')
    ->name('client.escrow.')
    ->group(function () {
        
        // Liste des transactions escrow
        Route::get('/', [EscrowController::class, 'clientIndex'])->name('index');
        
        // Détails d'une transaction
        Route::get('/{escrow}', [EscrowController::class, 'clientShow'])->name('show');
        
        // Confirmer la réception/satisfaction (libère le paiement)
        Route::post('/{escrow}/confirm', [EscrowController::class, 'clientConfirm'])->middleware('throttle:5,1')->name('confirm');

        // Ouvrir un litige
        Route::post('/{escrow}/dispute', [EscrowController::class, 'clientDispute'])->middleware('throttle:5,1')->name('dispute');

        // Noter le prestataire
        Route::post('/{escrow}/rate', [EscrowController::class, 'clientRate'])->middleware('throttle:5,1')->name('rate');

        // === VENTES URGENTES ===
        // Confirmer réception produit conforme
        Route::post('/{escrow}/confirm-urgent-sale', [EscrowController::class, 'confirmUrgentSale'])->middleware('throttle:5,1')->name('confirm-urgent-sale');

        // Signaler produit non conforme (remboursement partiel)
        Route::post('/{escrow}/non-conformity', [EscrowController::class, 'reportNonConformity'])->middleware('throttle:5,1')->name('non-conformity');
    });

// Routes prestataire authentifié
Route::middleware(['auth', 'role:prestataire', 'profile.complete', 'subscription'])
    ->prefix('prestataire/escrow')
    ->name('prestataire.escrow.')
    ->group(function () {
        
        // Liste des transactions escrow
        Route::get('/', [EscrowController::class, 'prestataireIndex'])->name('index');
        
        // Détails d'une transaction
        Route::get('/{escrow}', [EscrowController::class, 'prestataireShow'])->name('show');
        
        // Confirmer le retour d'équipement (libère la caution)
        Route::post('/{escrow}/confirm', [EscrowController::class, 'prestataireConfirm'])->middleware('throttle:5,1')->name('confirm');

        // Noter le client
        Route::post('/{escrow}/rate', [EscrowController::class, 'prestataireRate'])->middleware('throttle:5,1')->name('rate');

        // Créer une expédition (pour vente urgente)
        Route::post('/{escrow}/shipment', [EscrowController::class, 'createShipment'])->middleware('throttle:5,1')->name('shipment');

        // Marquer comme livré (pour vente urgente) -> démarre le délai d'auto-libération
        Route::post('/{escrow}/mark-delivered', [EscrowController::class, 'markUrgentSaleDelivered'])->middleware('throttle:5,1')->name('mark-delivered');

        // Vente urgente: retour reçu (accord) -> remboursement total
        Route::post('/{escrow}/return-received', [EscrowController::class, 'confirmUrgentSaleReturnReceived'])->middleware('throttle:5,1')->name('return-received');

        // === ÉQUIPEMENT - RETOUR ===
        // Valider le retour d'équipement avec inspection
        Route::post('/{escrow}/return-equipment', [EscrowController::class, 'returnEquipment'])->middleware('throttle:5,1')->name('return-equipment');

        // === DEMANDE DE VERSEMENT ===
        // Demander la libération des fonds (quand client confirmé ou délai 48h dépassé)
        Route::post('/{escrow}/request-release', [EscrowController::class, 'requestRelease'])->middleware('throttle:5,1')->name('request-release');
    });

// Paramètres mode de paiement (prestataire)
Route::middleware(['auth', 'role:prestataire', 'profile.complete', 'subscription'])
    ->prefix('prestataire/settings')
    ->name('prestataire.settings.')
    ->group(function () {
        
        // Page de choix du mode de paiement
        Route::get('/payment-mode', [EscrowController::class, 'paymentModeSettings'])->name('payment-mode');
        
        // Mettre à jour le mode de paiement
        Route::post('/payment-mode', [EscrowController::class, 'updatePaymentMode'])->name('payment-mode.update');
    });

// API pour transporteurs et points relais
Route::middleware(['auth'])
    ->prefix('api/shipping')
    ->name('api.shipping.')
    ->group(function () {
        
        // Liste des transporteurs disponibles
        Route::get('/carriers', [EscrowController::class, 'getCarriers'])->name('carriers');
        
        // Recherche de points relais
        Route::post('/relay-points', [EscrowController::class, 'searchRelayPoints'])->name('relay-points');
    });
