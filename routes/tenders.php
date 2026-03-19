<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\TenderController as ClientTenderController;
use App\Http\Controllers\Client\BecomeProviderController;
use App\Http\Controllers\Prestataire\TenderController as PrestataireTenderController;
use App\Http\Controllers\Prestataire\QuoteController;

/*
|--------------------------------------------------------------------------
| Routes Appels d'Offres (Tender System) + Devenir Prestataire + Devis
|--------------------------------------------------------------------------
|
| Ce fichier contient toutes les routes pour le système d'appels d'offres
| permettant aux clients de poster des demandes et aux prestataires de
| soumettre des propositions.
|
| Inclut également les routes pour la conversion client -> prestataire
| et le système de devis (quotes).
|
*/

// ============================================================================
// ROUTES DEVENIR PRESTATAIRE - Conversion client -> prestataire
// ============================================================================

Route::middleware(['auth', 'role:client'])
    ->prefix('client/become-provider')
    ->name('client.become-provider.')
    ->group(function () {
        
        // Page d'avantages (pré-inscription)
        Route::get('/benefits', [BecomeProviderController::class, 'benefits'])
            ->name('benefits');
        
        // Page de confirmation rapide
        Route::get('/', [BecomeProviderController::class, 'index'])
            ->name('index');
        
        // Conversion rapide (nouveau flux simplifié)
        Route::post('/quick', [BecomeProviderController::class, 'quickConvert'])
            ->name('quick');
        
        // Sauvegarder une étape (legacy)
        Route::post('/step/{step}', [BecomeProviderController::class, 'storeStep'])
            ->name('step')
            ->where('step', '[1-6]');
        
        // Finaliser la conversion (legacy)
        Route::post('/finalize', [BecomeProviderController::class, 'finalize'])
            ->name('finalize');

        // Réactiver un profil prestataire existant (si l'utilisateur en a un mais inactif)
        Route::post('/reactivate', [BecomeProviderController::class, 'reactivate'])
            ->name('reactivate');
    });

// ============================================================================
// ROUTES CLIENT - Création et gestion des appels d'offres
// ============================================================================

// Permettre aux clients ET prestataires (qui ont un profil client) d'accéder
Route::middleware(['auth', 'role:client|administrateur'])
    ->prefix('client/tenders')
    ->name('client.tenders.')
    ->group(function () {
        
        // Liste des appels d'offres du client
        Route::get('/', [ClientTenderController::class, 'index'])->name('index');
        
        // Création - Formulaire multi-étapes
        Route::get('/create', [ClientTenderController::class, 'create'])->name('create');
        
        // Création rapide (formulaire simplifié)
        Route::post('/quick-create', [ClientTenderController::class, 'quickCreate'])->name('quick-create');
        
        // Sauvegarde d'une étape
        Route::post('/step/{step}', [ClientTenderController::class, 'storeStep'])
            ->name('store-step')
            ->where('step', '[1-7]');
        
        // Afficher un appel d'offre
        Route::get('/{tender}', [ClientTenderController::class, 'show'])
            ->name('show')
            ->where('tender', '[0-9]+');
        
        // Publier l'appel d'offre
        Route::post('/{tender}/publish', [ClientTenderController::class, 'publish'])
            ->name('publish')
            ->where('tender', '[0-9]+');
        
        // Annuler un appel d'offre
        Route::post('/{tender}/cancel', [ClientTenderController::class, 'cancel'])
            ->name('cancel')
            ->where('tender', '[0-9]+');
        
        // Supprimer un appel d'offre
        Route::delete('/{tender}', [ClientTenderController::class, 'destroy'])
            ->name('destroy')
            ->where('tender', '[0-9]+');
        
        // Supprimer un média
        Route::delete('/{tender}/media', [ClientTenderController::class, 'deleteMedia'])
            ->name('delete-media')
            ->where('tender', '[0-9]+');
        
        // Répondre à une proposition de prestataire
        Route::post('/{tender}/responses/{response}', [ClientTenderController::class, 'respondToProposal'])
            ->name('respond-to-proposal')
            ->where(['tender' => '[0-9]+', 'response' => '[0-9]+']);
    });


// ============================================================================
// ROUTES PRESTATAIRE - Consultation et réponse aux appels d'offres
// ============================================================================

