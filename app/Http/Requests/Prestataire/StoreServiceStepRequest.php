<?php

namespace App\Http\Requests\Prestataire;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'price_type' => 'required|in:fixed,hourly,daily,quote',
            'price' => $this->input('price_type') === 'quote'
                ? 'nullable|numeric|min:0'
                : 'required|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'max_participants' => 'nullable|integer|min:1',
            'location_type' => 'nullable|in:client_location,provider_location,online',
            'radius' => 'nullable|integer|min:0',
            'availability_id' => 'nullable|exists:availabilities,id',
            'equipment_ids' => 'nullable|array',
            'equipment_ids.*' => 'exists:equipment,id',
            'deposit_percentage' => 'nullable|integer|min:0|max:100',
            'payment_requirement' => 'nullable|in:none,deposit,full',
            'cancellation_hours' => 'nullable|integer|min:0|max:720',
            'cancellation_refund_percentage' => 'nullable|numeric|min:0|max:100',
            'estimated_duration' => 'nullable|integer|min:1|max:999',
            'duration_unit' => 'nullable|in:minutes,hours,days',
            'buffer_time' => 'nullable|integer|min:0|max:120',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'La catégorie est obligatoire.',
            'category_id.exists' => 'La catégorie sélectionnée est invalide.',
            'price_type.required' => 'Le type de prix est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être supérieur ou égal à :min.',
            'duration.integer' => 'La durée doit être un nombre entier.',
            'duration.min' => 'La durée doit être d\'au moins :min.',
            'max_participants.min' => 'Le nombre de participants doit être d\'au moins :min.',
            'estimated_duration.integer' => 'La durée estimée doit être un nombre entier.',
            'estimated_duration.min' => 'La durée estimée doit être d\'au moins :min.',
            'estimated_duration.max' => 'La durée estimée ne peut pas dépasser :max.',
            'buffer_time.min' => 'Le temps tampon doit être d\'au moins :min minutes.',
            'buffer_time.max' => 'Le temps tampon ne peut pas dépasser :max minutes.',
            'deposit_percentage.min' => 'Le pourcentage d\'acompte doit être d\'au moins :min.',
            'deposit_percentage.max' => 'Le pourcentage d\'acompte ne peut pas dépasser :max.',
        ];
    }
}
