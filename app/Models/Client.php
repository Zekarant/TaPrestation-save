<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo',
        'location',
        'phone',
        'address',
        'bio',
    ];

    /**
     * Get the user that owns the client profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    

    /**
     * Get the prestataires followed by this client.
     */
    public function followedPrestataires()
    {
        return $this->belongsToMany(Prestataire::class, 'client_prestataire_follows', 'client_id', 'prestataire_id')
                    ->withTimestamps();
    }

    /**
     * Alias for followedPrestataires() method.
     */
    public function follows()
    {
        return $this->followedPrestataires();
    }

    /**
     * Get the bookings for this client.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the reviews written by this client.
     * 
     * Note: Dans la table reviews, client_id fait référence à users.id
     * Nous devons donc utiliser la relation via l'utilisateur associé.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'client_id', 'user_id');
    }

    /**
     * Check if this client follows a specific prestataire.
     */
    public function isFollowing($prestataire)
    {
        $prestataireId = $prestataire instanceof Prestataire ? $prestataire->id : $prestataire;
        return $this->followedPrestataires()->where('prestataire_id', $prestataireId)->exists();
    }



    /**
     * Get the client requests (now bookings) for this client.
     */
    public function clientRequests()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the equipment rental requests for this client.
     */
    public function equipmentRentalRequests()
    {
        return $this->hasMany(EquipmentRentalRequest::class);
    }

    /**
     * Get the urgent sale contacts for this client.
     */
    public function urgentSaleContacts()
    {
        return $this->hasMany(UrgentSaleContact::class, 'user_id', 'user_id');
    }
}