Route::middleware(['auth', 'role:prestataire'])
    ->prefix('prestataire/tenders')
    ->name('prestataire.tenders.')
    ->group(function () {
        
        // Liste des appels d'offres disponibles (matching)
        Route::get('/', [PrestataireTenderController::class, 'index'])->name('index');
        
        // Mes propositions envoyées
        Route::get('/my-responses', [PrestataireTenderController::class, 'myResponses'])
            ->name('my-responses');
        
        // Mes invitations
        Route::get('/invitations', [PrestataireTenderController::class, 'invitations'])
            ->name('invitations');
        
        // Marquer une invitation comme lue (AJAX)
        Route::post('/invitations/{invitation}/read', [PrestataireTenderController::class, 'markInvitationRead'])
            ->name('invitation-read')
            ->where('invitation', '[0-9]+');
        
        // Voir un appel d'offre
        Route::get('/{tender}', [PrestataireTenderController::class, 'show'])
            ->name('show')
            ->where('tender', '[0-9]+');
        
        // Formulaire de proposition
        Route::get('/{tender}/respond', [PrestataireTenderController::class, 'createResponse'])
            ->name('respond')
            ->where('tender', '[0-9]+');
        
        // Soumettre une proposition
        Route::post('/{tender}/respond', [PrestataireTenderController::class, 'storeResponse'])
            ->name('store-response')
            ->where('tender', '[0-9]+');
        
        // Modifier une proposition
        Route::get('/{tender}/responses/{response}/edit', [PrestataireTenderController::class, 'editResponse'])
            ->name('edit-response')
            ->where(['tender' => '[0-9]+', 'response' => '[0-9]+']);
        
        // Mettre à jour une proposition
        Route::put('/{tender}/responses/{response}', [PrestataireTenderController::class, 'updateResponse'])
            ->name('update-response')
            ->where(['tender' => '[0-9]+', 'response' => '[0-9]+']);
        
        // Retirer une proposition
        Route::delete('/responses/{response}/withdraw', [PrestataireTenderController::class, 'withdrawResponse'])
            ->name('withdraw-response')
            ->where('response', '[0-9]+');
    });


// ============================================================================
// ROUTES DEVIS (QUOTES) - Création et envoi de devis par les prestataires
// ============================================================================

Route::middleware(['auth', 'role:prestataire'])
    ->prefix('prestataire/quotes')
    ->name('prestataire.quotes.')
    ->group(function () {
        
        // Liste des devis
        Route::get('/', [QuoteController::class, 'index'])->name('index');
        
        // Créer un devis
        Route::get('/create', [QuoteController::class, 'create'])->name('create');
        Route::post('/', [QuoteController::class, 'store'])->name('store');
        
        // Recherche de clients (AJAX)
        Route::get('/search-clients', [QuoteController::class, 'searchClients'])->name('search-clients');
        
        // Voir un devis
        Route::get('/{quote}', [QuoteController::class, 'show'])
            ->name('show')
            ->where('quote', '[0-9]+');
        
        // Modifier un devis
        Route::get('/{quote}/edit', [QuoteController::class, 'edit'])
            ->name('edit')
            ->where('quote', '[0-9]+');
        Route::put('/{quote}', [QuoteController::class, 'update'])
            ->name('update')
            ->where('quote', '[0-9]+');
        
        // Envoyer un devis
        Route::post('/{quote}/send', [QuoteController::class, 'send'])
            ->name('send')
            ->where('quote', '[0-9]+');
        
        // Dupliquer un devis
        Route::post('/{quote}/duplicate', [QuoteController::class, 'duplicate'])
            ->name('duplicate')
            ->where('quote', '[0-9]+');
        
        // Annuler un devis
        Route::post('/{quote}/cancel', [QuoteController::class, 'cancel'])
            ->name('cancel')
            ->where('quote', '[0-9]+');
        
        // Supprimer un devis
        Route::delete('/{quote}', [QuoteController::class, 'destroy'])
            ->name('destroy')
            ->where('quote', '[0-9]+');
        
        // Télécharger en PDF
        Route::get('/{quote}/pdf', [QuoteController::class, 'downloadPdf'])
            ->name('pdf')
            ->where('quote', '[0-9]+');
    });
