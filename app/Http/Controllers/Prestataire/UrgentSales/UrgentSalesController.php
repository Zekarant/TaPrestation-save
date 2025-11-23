<?php

namespace App\Http\Controllers\Prestataire\UrgentSales;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Prestataire\UrgentSales\StoreStep2Request;

class UrgentSalesController extends Controller
{
    public function getCategories()
    {
        // Retrieve categories from the database
        $categories = DB::table('categories')->get();
        
        // Return categories as JSON response
        return response()->json($categories);
    }
    
    public function createStep1()
    {
        // Retrieve all categories from the database
        $categories = DB::table('categories')
            ->whereNull('parent_id')
            ->orWhere('parent_id', 0)
            ->get();
        
        // Convert categories to array for easier access in JavaScript
        $categoriesArray = [];
        foreach ($categories as $category) {
            $subcategories = DB::table('categories')
                ->where('parent_id', $category->id)
                ->get();
            
            $categoriesArray[$category->id] = $subcategories->toArray();
        }
        
        // Pass categories to the view
        return view('prestataire.urgent-sales.steps.step1', [
            'categories' => $categories,
            'categoriesData' => $categoriesArray
        ]);
    }
    
    public function createStep2()
    {
        // Retrieve categories from session or database
        $categories = session('categories', []);
        
        // If no categories in session, retrieve from database
        if (empty($categories)) {
            $categories = DB::table('categories')->get();
            // Store in session for next steps
            session(['categories' => $categories]);
        }
        
        // Retrieve location data from session if any
        $locationData = session('urgent_sale_data', []);
        
        // Pass data to the view
        return view('prestataire.urgent-sales.steps.step2', [
            'locationData' => $locationData
        ]);
    }
    
    public function storeStep2(StoreStep2Request $request)
    {
        // Validate the input
        $validated = $request->validated();
        
        // Store the data in session
        session(['urgent_sale_data' => $validated]);
        
        // Redirect to step 3
        return redirect()->route('prestataire.urgent-sales.create.step3');
    }
}