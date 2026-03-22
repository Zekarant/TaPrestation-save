<?php

use App\Http\Controllers\Ambassador\CommissionController;
use App\Http\Controllers\Ambassador\DashboardController;
use App\Http\Controllers\Ambassador\PrestataireController;
use App\Http\Controllers\Ambassador\ProfileController;
use App\Http\Controllers\Ambassador\ReferralController;
use App\Http\Controllers\Ambassador\StripeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:ambassador', 'throttle:60,1'])
    ->prefix('ambassador')
    ->name('ambassador.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Prestataires management
        Route::get('/prestataires', [PrestataireController::class, 'index'])->name('prestataires.index');
        Route::get('/prestataires/create', [PrestataireController::class, 'create'])->name('prestataires.create');
        Route::post('/prestataires', [PrestataireController::class, 'store'])->name('prestataires.store');
        Route::get('/prestataires/{prestataire}', [PrestataireController::class, 'show'])->name('prestataires.show');

        // Commissions
        Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
        Route::get('/commissions/payouts', [CommissionController::class, 'payouts'])->name('commissions.payouts');

        // Referral
        Route::get('/referral', [ReferralController::class, 'index'])->name('referral.index');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Stripe Connect
        Route::get('/stripe', [StripeController::class, 'index'])->name('stripe.index');
        Route::post('/stripe/connect', [StripeController::class, 'createAccount'])->name('stripe.connect');
        Route::get('/stripe/callback', [StripeController::class, 'callback'])->name('stripe.callback');
    });
