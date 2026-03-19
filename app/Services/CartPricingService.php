<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EquipmentRentalRequest;
use App\Models\UrgentSale;
use Illuminate\Database\Eloquent\Model;

class CartPricingService
{
    /**
     * Returns pricing snapshot for the cart line.
     * - unit_price: price per unit (qty)
     * - line_total: total payable if paying full
     * - line_deposit: total payable if paying deposit (per item)
     */
    public function pricingFor(Model $purchasable, int $quantity = 1): array
    {
        $quantity = max(1, (int) $quantity);
        $currency = 'eur';

        if ($purchasable instanceof Booking) {
            $unit = (float) $purchasable->total_price;
            $total = $unit;
            $deposit = (float) ($purchasable->deposit_amount ?? 0);
            // If no deposit is configured, deposit defaults to full price for that item
            $lineDeposit = $deposit > 0 ? $deposit : $total;

            return [
                'unit_price' => $unit,
                'line_total' => $total,
                'line_deposit' => $lineDeposit,
                'currency' => $currency,
                'quantity' => 1,
            ];
        }

        if ($purchasable instanceof EquipmentRentalRequest) {
            // Rental requests are single-quantity items
            $unit = (float) ($purchasable->final_amount ?? $purchasable->total_amount ?? 0);
            $total = $unit;
            $deposit = (float) ($purchasable->security_deposit ?? 0);
            $lineDeposit = $deposit > 0 ? $deposit : $total;

            return [
                'unit_price' => $unit,
                'line_total' => $total,
                'line_deposit' => $lineDeposit,
                'currency' => $currency,
                'quantity' => 1,
            ];
        }

        if ($purchasable instanceof UrgentSale) {
            $unit = (float) $purchasable->price;
            $total = $unit * $quantity;
            // For urgent-sale products, deposit == full amount
            $lineDeposit = $total;

            return [
                'unit_price' => $unit,
                'line_total' => $total,
                'line_deposit' => $lineDeposit,
                'currency' => $currency,
                'quantity' => $quantity,
            ];
        }

        throw new \InvalidArgumentException('Unsupported cart item type');
    }
}
