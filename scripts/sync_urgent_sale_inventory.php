<?php
// Script de synchronisation des liens urgent_sale_id <-> inventory_item_id
// À exécuter une seule fois en CLI : php scripts/sync_urgent_sale_inventory.php

use Illuminate\Support\Facades\App;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Boot Laravel
$kernel->bootstrap();

use App\Models\UrgentSale;
use App\Models\InventoryItem;

echo "Synchronisation des liens urgent_sale_id <-> inventory_item_id...\n";

$count = 0;
$updated = 0;

UrgentSale::whereNotNull('inventory_item_id')->chunk(100, function ($sales) use (&$count, &$updated) {
    foreach ($sales as $sale) {
        $count++;
        $item = InventoryItem::find($sale->inventory_item_id);
        if ($item && $item->urgent_sale_id !== $sale->id) {
            $item->urgent_sale_id = $sale->id;
            $item->save();
            $updated++;
            echo "[OK] InventoryItem #{$item->id} lié à UrgentSale #{$sale->id}\n";
        }
    }
});

echo "Terminé. $updated inventaires mis à jour sur $count annonces traitées.\n";
