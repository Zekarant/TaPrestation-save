<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:administrateur', 'throttle:60,1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Route de synchronisation Urgent Sale / Inventory (one-shot)
        Route::post('/sync-urgent-sale-inventory', [App\Http\Controllers\SyncUrgentSaleInventoryController::class, 'sync'])->name('sync-urgent-sale-inventory');

        // Vue détaillée généralités du site
        Route::get('/overview/general', [\App\Http\Controllers\Admin\AdminOverviewController::class, 'general'])->name('overview.general');

        // Vérifications prestataires
        Route::prefix('verifications')->name('verifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\VerificationController::class, 'index'])->name('index');
            Route::get('/{verificationRequest}', [App\Http\Controllers\Admin\VerificationController::class, 'show'])->name('show');
            Route::patch('/{verificationRequest}/approve', [App\Http\Controllers\Admin\VerificationController::class, 'approve'])->name('approve');
            Route::patch('/{verificationRequest}/reject', [App\Http\Controllers\Admin\VerificationController::class, 'reject'])->name('reject');
            Route::get('/{verificationRequest}/document/{documentIndex}', [App\Http\Controllers\Admin\VerificationController::class, 'downloadDocument'])->name('download-document');
            Route::post('/run-automatic', [App\Http\Controllers\Admin\VerificationController::class, 'runAutomaticVerification'])->name('run-automatic');
            Route::patch('/{prestataire}/revoke', [App\Http\Controllers\Admin\VerificationController::class, 'revokeVerification'])->name('revoke');
        });

        // Équipements (admin)
        Route::resource('equipments', App\Http\Controllers\Admin\EquipmentController::class)
            ->except(['create', 'store', 'destroy'])
            ->names('equipments');

        // Ventes urgentes (annonces)
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UrgentSaleController::class, 'index'])->name('index');
            Route::get('/dashboard', [App\Http\Controllers\Admin\UrgentSaleController::class, 'dashboard'])->name('dashboard');
            Route::get('/{urgentSale}', [App\Http\Controllers\Admin\UrgentSaleController::class, 'show'])->name('show');
            Route::patch('/{urgentSale}/suspend', [App\Http\Controllers\Admin\UrgentSaleController::class, 'suspend'])->name('suspend');
            Route::patch('/{urgentSale}/reactivate', [App\Http\Controllers\Admin\UrgentSaleController::class, 'reactivate'])->name('reactivate');
            Route::delete('/{urgentSale}', [App\Http\Controllers\Admin\UrgentSaleController::class, 'destroy'])->name('destroy');
        });

        // Services (vue admin)
        Route::resource('services', App\Http\Controllers\Admin\ServiceController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('services');

        // Avis
        Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('reviews');

        // Réservations
        Route::resource('bookings', App\Http\Controllers\Admin\BookingController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('bookings');

        // Notifications
        Route::resource('notifications', App\Http\Controllers\Admin\NotificationController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('notifications');

        // Messages
        Route::resource('messages', App\Http\Controllers\Admin\MessageController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('messages');

        // ============================================================================
        // 15 NOUVELLES FONCTIONNALITÉS INTÉGRÉES POUR ADMIN
        // ============================================================================

        // 1. Paiements - Payment Monitoring & Analytics
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [App\Http\Controllers\Payment\PaymentController::class, 'adminTransactionHistory'])->name('index');
            Route::get('/transactions', [App\Http\Controllers\Payment\PaymentController::class, 'adminTransactionHistory'])->name('transactions');
            Route::get('/analytics', [App\Http\Controllers\Payment\PaymentController::class, 'adminAnalytics'])->name('analytics');
            Route::post('/refund/{transaction}', [App\Http\Controllers\Payment\PaymentController::class, 'adminRefund'])->name('refund');
            Route::post('/refund-requests/{refund}/process', [App\Http\Controllers\Payment\PaymentController::class, 'adminProcessRefundRequest'])->name('refund-requests.process');
            Route::post('/food/{foodOrder}/capture', [App\Http\Controllers\Payment\PaymentController::class, 'adminFoodCapture'])->name('food.capture');
            Route::post('/food/{foodOrder}/cancel-authorization', [App\Http\Controllers\Payment\PaymentController::class, 'adminFoodCancelAuthorization'])->name('food.cancel-authorization');
            Route::post('/food/{foodOrder}/refund', [App\Http\Controllers\Payment\PaymentController::class, 'adminFoodRefund'])->name('food.refund');
            Route::post('/escrow/{escrowId}/release', [App\Http\Controllers\Payment\PaymentController::class, 'adminEscrowRelease'])->name('escrow.release');
            Route::post('/escrow/{escrowId}/refund', [App\Http\Controllers\Payment\PaymentController::class, 'adminEscrowRefund'])->name('escrow.refund');
            Route::post('/escrow/{escrowId}/return-equipment', [App\Http\Controllers\Payment\PaymentController::class, 'adminEscrowReturnEquipment'])->name('escrow.return-equipment');
            Route::post('/disputes/{disputeId}/resolve', [App\Http\Controllers\Payment\PaymentController::class, 'adminResolveEscrowDispute'])->name('disputes.resolve');
        });

        // 2. Abonnements - Subscription Management
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminSubscribers'])->name('index');
            Route::get('/plans', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminPlans'])->name('plans');
            Route::post('/plans', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminStorePlan'])->name('store-plan');
            Route::put('/plans/{plan}', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminUpdatePlan'])->name('update-plan');
            Route::delete('/plans/{plan}', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminDestroyPlan'])->name('destroy-plan');
            Route::get('/subscribers', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminSubscribers'])->name('subscribers');

            // Gestion manuelle des abonnements
            Route::post('/{subscription}/activate', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminActivate'])->name('activate');
            Route::post('/{subscription}/deactivate', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminDeactivate'])->name('deactivate');
            Route::post('/{subscription}/extend', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminExtend'])->name('extend');
            Route::post('/create-for-user/{user}', [App\Http\Controllers\Subscription\SubscriptionController::class, 'adminCreateForUser'])->name('create-for-user');
        });

        // 3. Enchères - Auction Moderation
        Route::prefix('auctions')->name('auctions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Auction\AuctionController::class, 'adminAllBids'])->name('index');
            Route::get('/all-bids', [App\Http\Controllers\Auction\AuctionController::class, 'adminAllBids'])->name('all-bids');
            Route::get('/disputes', [App\Http\Controllers\Auction\AuctionController::class, 'adminDisputes'])->name('disputes');
            Route::post('/disputes/{bid}/resolve', [App\Http\Controllers\Auction\AuctionController::class, 'resolveDispute'])->name('resolve-dispute');
            Route::get('/analytics', [App\Http\Controllers\Auction\AuctionController::class, 'adminAnalytics'])->name('analytics');
        });

        // 4. Livraison - Delivery Provider Management
        Route::prefix('delivery')->name('delivery.')->group(function () {
            Route::get('/', [App\Http\Controllers\Delivery\DeliveryController::class, 'adminAllOrders'])->name('index');
            Route::get('/providers', [App\Http\Controllers\Delivery\DeliveryController::class, 'adminProviders'])->name('providers');
            Route::post('/providers', [App\Http\Controllers\Delivery\DeliveryController::class, 'adminStoreProvider'])->name('store-provider');
            Route::put('/providers/{provider}', [App\Http\Controllers\Delivery\DeliveryController::class, 'adminUpdateProvider'])->name('update-provider');
            Route::delete('/providers/{provider}', [App\Http\Controllers\Delivery\DeliveryController::class, 'adminDestroyProvider'])->name('destroy-provider');
            Route::get('/all-orders', [App\Http\Controllers\Delivery\DeliveryController::class, 'adminAllOrders'])->name('all-orders');
        });

        // 4b. Gestion des Livreurs (Admin)
        Route::prefix('drivers')->name('drivers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminDriverController::class, 'index'])->name('index');
            Route::get('/{driver}', [\App\Http\Controllers\Admin\AdminDriverController::class, 'show'])->name('show');
            Route::post('/{driver}/approve', [\App\Http\Controllers\Admin\AdminDriverController::class, 'approve'])->name('approve');
            Route::post('/{driver}/suspend', [\App\Http\Controllers\Admin\AdminDriverController::class, 'suspend'])->name('suspend');
            Route::post('/{driver}/reactivate', [\App\Http\Controllers\Admin\AdminDriverController::class, 'reactivate'])->name('reactivate');
            Route::delete('/{driver}', [\App\Http\Controllers\Admin\AdminDriverController::class, 'destroy'])->name('destroy');
        });

        // 5. Carnet d'adresses - User Address Management (Admin)
        Route::prefix('address-book')->name('address-book.')->group(function () {
            Route::get('/', [App\Http\Controllers\Address\AddressBookController::class, 'adminAllAddresses'])->name('index');
            Route::get('/all-addresses', [App\Http\Controllers\Address\AddressBookController::class, 'adminAllAddresses'])->name('all-addresses');
            Route::get('/by-user/{user}', [App\Http\Controllers\Address\AddressBookController::class, 'adminUserAddresses'])->name('by-user');
        });

        // 6. Inventaire - System Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [App\Http\Controllers\Inventory\InventoryController::class, 'adminIndex'])->name('index');
            Route::get('/all-items', [App\Http\Controllers\Inventory\InventoryController::class, 'adminAllItems'])->name('all-items');
            Route::delete('/{item}', [App\Http\Controllers\Inventory\InventoryController::class, 'adminDestroy'])->name('destroy');
            Route::get('/analytics', [App\Http\Controllers\Inventory\InventoryController::class, 'adminAnalytics'])->name('analytics');
            Route::post('/export', [App\Http\Controllers\Inventory\InventoryController::class, 'adminExport'])->name('export');
            Route::post('/bulk-action', [App\Http\Controllers\Inventory\InventoryController::class, 'bulkAction'])->name('bulk-action');
        });

        // 7. Paramètres de Notifications - System Notifications Management
        Route::prefix('notification-settings')->name('notification-settings.')->group(function () {
            Route::get('/', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'adminSystem'])->name('index');
            Route::get('/system', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'adminSystem'])->name('system');
            Route::put('/system', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'adminUpdateSystem'])->name('update-system');
            Route::get('/user-preferences', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'adminUserPreferences'])->name('user-preferences');
            Route::post('/broadcast', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'broadcast'])->name('broadcast');
        });

        // ============================================================================
        // 50 NOUVELLES FONCTIONNALITÉS ADMIN - GESTION COMPLÈTE DU SITE
        // ============================================================================

        // 8. Paramètres généraux du site (AdminSettingsController)
        Route::prefix('settings')->name('settings.')->group(function () {
            // Paramètres généraux
            Route::get('/general', [App\Http\Controllers\Admin\AdminSettingsController::class, 'general'])->name('general');
            Route::put('/general', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateGeneral'])->name('general.update');

            // Commissions
            Route::get('/commissions', [App\Http\Controllers\Admin\AdminSettingsController::class, 'commissions'])->name('commissions');
            Route::put('/commissions', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateCommissions'])->name('commissions.update');
            Route::post('/commissions/prestataires/{prestataire}/toggle', [App\Http\Controllers\Admin\AdminSettingsController::class, 'togglePrestataireCommission'])->name('commissions.prestataires.toggle');
            Route::post('/commissions/clients/{user}/toggle', [App\Http\Controllers\Admin\AdminSettingsController::class, 'toggleClientCommission'])->name('commissions.clients.toggle');

            // Email
            Route::get('/email', [App\Http\Controllers\Admin\AdminSettingsController::class, 'email'])->name('email');
            Route::put('/email', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateEmail'])->name('email.update');
            Route::post('/email/test', [App\Http\Controllers\Admin\AdminSettingsController::class, 'testEmail'])->name('email.test');

            // Paiements
            Route::get('/payments', [App\Http\Controllers\Admin\AdminSettingsController::class, 'payments'])->name('payments');
            Route::put('/payments', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updatePayments'])->name('payments.update');

            // SEO
            Route::get('/seo', [App\Http\Controllers\Admin\AdminSettingsController::class, 'seo'])->name('seo');
            Route::put('/seo', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateSeo'])->name('seo.update');

            // Notifications système
            Route::get('/notifications', [App\Http\Controllers\Admin\AdminSettingsController::class, 'notifications'])->name('notifications');
            Route::put('/notifications', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateNotifications'])->name('notifications.update');

            // Catégories
            Route::get('/categories', [App\Http\Controllers\Admin\AdminSettingsController::class, 'categories'])->name('categories');
            Route::post('/categories', [App\Http\Controllers\Admin\AdminSettingsController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{id}', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/categories/{id}', [App\Http\Controllers\Admin\AdminSettingsController::class, 'deleteCategory'])->name('categories.destroy');

            // Localisation
            Route::get('/localization', [App\Http\Controllers\Admin\AdminSettingsController::class, 'localization'])->name('localization');
            Route::put('/localization', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateLocalization'])->name('localization.update');

            // Légal
            Route::get('/legal', [App\Http\Controllers\Admin\AdminSettingsController::class, 'legal'])->name('legal');
            Route::put('/legal', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateLegal'])->name('legal.update');

            // Abonnement prestataire
            Route::get('/subscription', [App\Http\Controllers\Admin\AdminSettingsController::class, 'subscription'])->name('subscription');
            Route::put('/subscription', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateSubscription'])->name('subscription.update');
            Route::post('/subscription/toggle', [App\Http\Controllers\Admin\AdminSettingsController::class, 'toggleSubscription'])->name('subscription.toggle');

            // Visibilité des fonctionnalités (paiements, abonnements, etc.)
            Route::get('/features', [App\Http\Controllers\Admin\AdminSettingsController::class, 'features'])->name('features');
            Route::put('/features', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateFeatures'])->name('features.update');

            // Import/Export
            Route::get('/export', [App\Http\Controllers\Admin\AdminSettingsController::class, 'exportSettings'])->name('export');
            Route::post('/import', [App\Http\Controllers\Admin\AdminSettingsController::class, 'importSettings'])->name('import');
        });

        // 9. Analytiques et statistiques (AdminAnalyticsController)
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'dashboard'])->name('index');
            Route::get('/dashboard', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'dashboard'])->name('dashboard');
            Route::get('/revenue', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'revenue'])->name('revenue');
            Route::get('/users', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'users'])->name('users');
            Route::get('/services', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'services'])->name('services');
            Route::get('/geographic', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'geographic'])->name('geographic');
            Route::get('/reviews', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'reviews'])->name('reviews');
            Route::get('/performance', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'performance'])->name('performance');
            Route::post('/export', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'export'])->name('export');
        });

        // 10. Gestion système (AdminSystemController)
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/status', [App\Http\Controllers\Admin\AdminSystemController::class, 'status'])->name('status');
            Route::get('/logs', [App\Http\Controllers\Admin\AdminSystemController::class, 'logs'])->name('logs');
            Route::post('/logs/clear', [App\Http\Controllers\Admin\AdminSystemController::class, 'clearLogs'])->name('logs.clear');
            Route::get('/cache', [App\Http\Controllers\Admin\AdminSystemController::class, 'cache'])->name('cache');
            Route::post('/cache/clear', [App\Http\Controllers\Admin\AdminSystemController::class, 'clearCache'])->name('cache.clear');
            Route::post('/cache/optimize', [App\Http\Controllers\Admin\AdminSystemController::class, 'optimizeCache'])->name('cache.optimize');
            Route::get('/maintenance', [App\Http\Controllers\Admin\AdminSystemController::class, 'maintenance'])->name('maintenance');
            Route::post('/maintenance/toggle', [App\Http\Controllers\Admin\AdminSystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');
            Route::get('/backups', [App\Http\Controllers\Admin\AdminSystemController::class, 'backups'])->name('backups');
            Route::post('/backups/create', [App\Http\Controllers\Admin\AdminSystemController::class, 'createBackup'])->name('backups.create');
            Route::get('/backups/{filename}/download', [App\Http\Controllers\Admin\AdminSystemController::class, 'downloadBackup'])
                ->where('filename', '[A-Za-z0-9._-]+')
                ->name('backups.download');
            Route::delete('/backups/{filename}', [App\Http\Controllers\Admin\AdminSystemController::class, 'deleteBackup'])
                ->where('filename', '[A-Za-z0-9._-]+')
                ->name('backups.destroy');
            Route::get('/tasks', [App\Http\Controllers\Admin\AdminSystemController::class, 'scheduledTasks'])->name('tasks');
            Route::post('/tasks/{task}/run', [App\Http\Controllers\Admin\AdminSystemController::class, 'runTask'])->name('tasks.run');
            Route::get('/queues', [App\Http\Controllers\Admin\AdminSystemController::class, 'queues'])->name('queues');
            Route::post('/queues/retry/{jobId}', [App\Http\Controllers\Admin\AdminSystemController::class, 'retryFailedJob'])->name('queues.retry');
            Route::delete('/queues/{jobId}', [App\Http\Controllers\Admin\AdminSystemController::class, 'deleteFailedJob'])->name('queues.delete');
            Route::post('/queues/clear-failed', [App\Http\Controllers\Admin\AdminSystemController::class, 'clearFailedJobs'])->name('queues.clear-failed');
        });

        // 11. Sécurité (AdminSecurityController)
        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminSecurityController::class, 'dashboard'])->name('index');
            Route::get('/dashboard', [App\Http\Controllers\Admin\AdminSecurityController::class, 'dashboard'])->name('dashboard');
            Route::get('/login-logs', [App\Http\Controllers\Admin\AdminSecurityController::class, 'loginLogs'])->name('login-logs');
            Route::get('/blocked-ips', [App\Http\Controllers\Admin\AdminSecurityController::class, 'blockedIps'])->name('blocked-ips');
            Route::post('/blocked-ips', [App\Http\Controllers\Admin\AdminSecurityController::class, 'blockIp'])->name('blocked-ips.store');
            Route::delete('/blocked-ips/{id}', [App\Http\Controllers\Admin\AdminSecurityController::class, 'unblockIp'])->name('blocked-ips.destroy');
            Route::get('/sessions', [App\Http\Controllers\Admin\AdminSecurityController::class, 'activeSessions'])->name('sessions');
            Route::delete('/sessions/{id}', [App\Http\Controllers\Admin\AdminSecurityController::class, 'terminateSession'])->name('sessions.terminate');
            Route::post('/sessions/terminate-all', [App\Http\Controllers\Admin\AdminSecurityController::class, 'terminateAllSessions'])->name('sessions.terminate-all');
            Route::get('/roles', [App\Http\Controllers\Admin\AdminSecurityController::class, 'roles'])->name('roles');
            Route::post('/roles', [App\Http\Controllers\Admin\AdminSecurityController::class, 'createRole'])->name('roles.store');
            Route::put('/roles/{id}', [App\Http\Controllers\Admin\AdminSecurityController::class, 'updateRole'])->name('roles.update');
            Route::delete('/roles/{id}', [App\Http\Controllers\Admin\AdminSecurityController::class, 'deleteRole'])->name('roles.destroy');
            Route::get('/audit-log', [App\Http\Controllers\Admin\AdminSecurityController::class, 'auditLog'])->name('audit-log');
            Route::get('/audit-log/export', [App\Http\Controllers\Admin\AdminSecurityController::class, 'exportAuditLog'])->name('audit-log.export');

            // Changement de mot de passe admin
            Route::get('/change-password', [App\Http\Controllers\Admin\AdminSecurityController::class, 'changePassword'])->name('change-password');
            Route::put('/change-password', [App\Http\Controllers\Admin\AdminSecurityController::class, 'updatePassword'])->name('change-password.update');
        });

        // 12. Gestion de contenu (AdminContentController)
        Route::prefix('content')->name('content.')->group(function () {
            // Pages statiques
            Route::get('/pages', [App\Http\Controllers\Admin\AdminContentController::class, 'pages'])->name('pages');
            Route::get('/pages/create', [App\Http\Controllers\Admin\AdminContentController::class, 'createPage'])->name('pages.create');
            Route::post('/pages', [App\Http\Controllers\Admin\AdminContentController::class, 'storePage'])->name('pages.store');
            Route::get('/pages/{id}/edit', [App\Http\Controllers\Admin\AdminContentController::class, 'editPage'])->name('pages.edit');
            Route::put('/pages/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'updatePage'])->name('pages.update');
            Route::delete('/pages/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'deletePage'])->name('pages.destroy');

            // FAQ
            Route::get('/faqs', [App\Http\Controllers\Admin\AdminContentController::class, 'faqs'])->name('faqs');
            Route::post('/faqs', [App\Http\Controllers\Admin\AdminContentController::class, 'storeFaq'])->name('faqs.store');
            Route::put('/faqs/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'updateFaq'])->name('faqs.update');
            Route::delete('/faqs/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'deleteFaq'])->name('faqs.destroy');
            Route::post('/faqs/reorder', [App\Http\Controllers\Admin\AdminContentController::class, 'reorderFaqs'])->name('faqs.reorder');

            // Bannières
            Route::get('/banners', [App\Http\Controllers\Admin\AdminContentController::class, 'banners'])->name('banners');
            Route::post('/banners', [App\Http\Controllers\Admin\AdminContentController::class, 'storeBanner'])->name('banners.store');
            Route::put('/banners/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'updateBanner'])->name('banners.update');
            Route::delete('/banners/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'deleteBanner'])->name('banners.destroy');

            // Témoignages
            Route::get('/testimonials', [App\Http\Controllers\Admin\AdminContentController::class, 'testimonials'])->name('testimonials');
            Route::post('/testimonials', [App\Http\Controllers\Admin\AdminContentController::class, 'storeTestimonial'])->name('testimonials.store');
            Route::put('/testimonials/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'updateTestimonial'])->name('testimonials.update');
            Route::delete('/testimonials/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'deleteTestimonial'])->name('testimonials.destroy');

            // Templates email
            Route::get('/email-templates', [App\Http\Controllers\Admin\AdminContentController::class, 'emailTemplates'])->name('email-templates');
            Route::get('/email-templates/{id}/edit', [App\Http\Controllers\Admin\AdminContentController::class, 'editEmailTemplate'])->name('email-templates.edit');
            Route::put('/email-templates/{id}', [App\Http\Controllers\Admin\AdminContentController::class, 'updateEmailTemplate'])->name('email-templates.update');
            Route::get('/email-templates/{id}/preview', [App\Http\Controllers\Admin\AdminContentController::class, 'previewEmailTemplate'])->name('email-templates.preview');

            // Médiathèque
            Route::get('/media', [App\Http\Controllers\Admin\AdminContentController::class, 'mediaLibrary'])->name('media');
            Route::post('/media/upload', [App\Http\Controllers\Admin\AdminContentController::class, 'uploadMedia'])->name('media.upload');
            Route::delete('/media', [App\Http\Controllers\Admin\AdminContentController::class, 'deleteMedia'])->name('media.destroy');
            Route::post('/media/folder', [App\Http\Controllers\Admin\AdminContentController::class, 'createFolder'])->name('media.folder');
        });

        // 13. Gestion financière (AdminFinanceController)
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminFinanceController::class, 'dashboard'])->name('index');
            Route::get('/dashboard', [App\Http\Controllers\Admin\AdminFinanceController::class, 'dashboard'])->name('dashboard');

            // Transactions
            Route::get('/transactions', [App\Http\Controllers\Admin\AdminFinanceController::class, 'transactions'])->name('transactions');
            Route::get('/transactions/{id}', [App\Http\Controllers\Admin\AdminFinanceController::class, 'transactionDetails'])->name('transactions.show');
            Route::get('/transactions/export', [App\Http\Controllers\Admin\AdminFinanceController::class, 'exportTransactions'])->name('transactions.export');

            // Retraits
            Route::get('/withdrawals', [App\Http\Controllers\Admin\AdminFinanceController::class, 'withdrawals'])->name('withdrawals');
            Route::post('/withdrawals/{id}/process', [App\Http\Controllers\Admin\AdminFinanceController::class, 'processWithdrawal'])->name('withdrawals.process');
            Route::post('/withdrawals/bulk', [App\Http\Controllers\Admin\AdminFinanceController::class, 'bulkProcessWithdrawals'])->name('withdrawals.bulk');

            // Remboursements
            Route::get('/refunds', [App\Http\Controllers\Admin\AdminFinanceController::class, 'refunds'])->name('refunds');
            Route::post('/refunds', [App\Http\Controllers\Admin\AdminFinanceController::class, 'createRefund'])->name('refunds.store');
            Route::post('/refunds/{id}/process', [App\Http\Controllers\Admin\AdminFinanceController::class, 'processRefund'])->name('refunds.process');

            // Factures
            Route::get('/invoices', [App\Http\Controllers\Admin\AdminFinanceController::class, 'invoices'])->name('invoices');
            Route::get('/invoices/{id}/download', [App\Http\Controllers\Admin\AdminFinanceController::class, 'generateInvoice'])->name('invoices.download');
            Route::post('/invoices/{id}/send', [App\Http\Controllers\Admin\AdminFinanceController::class, 'sendInvoice'])->name('invoices.send');

            // Commissions
            Route::get('/commissions', [App\Http\Controllers\Admin\AdminFinanceController::class, 'commissions'])->name('commissions');

            // Versements prestataires
            Route::get('/payouts', [App\Http\Controllers\Admin\AdminFinanceController::class, 'payouts'])->name('payouts');
            Route::post('/payouts', [App\Http\Controllers\Admin\AdminFinanceController::class, 'createPayout'])->name('payouts.store');
            Route::post('/payouts/{id}/process', [App\Http\Controllers\Admin\AdminFinanceController::class, 'processPayout'])->name('payouts.process');

            // Synchronisation escrow
            Route::post('/escrow/sync', [App\Http\Controllers\Admin\AdminFinanceController::class, 'syncEscrowPayments'])->name('escrow.sync');
        });

        // 14. Support et tickets (AdminSupportController)
        Route::prefix('support')->name('support.')->group(function () {
            // Tickets
            Route::get('/tickets', [App\Http\Controllers\Admin\AdminSupportController::class, 'tickets'])->name('tickets');
            Route::get('/tickets/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'ticketDetails'])->name('tickets.show');
            Route::post('/tickets/{id}/reply', [App\Http\Controllers\Admin\AdminSupportController::class, 'replyTicket'])->name('tickets.reply');
            Route::put('/tickets/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'updateTicket'])->name('tickets.update');
            Route::post('/tickets/{id}/close', [App\Http\Controllers\Admin\AdminSupportController::class, 'closeTicket'])->name('tickets.close');

            // Messages de contact
            Route::get('/contact-messages', [App\Http\Controllers\Admin\AdminSupportController::class, 'contactMessages'])->name('contact-messages');
            Route::get('/contact-messages/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'viewContactMessage'])->name('contact-messages.show');
            Route::post('/contact-messages/{id}/reply', [App\Http\Controllers\Admin\AdminSupportController::class, 'replyContactMessage'])->name('contact-messages.reply');
            Route::delete('/contact-messages/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'deleteContactMessage'])->name('contact-messages.destroy');

            // Litiges
            Route::get('/disputes', [App\Http\Controllers\Admin\AdminSupportController::class, 'disputes'])->name('disputes');
            Route::get('/disputes/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'disputeDetails'])->name('disputes.show');
            Route::put('/disputes/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'updateDispute'])->name('disputes.update');
            Route::post('/disputes/{id}/reply', [App\Http\Controllers\Admin\AdminSupportController::class, 'replyDispute'])->name('disputes.reply');

            // Articles d'aide
            Route::get('/help-articles', [App\Http\Controllers\Admin\AdminSupportController::class, 'helpArticles'])->name('help-articles');
            Route::get('/help-articles/create', [App\Http\Controllers\Admin\AdminSupportController::class, 'createHelpArticle'])->name('help-articles.create');
            Route::post('/help-articles', [App\Http\Controllers\Admin\AdminSupportController::class, 'storeHelpArticle'])->name('help-articles.store');
            Route::get('/help-articles/{id}/edit', [App\Http\Controllers\Admin\AdminSupportController::class, 'editHelpArticle'])->name('help-articles.edit');
            Route::put('/help-articles/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'updateHelpArticle'])->name('help-articles.update');
            Route::delete('/help-articles/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'deleteHelpArticle'])->name('help-articles.destroy');

            // Statistiques support
            Route::get('/statistics', [App\Http\Controllers\Admin\AdminSupportController::class, 'statistics'])->name('statistics');
        });

        // 15. Pages légales dynamiques (LegalPagesController)
        Route::prefix('legal-pages')->name('legal-pages.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\LegalPagesController::class, 'index'])->name('index');
            Route::get('/{legalPage}/edit', [App\Http\Controllers\Admin\LegalPagesController::class, 'edit'])->name('edit');
            Route::put('/{legalPage}', [App\Http\Controllers\Admin\LegalPagesController::class, 'update'])->name('update');
            Route::get('/{legalPage}/preview', [App\Http\Controllers\Admin\LegalPagesController::class, 'preview'])->name('preview');
        });

        // 16. Factures - Invoice Management (Admin)
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('index');
            Route::get('/export', [App\Http\Controllers\Admin\InvoiceController::class, 'export'])->name('export');
            Route::get('/commissions', [App\Http\Controllers\Admin\InvoiceController::class, 'commissions'])->name('commissions');
            Route::get('/{invoice}', [App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('show');
        });

        // 17. Ambassadeurs Management
        Route::prefix('ambassadors')->name('ambassadors.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AmbassadorController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\AmbassadorController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\AmbassadorController::class, 'store'])->name('store');
            Route::get('/payouts', [App\Http\Controllers\Admin\AmbassadorController::class, 'payoutsIndex'])->name('payouts.index');
            Route::get('/settings', [App\Http\Controllers\Admin\AmbassadorController::class, 'settings'])->name('settings');
            Route::post('/settings', [App\Http\Controllers\Admin\AmbassadorController::class, 'updateSettings'])->name('settings.update');
            Route::get('/{ambassador}', [App\Http\Controllers\Admin\AmbassadorController::class, 'show'])->name('show');
            Route::get('/{ambassador}/edit', [App\Http\Controllers\Admin\AmbassadorController::class, 'edit'])->name('edit');
            Route::put('/{ambassador}', [App\Http\Controllers\Admin\AmbassadorController::class, 'update'])->name('update');
            Route::delete('/{ambassador}', [App\Http\Controllers\Admin\AmbassadorController::class, 'destroy'])->name('destroy');
            Route::get('/{ambassador}/commissions', [App\Http\Controllers\Admin\AmbassadorController::class, 'commissions'])->name('commissions');
            Route::post('/{ambassador}/payout', [App\Http\Controllers\Admin\AmbassadorController::class, 'createPayout'])->name('payout');
            Route::post('/{ambassador}/assign-prestataire', [App\Http\Controllers\Admin\AmbassadorController::class, 'assignPrestataire'])->name('assign-prestataire');
        });
    });
