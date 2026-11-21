<?php

return [
    // Batas harian try out per user (PRD F4).
    'tryout_daily_attempts' => (int) env('TRYOUT_DAILY_ATTEMPTS', 3),

    // Batas pertanyaan Ruka per user per hari (PRD F7).
    'ruka_daily_limit' => (int) env('RUKA_DAILY_LIMIT', 30),

    // Password akun dummy; hanya dibaca oleh seeder.
    'seed_password' => env('SEED_PASSWORD'),

    'ai' => [
        'provider' => env('AI_PROVIDER', 'fake'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL'),
    ],
];
