<?php

namespace App\Http\Requests\Prestataire;

use Illuminate\Foundation\Http\FormRequest;

class CancelEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => 'required|string|max:1000'
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Veuillez indiquer la raison de l\'annulation.',
            'cancellation_reason.max' => 'La raison de l\'annulation ne peut pas dépasser 1000 caractères.',
        ];
    }
}
