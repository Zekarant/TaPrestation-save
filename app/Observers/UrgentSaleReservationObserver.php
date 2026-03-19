<?php

namespace App\Observers;

use App\Models\UrgentSaleReservation;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\Log;

class UrgentSaleReservationObserver
{
    protected InventorySyncService $syncService;
    
    public function __construct(InventorySyncService $syncService)
    {
        $this->syncService = $syncService;
    }
    
    /**
     * Handle the UrgentSaleReservation "updated" event.
     */
    public function updated(UrgentSaleReservation $reservation): void
    {
        // Si le statut a changé
        if ($reservation->isDirty('status')) {
            $oldStatus = $reservation->getOriginal('status');
            $newStatus = $reservation->status;
            
            Log::info('Reservation status changed', [
                'reservation_id' => $reservation->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
            
            // Si annulée alors qu'elle était confirmée → libérer le stock
            if ($newStatus === UrgentSaleReservation::STATUS_CANCELLED 
                && $oldStatus === UrgentSaleReservation::STATUS_CONFIRMED) {
                $this->syncService->releaseReservedStock($reservation);
            }
            
            // Si complétée → transférer de réservé à vendu
            // Note: ceci est déjà géré dans UrgentSaleReservation::complete()
            // mais on peut le doubler ici pour sécurité
        }
    }
    
    /**
     * Handle the UrgentSaleReservation "deleted" event.
     */
    public function deleted(UrgentSaleReservation $reservation): void
    {
        // Si supprimée alors qu'elle était confirmée → libérer le stock
        if ($reservation->status === UrgentSaleReservation::STATUS_CONFIRMED) {
            $this->syncService->releaseReservedStock($reservation);
        }
    }
}
