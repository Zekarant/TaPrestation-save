<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PrestataireConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_status' => 'required|in:good,damaged',
            'damage_description' => 'required_if:equipment_status,damaged|nullable|string|max:1000',
            'retain_deposit_percent' => 'required_if:equipment_status,damaged|nullable|integer|min:0|max:100',
            'damage_photos' => 'nullable|array|max:5',
            'damage_photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }
}
