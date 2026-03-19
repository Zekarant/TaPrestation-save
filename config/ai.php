<?php

return [
    // Global toggle to enable GPT-5 features for all clients
    'enabled' => env('GPT5_ENABLED', true),

    // Default LLM model identifier
    'model' => env('AI_MODEL', 'gpt-5'),

    // Scope indicates rollout target; kept for clarity/forward-compat
    'scope' => 'all_clients',
];
