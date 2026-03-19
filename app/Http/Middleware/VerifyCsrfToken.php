<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // SÉCURITÉ H4: CSRF réactivé sur login/register pour empêcher les attaques Login CSRF
        // Seuls les webhooks externes (Stripe) doivent être exemptés de CSRF
        'stripe/webhook',
        'api/stripe/webhook',
    ];
}