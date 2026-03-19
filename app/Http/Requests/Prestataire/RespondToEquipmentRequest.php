<?php

namespace App\Http\Requests\Prestataire;

use Illuminate\Foundation\Http\FormRequest;

class RespondToEquipmentRequest extends FormRequest
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
            'response_message' => 'required|string|max:1000'
        ];
    }

    public function messages(): array
    {
        return [
            'response_message.required' => 'Veuillez saisir votre message de réponse.',
            'response_message.max' => 'Le message ne peut pas dépasser 1000 caractères.',
        ];
    }
}
