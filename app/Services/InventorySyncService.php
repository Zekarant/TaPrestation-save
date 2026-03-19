<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\UrgentSale;
use App\Models\UrgentSaleReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de synchronisation de l'inventaire avec les ventes et réservations
 */
class InventorySyncService
{
    /**
     * Synchronise le stock d'un article d'inventaire avec ses ventes urgentes
     * Stock réel = quantity initiale - sold_quantity (des UrgentSales liées)
     * Stock disponible = stock réel - reserved_quantity
     */
    public function syncInventoryItem(InventoryItem $item): array
    {
        $urgentSales = $item->urgentSales ?? collect();
        
        // Si relation inversée (urgentSale_id sur inventory)
        if ($item->urgentSale) {
            $urgentSales = collect([$item->urgentSale])->merge($urgentSales);
        }
        
        $totalReserved = 0;
        $totalSold = 0;
        
        foreach ($urgentSales as $sale) {
            $totalReserved += (int) ($sale->reserved_quantity ?? 0);
            $totalSold += (int) ($sale->sold_quantity ?? 0);
        }
        
        return [
            'total_stock' => $item->quantity,
            'initial_stock' => $item->initial_quantity ?? $item->quantity,
            'reserved' => $totalReserved,
            'sold' => $totalSold,
            'available' => max(0, $item->quantity - $totalReserved),
        ];
    }
    
    /**
     * Libère le stock réservé quand une réservation est annulée ou expirée
     */
    public function releaseReservedStock(UrgentSaleReservation $reservation): void
    {
        if (!$reservation->urgentSale) {
            return;
        }
        
        $urgentSale = $reservation->urgentSale;
        $quantity = (int) $reservation->quantity;
        
        // Décrémenter reserved_quantity si > 0
        if ($urgentSale->reserved_quantity >= $quantity) {
            $urgentSale->decrement('reserved_quantity', $quantity);
        } else {
            // Éviter les valeurs négatives
            $urgentSale->update(['reserved_quantity' => 0]);
        }
        
        Log::info('Stock réservé libéré', [
            'reservation_id' => $reservation->id,
            'urgent_sale_id' => $urgentSale->id,
            'quantity_released' => $quantity,
            'new_reserved' => $urgentSale->fresh()->reserved_quantity,
        ]);
        
        // Synchroniser avec l'inventaire si lié
        $this->syncWithInventory($urgentSale);
    }
    
    /**
     * Confirme la vente et transfère de réservé à vendu
     */
    public function confirmSale(UrgentSaleReservation $reservation): void
    {
        if (!$reservation->urgentSale) {
            return;
        }
        
        $urgentSale = $reservation->urgentSale;
        $quantity = (int) $reservation->quantity;
        
        DB::transaction(function () use ($urgentSale, $quantity) {
            // Transférer de réservé à vendu
            if ($urgentSale->reserved_quantity >= $quantity) {
                $urgentSale->decrement('reserved_quantity', $quantity);
            }
            $urgentSale->increment('sold_quantity', $quantity);
        });
        
        Log::info('Vente confirmée', [
            'urgent_sale_id' => $urgentSale->id,
            'quantity_sold' => $quantity,
            'new_sold' => $urgentSale->fresh()->sold_quantity,
        ]);
        
        // Synchroniser avec l'inventaire
        $this->syncWithInventory($urgentSale);
    }
    
