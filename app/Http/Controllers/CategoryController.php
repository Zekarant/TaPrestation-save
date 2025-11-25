<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get main categories (those without parent)
     */
    public function getMainCategories()
    {
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * Get subcategories for a given category
     */
    public function getSubcategories(Category $category)
    {
        $subcategories = Category::where('parent_id', $category->id)->orderBy('name')->get();
        return response()->json($subcategories);
    }
}