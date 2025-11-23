<?php

namespace App\Http\Requests\Prestataire;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'description' => 'required|string|max:1000',
            'technical_specifications' => 'nullable|string|max:2000',
            'main_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'required|numeric|min:0.01',
            'price_per_week' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'condition' => 'nullable|in:excellent,very_good,good,fair,poor',
            'status' => 'nullable|in:active,inactive,maintenance,rented',
            'delivery_included' => 'nullable|boolean',
            'available_from' => 'nullable|date|after_or_equal:today',
            'available_until' => 'nullable|date|after:available_from',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'accessories' => 'nullable|string|max:1000',
            'rental_conditions' => 'nullable|string|max:2000',
            'license_required' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'équipement est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'category_id.required' => 'Veuillez sélectionner une catégorie principale.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'subcategory_id.exists' => 'La sous-catégorie sélectionnée n\'existe pas.',
            'description.required' => 'La description est obligatoire.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
            'technical_specifications.max' => 'Les spécifications techniques ne peuvent pas dépasser 2000 caractères.',
            'main_photo.required' => 'Une photo principale est obligatoire.',
            'main_photo.image' => 'Le fichier doit être une image.',
            'main_photo.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou WebP.',
            'main_photo.max' => 'L\'image ne peut pas dépasser 5 Mo.',
            'price_per_hour.numeric' => 'Le prix par heure doit être un nombre.',
            'price_per_hour.min' => 'Le prix par heure ne peut pas être négatif.',
            'price_per_day.required' => 'Le prix par jour est obligatoire.',
            'price_per_day.numeric' => 'Le prix par jour doit être un nombre.',
            'price_per_day.min' => 'Le prix par jour doit être supérieur à 0.',
            'price_per_week.numeric' => 'Le prix par semaine doit être un nombre.',
            'price_per_week.min' => 'Le prix par semaine ne peut pas être négatif.',
            'price_per_month.numeric' => 'Le prix par mois doit être un nombre.',
            'price_per_month.min' => 'Le prix par mois ne peut pas être négatif.',
            'security_deposit.required' => 'La caution est obligatoire.',
            'security_deposit.numeric' => 'La caution doit être un nombre.',
            'security_deposit.min' => 'La caution ne peut pas être négative.',
            'condition.in' => 'L\'état sélectionné n\'est pas valide.',
            'status.in' => 'Le statut sélectionné n\'est pas valide.',
            'available_from.date' => 'La date de début de disponibilité doit être une date valide.',
            'available_from.after_or_equal' => 'La date de début de disponibilité ne peut pas être dans le passé.',
            'available_until.date' => 'La date de fin de disponibilité doit être une date valide.',
            'available_until.after' => 'La date de fin de disponibilité doit être postérieure à la date de début.',
            'address.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'city.required' => 'La ville est obligatoire.',
            'city.max' => 'Le nom de la ville ne peut pas dépasser 255 caractères.',
            'postal_code.max' => 'Le code postal ne peut pas dépasser 20 caractères.',
            'country.required' => 'Le pays est obligatoire.',
            'country.max' => 'Le nom du pays ne peut pas dépasser 255 caractères.',
            'accessories.max' => 'La liste des accessoires ne peut pas dépasser 1000 caractères.',
            'rental_conditions.max' => 'Les conditions de location ne peuvent pas dépasser 2000 caractères.',
        ];
    }

    /**
     * Noms d'attributs personnalisés
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom de l\'équipement',
            'category_id' => 'catégorie',
            'description' => 'description',
            'technical_specifications' => 'spécifications techniques',
            'main_photo' => 'photo principale',
            'price_per_hour' => 'prix par heure',
            'price_per_day' => 'prix par jour',
            'price_per_week' => 'prix par semaine',
            'price_per_month' => 'prix par mois',
            'security_deposit' => 'caution',
            'condition' => 'état',
            'status' => 'statut',
            'delivery_included' => 'livraison incluse',
            'available_from' => 'date de début de disponibilité',
            'available_until' => 'date de fin de disponibilité',
            'address' => 'adresse',
            'city' => 'ville',
            'postal_code' => 'code postal',
            'country' => 'pays',
            'accessories' => 'accessoires',
            'rental_conditions' => 'conditions de location',
            'license_required' => 'permis requis',
            'is_available' => 'disponibilité',
        ];
    }
}
