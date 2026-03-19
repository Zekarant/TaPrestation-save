<?php

namespace App\Http\Requests\Prestataire\UrgentSales;

use Illuminate\Foundation\Http\FormRequest;

class StoreStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'address.required' => 'L\'adresse est obligatoire. Veuillez saisir une adresse valide.',
            'address.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',
            'city.required' => 'La ville est obligatoire.',
            'city.max' => 'Le nom de la ville ne peut pas dépasser 100 caractères.',
            'postal_code.required' => 'Le code postal est obligatoire.',
            'postal_code.max' => 'Le code postal ne peut pas dépasser 20 caractères.',
            'latitude.required' => 'La géolocalisation est nécessaire. Veuillez sélectionner une adresse sur la carte.',
            'latitude.between' => 'La position géographique est invalide.',
            'longitude.required' => 'La géolocalisation est nécessaire. Veuillez sélectionner une adresse sur la carte.',
            'longitude.between' => 'La position géographique est invalide.',
        ];
    }
}