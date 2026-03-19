<?php

return [
    // Commission rate taken by platform (percentage)
    // Audit 2.17: aligné sur CommissionService qui utilise get_setting('commission_services', '10')
    'commission_percent' => env('FINANCE_COMMISSION_PERCENT', 10),

    // Minimum commission (optional, 0 means none)
    'commission_min' => env('FINANCE_COMMISSION_MIN', 0),

    // Currency for platform (used for displays)
    'currency' => env('APP_CURRENCY', 'EUR'),
];
