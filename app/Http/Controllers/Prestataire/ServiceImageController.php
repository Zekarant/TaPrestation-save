<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\ServiceImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceImageController extends Controller
{
    public function destroy(ServiceImage $image)
    {
        // Optional: Add authorization check here to ensure the user owns the service
        
        // Check if image_path exists before attempting to delete
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();

        return response()->json(['success' => true]);
    }
}