<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrestataireProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)->whereNull('deleted_at'),
            ],
            'company_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'description' => 'nullable|string',
            'daily_rate' => 'nullable|numeric|min:0|max:9999.99',
            'average_delivery_time' => 'nullable|integer|min:1|max:365',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'secteur_activite' => 'nullable|string|max:255',
            'competences' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Le numéro de téléphone est obligatoire pour recevoir des réservations.',
            'address.required' => 'L\'adresse est obligatoire pour recevoir des réservations.',
        ];
    }
}
