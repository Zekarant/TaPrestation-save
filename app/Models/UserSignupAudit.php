<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSignupAudit extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
        'accept_language',
        'clicks',
        'keypresses',
        'time_to_submit_ms',
        'recaptcha_version',
        'recaptcha_action',
        'recaptcha_score',
        'recaptcha_success',
        'recaptcha_error_codes',
    ];

    protected $casts = [
        'recaptcha_success' => 'boolean',
        'recaptcha_score' => 'float',
        'recaptcha_error_codes' => 'array',
    ];
}
