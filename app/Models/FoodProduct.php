<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
class FoodProduct extends Model
{
    use HasFactory, SoftDeletes;
use App\Support\TableExistenceCache;

    protected static ?bool $supportsAdvanceOrderColumns = null;

    protected $fillable = [
        'prestataire_id',
        'name',
        'description',
        'price',
        'image',
        'category',
        'is_available',
        'is_preorder_only',
        'min_preorder_days',
        'payment_policy',
        'deposit_percent',
        'preparation_time',
        'stock',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_preorder_only' => 'boolean',
        'min_preorder_days' => 'integer',
        'deposit_percent' => 'integer',
        'options' => 'array',
    ];

    // Politiques de paiement
    const PAYMENT_CASH = 'cash';
    const PAYMENT_DEPOSIT = 'deposit';
    const PAYMENT_FULL_PREPAY = 'full_prepay';

    public static function paymentPolicies(): array
    {
        return [
            self::PAYMENT_CASH => 'Espèces (paiement en main propre)',
            self::PAYMENT_DEPOSIT => 'Acompte (% à la commande)',
            self::PAYMENT_FULL_PREPAY => 'Paiement total à la commande',
        ];
    }

    // Catégories disponibles
    public static function categories(): array
    {
        return [
            'entree' => 'Entrées',
            'plat' => 'Plats principaux',
            'dessert' => 'Desserts',
            'boisson' => 'Boissons',
            'amuse_bouche' => 'Amuse-bouches',
            'gateau' => 'Gâteaux',
            'pizza' => 'Pizzas',
            'sandwich' => 'Sandwichs',
            'salade' => 'Salades',
            'autre' => 'Autres',
        ];
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function orderItems()
    {
        return $this->hasMany(FoodOrderItem::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? $this->category;
    }

    public function getImageAttribute($value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        return function_exists('normalize_storage_asset_path')
            ? (normalize_storage_asset_path($value) ?? $value)
            : $value;
    }

    public static function supportsAdvanceOrder(): bool
    {
        if (self::$supportsAdvanceOrderColumns !== null) {
            return self::$supportsAdvanceOrderColumns;
        }

        try {
            self::$supportsAdvanceOrderColumns = TableExistenceCache::has('food_products')
                && Schema::hasColumn('food_products', 'is_preorder_only')
                && Schema::hasColumn('food_products', 'min_preorder_days');
        } catch (\Throwable $e) {
            self::$supportsAdvanceOrderColumns = false;
        }

        return self::$supportsAdvanceOrderColumns;
    }

    public function requiresAdvanceOrder(): bool
    {
        if (!self::supportsAdvanceOrder()) {
            return false;
        }

        return (bool) $this->getAttribute('is_preorder_only')
            && (int) ($this->getAttribute('min_preorder_days') ?? 0) >= 2;
    }

    public function earliestAvailableDate(?CarbonInterface $referenceDate = null): Carbon
    {
        $referenceDate = ($referenceDate ? Carbon::instance($referenceDate) : now())->startOfDay();
        $leadDays = $this->requiresAdvanceOrder() ? max(2, (int) $this->min_preorder_days) : 0;

        return $referenceDate->copy()->addDays($leadDays);
    }

    public function isAvailableForDate($date, ?CarbonInterface $referenceDate = null): bool
    {
        if (!$this->is_available) {
            return false;
        }

        if (!$date) {
            return true;
        }

        try {
            $requestedDate = $date instanceof CarbonInterface
                ? Carbon::instance($date)->startOfDay()
                : Carbon::parse($date)->startOfDay();
        } catch (\Throwable $e) {
            return true;
        }

        return $requestedDate->greaterThanOrEqualTo($this->earliestAvailableDate($referenceDate));
    }

    public function scopeAvailableForRequestedDate(Builder $query, $requestedDate = null, ?CarbonInterface $referenceDate = null): Builder
    {
        $query->where('is_available', true);

        if (!$requestedDate || !self::supportsAdvanceOrder()) {
            return $query;
        }

        try {
            $requestedDate = $requestedDate instanceof CarbonInterface
                ? Carbon::instance($requestedDate)->startOfDay()
                : Carbon::parse($requestedDate)->startOfDay();
        } catch (\Throwable $e) {
            return $query;
        }

        $referenceDate = ($referenceDate ? Carbon::instance($referenceDate) : now())->startOfDay();
        $daysAhead = max(0, $referenceDate->diffInDays($requestedDate, false));

        return $query->where(function (Builder $subQuery) use ($daysAhead) {
            $subQuery->whereNull('is_preorder_only')
                ->orWhere('is_preorder_only', false)
                ->orWhere(function (Builder $preorderQuery) use ($daysAhead) {
                    $preorderQuery->where('is_preorder_only', true)
                        ->where('min_preorder_days', '<=', $daysAhead);
                });
        });
    }

    public function getAdvanceOrderLabelAttribute(): ?string
    {
        if (!$this->requiresAdvanceOrder()) {
            return null;
        }

        $days = (int) $this->min_preorder_days;

        return "Sur commande - {$days} jour" . ($days > 1 ? 's' : '') . ' minimum';
    }

    public function isInStock(): bool
    {
        if ($this->stock === null) {
            return true; // Stock illimité
        }
        return $this->stock > 0;
    }

    public function decrementStock(int $quantity = 1): void
    {
        if ($this->stock !== null) {
            $this->decrement('stock', $quantity);
        }
    }

    public function incrementStock(int $quantity = 1): void
    {
        if ($this->stock !== null) {
            $this->increment('stock', $quantity);
        }
    }
}
