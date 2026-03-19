<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_type' => 'required|in:full,deposit,balance',
            'terms_version' => 'nullable|string',
            'terms_accepted_at' => 'nullable|date',
        ];
    }
}
