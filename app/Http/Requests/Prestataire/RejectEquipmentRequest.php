<?php

namespace App\Http\Requests\Prestataire;

use Illuminate\Foundation\Http\FormRequest;

class RejectEquipmentRequest extends FormRequest
{
    protected $errorBag = 'rejectRequest';
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
            'rejection_reason' => 'required|string|max:1000'
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Veuillez indiquer la raison du refus.',
            'rejection_reason.max' => 'La raison du refus ne peut pas dépasser 1000 caractères.',
        ];
    }
}
