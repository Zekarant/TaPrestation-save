<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'file_path',
        'is_active',
        'updated_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Pages légales disponibles
     */
    public static function availablePages(): array
    {
        return [
            'cgu' => 'Conditions Générales d\'Utilisation (CGU)',
            'cgv' => 'Conditions Générales de Vente (CGV)',
            'terms' => 'Conditions d\'utilisation',
            'privacy' => 'Politique de confidentialité',
            'cookies' => 'Politique de cookies',
            'mentions' => 'Mentions légales',
            'faq' => 'FAQ',
            'contact' => 'Contactez-nous',
            'videos' => 'Vidéos',
        ];
    }

    /**
     * Récupérer une page par son slug
     */
    public static function getBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Utilisateur qui a mis à jour la page
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Obtenir le contenu formaté (HTML)
     */
    public function getFormattedContentAttribute(): string
    {
        if (empty($this->content)) {
            return '';
        }

        // Convertir les sauts de ligne en paragraphes
        return nl2br(e($this->content));
    }

    /**
     * Obtenir le contenu HTML (si le contenu est du HTML)
     */
    public function getHtmlContentAttribute(): string
    {
        return $this->content ?? '';
    }
}
