<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'nullable|string',
            'payment_type' => 'required|in:full,deposit,balance',
            'provider' => 'nullable|in:stripe',
            'terms_version' => 'nullable|string',
            'terms_accepted_at' => 'nullable|date',
        ];
    }
}
