<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Auction\AuctionController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Address\AddressBookController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Notifications\NotificationSettingsController;

// Payment Routes
Route::prefix('payments')->middleware(['auth'])->group(function () {
    Route::get('/booking/{booking}', [PaymentController::class, 'showPaymentForm'])->name('payment.form');
    Route::post('/booking/{booking}/intent', [PaymentController::class, 'createPaymentIntent'])->middleware('throttle:10,1')->name('payment.intent');
    Route::post('/booking/{booking}/confirm', [PaymentController::class, 'confirmPayment'])->middleware('throttle:20,1')->name('payment.confirm');
    Route::post('/booking/{booking}/refund', [PaymentController::class, 'requestRefund'])->middleware('throttle:5,1')->name('payment.refund');
    Route::post('/booking/{booking}/cash', [PaymentController::class, 'confirmCashPayment'])->middleware('throttle:10,1')->name('payment.cash');
    Route::get('/booking/{booking}/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/history', [PaymentController::class, 'transactionHistory'])->name('payment.history');
});

// Auction Routes
Route::prefix('auctions')->middleware(['auth'])->group(function () {
    Route::get('/service/{service}', [AuctionController::class, 'showBids'])->name('auction.bids');
    Route::post('/service/{service}/bid', [AuctionController::class, 'placeBid'])->name('auction.place-bid');
    Route::post('/bid/{bid}/accept', [AuctionController::class, 'acceptBid'])->name('auction.accept');
    Route::post('/bid/{bid}/reject', [AuctionController::class, 'rejectBid'])->name('auction.reject');
    Route::get('/my-bids', [AuctionController::class, 'myBids'])->name('auction.my-bids');
    Route::get('/stats', [AuctionController::class, 'stats'])->name('auction.stats');
});

// Delivery Routes
Route::prefix('delivery')->middleware(['auth'])->group(function () {
    Route::post('/providers', [DeliveryController::class, 'getAvailableProviders'])->name('delivery.providers');
    Route::post('/calculate', [DeliveryController::class, 'calculateShipping'])->name('delivery.calculate');
    Route::post('/track', [DeliveryController::class, 'track'])->name('delivery.track');
    Route::post('/booking/{booking}/setup', [DeliveryController::class, 'setupDelivery'])->name('delivery.setup');
});

// Subscription Routes
Route::prefix('subscriptions')->middleware(['auth'])->group(function () {
    Route::get('/plans', [SubscriptionController::class, 'showPlans'])->name('subscription.plans');
    Route::post('/plan/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription'])->name('subscription.my');
});

// Address Book Routes
Route::prefix('address-book')->middleware(['auth'])->group(function () {
    Route::get('/', [AddressBookController::class, 'index'])->name('address-book.index');
    Route::post('/add', [AddressBookController::class, 'store'])->name('address-book.store');
    Route::patch('/{address}/update', [AddressBookController::class, 'update'])->name('address-book.update');
    Route::delete('/{address}', [AddressBookController::class, 'destroy'])->name('address-book.destroy');
    Route::patch('/{address}/default', [AddressBookController::class, 'setDefault'])->name('address-book.default');
});

// Notification Settings Routes
Route::prefix('notification-settings')->middleware(['auth'])->group(function () {
    Route::get('/', [NotificationSettingsController::class, 'index'])->name('notification-settings.index');
    Route::patch('/', [NotificationSettingsController::class, 'update'])->name('notification-settings.update');
    Route::post('/test-email', [NotificationSettingsController::class, 'testEmail'])->name('notification-settings.test-email');
    Route::post('/test-sms', [NotificationSettingsController::class, 'testSMS'])->name('notification-settings.test-sms');
    Route::post('/test-push', [NotificationSettingsController::class, 'testPush'])->name('notification-settings.test-push');
    Route::get('/quiet-hours', [NotificationSettingsController::class, 'getQuietHours'])->name('notification-settings.quiet-hours');
    Route::patch('/quiet-hours', [NotificationSettingsController::class, 'updateQuietHours'])->name('notification-settings.update-quiet-hours');
});

// Inventory Routes (for Prestataires)
Route::prefix('inventory')->middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/add', [InventoryController::class, 'store'])->name('inventory.store');
    Route::patch('/{item}/update', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/{item}/decrease-stock', [InventoryController::class, 'decreaseStock'])->name('inventory.decrease-stock');
    Route::post('/{item}/increase-stock', [InventoryController::class, 'increaseStock'])->name('inventory.increase-stock');
    Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::get('/analytics', [InventoryController::class, 'getAnalytics'])->name('inventory.analytics');
    Route::get('/export', [InventoryController::class, 'export'])->name('inventory.export');
});


