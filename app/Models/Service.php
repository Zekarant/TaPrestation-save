<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prestataire_id',
        'title',
        'description',
        'price',
        'price_type',
        'quantity',
        'duration',
        'delivery_time',
        'status',
        'reservable',
        'city',
        'postal_code',
        'address',
        'latitude',
        'longitude',
    ];

    /**
     * Get the prestataire that owns the service.
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * The categories that belong to the service.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'service_category');
    }

    /**
     * Get the primary category for the service.
     */
    public function category()
    {
        return $this->belongsToMany(Category::class, 'service_category')->limit(1);
    }

    /**
     * Get the reviews for the service.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the bookings for the service.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    /**
     * Get the images for the service.
     */
    public function images()
    {
        return $this->hasMany(ServiceImage::class)->orderBy('order');
    }

    /**
     * Get the first image for the service (cover image).
     */
    public function coverImage()
    {
        return $this->hasOne(ServiceImage::class)->orderBy('order');
    }

    /**
     * Get the name attribute (alias for title).
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->title;
    }

    /**
     * Get the badge color for the delivery time.
     *
     * @return string
     */
    public function getDeliveryTimeBadgeColorAttribute()
    {
        $time = strtolower($this->delivery_time);

        if (str_contains($time, 'jour')) {
            return 'bg-blue-200 text-blue-800';
        } elseif (str_contains($time, 'semaine')) {
            return 'bg-orange-200 text-orange-800';
        } elseif (str_contains($time, 'mois')) {
            return 'bg-green-200 text-green-800';
        }

        return 'bg-gray-200 text-gray-800';
    }
}