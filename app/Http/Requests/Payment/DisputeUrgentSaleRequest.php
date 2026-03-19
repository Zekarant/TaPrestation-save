<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class DisputeUrgentSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|min:20|max:2000',
            'evidence' => 'required|array|min:1|max:5',
            'evidence.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }
}
