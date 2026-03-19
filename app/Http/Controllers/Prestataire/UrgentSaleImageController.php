<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\UrgentSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UrgentSaleImageController extends Controller
{
    public function destroy(UrgentSale $urgentSale, $photoIndex)
    {
        // Authorize the user to update this urgent sale
        $this->authorize('update', $urgentSale);
        
        // Get the photos array
        $photos = $urgentSale->photos ?? [];
        
        // Check if the photo index exists
        if (!isset($photos[$photoIndex])) {
            return response()->json(['success' => false, 'message' => 'Photo non trouvée.'], 404);
        }
        
        // Get the photo path
        $photoPath = $photos[$photoIndex];
        
        // Delete the photo file if it exists
        if ($photoPath && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }
        
        // Remove the photo from the array
        unset($photos[$photoIndex]);
        
        // Re-index the array to ensure sequential keys
        $photos = array_values($photos);
        
        // Update the urgent sale with the new photos array
        $urgentSale->update(['photos' => $photos]);
        
        return response()->json(['success' => true]);
    }
}