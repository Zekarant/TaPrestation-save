<?php

namespace App\Http\Requests\Prestataire\UrgentSales;

use Illuminate\Foundation\Http\FormRequest;

class GetCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow all users to access categories
        return true;
    }

    public function rules(): array
    {
        return [
            // No input validation needed for category retrieval
        ];
    }
}