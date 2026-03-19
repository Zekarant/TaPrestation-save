<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UrgentSale;
use App\Models\InventoryItem;

class SyncUrgentSaleInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrateur']);
    }

    public function sync(Request $request)
    {
        $count = 0;
        DB::beginTransaction();
        try {
            $urgentSales = UrgentSale::whereNotNull('inventory_item_id')->get();
            foreach ($urgentSales as $urgentSale) {
                $inventory = InventoryItem::find($urgentSale->inventory_item_id);
                if ($inventory && !$inventory->urgent_sale_id) {
                    $inventory->urgent_sale_id = $urgentSale->id;
                    $inventory->save();
                    $count++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sync urgent_sale_id failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Synchronisation impossible pour le moment.',
            ], 500);
        }
        return response()->json(['success' => true, 'updated' => $count]);
    }
}
