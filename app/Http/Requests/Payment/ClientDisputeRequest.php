<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ClientDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|in:non_conformity,not_received,damaged,service_not_provided,other',
            'description' => 'required|string|min:20|max:2000',
            'evidence' => 'nullable|array|max:5',
            'evidence.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }
}
