<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'title' => 'required|string',
            'description' => 'required|string',
            'delivery_time' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'nullable|string|in:fixe,heure,jour,projet,devis',
            'quantity' => 'nullable|integer|min:1',
            'estimated_duration' => 'nullable|integer|min:1|max:999',
            'duration_unit' => 'nullable|string|in:minutes,hours,days',
            'buffer_time' => 'nullable|integer|min:0|max:120',
            'deposit_percentage' => 'nullable|integer|min:0|max:100',
            'payment_requirement' => 'nullable|string|in:none,deposit,full',
            'cancellation_hours' => 'nullable|integer|min:0|max:720',
            'cancellation_refund_percentage' => 'nullable|numeric|min:0|max:100',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,bmp,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre du service est obligatoire. Veuillez saisir un titre descriptif.',
            'description.required' => 'La description est obligatoire. Décrivez votre service en détail.',
            'delivery_time.integer' => 'Le délai de livraison doit être un nombre entier.',
            'delivery_time.min' => 'Le délai de livraison ne peut pas être négatif.',
            'price.numeric' => 'Le prix doit être un nombre valide (ex: 25.50).',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'price_type.in' => 'Le type de prix sélectionné est invalide. Choisissez parmi : fixe, heure, jour, projet ou devis.',
            'quantity.integer' => 'La quantité doit être un nombre entier.',
            'quantity.min' => 'La quantité doit être d\'au moins 1.',
            'estimated_duration.integer' => 'La durée estimée doit être un nombre entier.',
            'estimated_duration.min' => 'La durée estimée doit être d\'au moins 1.',
            'estimated_duration.max' => 'La durée estimée ne peut pas dépasser 999.',
            'duration_unit.in' => 'L\'unité de durée est invalide. Choisissez parmi : minutes, heures ou jours.',
            'buffer_time.integer' => 'Le temps tampon doit être un nombre entier.',
            'buffer_time.min' => 'Le temps tampon ne peut pas être négatif.',
            'buffer_time.max' => 'Le temps tampon ne peut pas dépasser 120 minutes.',
            'deposit_percentage.min' => 'Le pourcentage d\'acompte ne peut pas être négatif.',
            'deposit_percentage.max' => 'Le pourcentage d\'acompte ne peut pas dépasser 100%.',
            'payment_requirement.in' => 'L\'exigence de paiement sélectionnée est invalide.',
            'address.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',
            'city.max' => 'La ville ne peut pas dépasser 255 caractères.',
            'postal_code.max' => 'Le code postal ne peut pas dépasser 10 caractères.',
            'latitude.numeric' => 'La latitude doit être un nombre valide.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.numeric' => 'La longitude doit être un nombre valide.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'category_id.required' => 'Veuillez sélectionner une catégorie pour votre service.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'subcategory_id.exists' => 'La sous-catégorie sélectionnée n\'existe pas.',
            'images.max' => 'Vous ne pouvez pas ajouter plus de 5 images.',
            'images.*.image' => 'Chaque fichier doit être une image valide.',
            'images.*.mimes' => 'Les images doivent être au format JPEG, PNG, JPG, GIF, BMP ou WebP.',
        ];
    }
}