    /**
     * Synchronise une UrgentSale avec son InventoryItem associé
     */
    public function syncWithInventory(UrgentSale $urgentSale): void
    {
        // Si l'urgentSale est liée à un inventaire
        if (!$urgentSale->inventory_item_id) {
            return;
        }
        
        $inventoryItem = $urgentSale->inventoryItem;
        if (!$inventoryItem) {
            return;
        }
        
        // Le stock de l'inventaire doit refléter les ventes
        // Nouvelle quantité = quantité initiale - total vendu de toutes les urgentSales liées
        $totalSold = UrgentSale::where('inventory_item_id', $inventoryItem->id)
            ->sum('sold_quantity');
        
        $newQuantity = max(0, ($inventoryItem->initial_quantity ?? $inventoryItem->quantity) - $totalSold);
        
        if ($inventoryItem->quantity != $newQuantity) {
            $inventoryItem->update(['quantity' => $newQuantity]);
            
            Log::info('Stock inventaire synchronisé', [
                'inventory_item_id' => $inventoryItem->id,
                'old_quantity' => $inventoryItem->getOriginal('quantity'),
                'new_quantity' => $newQuantity,
                'total_sold' => $totalSold,
            ]);
        }
    }
    
    /**
     * Synchronise tout l'inventaire d'un utilisateur
     */
    public function syncUserInventory(int $userId): array
    {
        $items = InventoryItem::where('user_id', $userId)->get();
        $synced = 0;
        
        foreach ($items as $item) {
            $this->syncInventoryItemFromSales($item);
            $synced++;
        }
        
        return ['synced_items' => $synced];
    }
    
    /**
     * Recalcule la quantité d'un article basée sur les ventes
     */
    public function syncInventoryItemFromSales(InventoryItem $item): void
    {
        // Trouver toutes les UrgentSales liées
        $totalSold = 0;
        
        // Via inventory_item_id sur urgent_sales
        $salesViaFk = UrgentSale::where('inventory_item_id', $item->id)->get();
        foreach ($salesViaFk as $sale) {
            $totalSold += (int) ($sale->sold_quantity ?? 0);
        }
        
        // Via urgent_sale_id sur inventory (relation inverse)
        if ($item->urgent_sale_id && $item->urgentSale) {
            $totalSold += (int) ($item->urgentSale->sold_quantity ?? 0);
        }
        
        $initialQty = $item->initial_quantity ?? $item->quantity;
        $newQty = max(0, $initialQty - $totalSold);
        
        if ($item->quantity != $newQty) {
            $item->update(['quantity' => $newQty]);
        }
    }
    
    /**
     * Expire les réservations en attente après X heures et libère le stock
     */
    public function expireOldReservations(int $hoursBeforeExpiry = 24): int
    {
        $expiredCount = 0;
        
        $pendingReservations = UrgentSaleReservation::where('status', UrgentSaleReservation::STATUS_PENDING)
            ->where('created_at', '<', now()->subHours($hoursBeforeExpiry))
            ->get();
        
        foreach ($pendingReservations as $reservation) {
            $reservation->update([
                'status' => UrgentSaleReservation::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
            
            // La réservation pending n'a pas encore réservé le stock
            // (le stock est réservé seulement à la confirmation)
            // Mais si jamais il a été réservé, le libérer
            if ($reservation->urgentSale && $reservation->urgentSale->reserved_quantity > 0) {
                $this->releaseReservedStock($reservation);
            }
            
            $expiredCount++;
        }
        
        Log::info('Réservations expirées', ['count' => $expiredCount]);
        
        return $expiredCount;
    }
    
    /**
     * Annule les réservations confirmées non payées après X heures et libère le stock
     */
    public function cancelUnpaidReservations(int $hoursAfterConfirmation = 48): int
    {
        $cancelledCount = 0;
        
        $unpaidReservations = UrgentSaleReservation::where('status', UrgentSaleReservation::STATUS_CONFIRMED)
            ->where('confirmed_at', '<', now()->subHours($hoursAfterConfirmation))
            ->whereNull('completed_at')
            ->get();
        
        foreach ($unpaidReservations as $reservation) {
            // Libérer le stock réservé
            $this->releaseReservedStock($reservation);
            
            $reservation->update([
                'status' => UrgentSaleReservation::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
            
            $cancelledCount++;
        }
        
        Log::info('Réservations non payées annulées', ['count' => $cancelledCount]);
        
        return $cancelledCount;
    }
}
