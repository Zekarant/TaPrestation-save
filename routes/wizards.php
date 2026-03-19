<?php

use Illuminate\Support\Facades\Route;

// Wizards services
Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('prestataire/services/create/step1', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep1'])->name('prestataire.services.create.step1');
    Route::post('prestataire/services/create/step1', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep1'])->name('prestataire.services.create.step1.store');
    Route::get('prestataire/services/create/step2', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep2'])->name('prestataire.services.create.step2');
    Route::post('prestataire/services/create/step2', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep2'])->name('prestataire.services.create.step2.store');
    Route::get('prestataire/services/create/step3', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep3'])->name('prestataire.services.create.step3');
    Route::post('prestataire/services/create/step3', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep3'])->name('prestataire.services.create.step3.store');
    Route::get('prestataire/services/create/step4', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep4'])->name('prestataire.services.create.step4');
    Route::post('prestataire/services/create/step4', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep4'])->name('prestataire.services.create.step4.store');
    Route::get('prestataire/services/create/review', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createReview'])->name('prestataire.services.create.review');
    Route::post('prestataire/services/create', [\App\Http\Controllers\Prestataire\ServiceController::class, 'store'])->name('prestataire.services.create.store');
});

// Wizard équipement
Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('prestataire/equipment/create/step1', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep1'])->name('prestataire.equipment.create.step1');
    Route::post('prestataire/equipment/create/step1', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep1'])->name('prestataire.equipment.store.step1');
    Route::get('prestataire/equipment/create/step2', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep2'])->name('prestataire.equipment.create.step2');
    Route::post('prestataire/equipment/create/step2', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep2'])->name('prestataire.equipment.store.step2');
    Route::get('prestataire/equipment/create/step3', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep3'])->name('prestataire.equipment.create.step3');
    Route::post('prestataire/equipment/create/step3', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep3'])->name('prestataire.equipment.store.step3');
    Route::get('prestataire/equipment/create/step4', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep4'])->name('prestataire.equipment.create.step4');
});

// Wizard urgent-sales
Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('prestataire/urgent-sales/api/inventory', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'getInventoryItems'])->name('prestataire.urgent-sales.api.inventory');
    Route::get('prestataire/urgent-sales/api/equipments', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'getEquipments'])->name('prestataire.urgent-sales.api.equipments');
    Route::get('prestataire/urgent-sales/create/step1', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep1'])->name('prestataire.urgent-sales.create.step1');
    Route::post('prestataire/urgent-sales/create/step1', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'storeStep1'])->name('prestataire.urgent-sales.create.step1.store');
    Route::get('prestataire/urgent-sales/create/step2', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep2'])->name('prestataire.urgent-sales.create.step2');
    Route::post('prestataire/urgent-sales/create/step2', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'storeStep2'])->name('prestataire.urgent-sales.create.step2.store');
    Route::get('prestataire/urgent-sales/create/step3', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep3'])->name('prestataire.urgent-sales.create.step3');
    Route::post('prestataire/urgent-sales/create/step3', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'storeStep3'])->name('prestataire.urgent-sales.create.step3.store');
    Route::get('prestataire/urgent-sales/create/step4', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep4'])->name('prestataire.urgent-sales.create.step4');
});
