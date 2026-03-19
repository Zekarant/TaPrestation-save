<?php

return [
    // Enable/disable verification (safe default: off if keys not set)
    'enabled' => (bool) env('RECAPTCHA_ENABLED', false),

    // reCAPTCHA v3 keys
    'site_key' => (string) env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => (string) env('RECAPTCHA_SECRET_KEY', ''),

    // Score threshold for v3 (0.0 - 1.0)
    'score_threshold' => (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
];
