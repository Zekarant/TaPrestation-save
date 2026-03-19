<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmUrgentSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition' => 'required|in:good,damaged,partial_damage',
            'damage_percent' => 'required_if:condition,damaged,partial_damage|nullable|integer|min:0|max:100',
            'damage_description' => 'required_if:condition,damaged,partial_damage|nullable|string|max:1000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }
}
