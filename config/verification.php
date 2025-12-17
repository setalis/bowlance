<?php

return [
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    'code_expires_minutes' => env('VERIFICATION_CODE_EXPIRES_MINUTES', 10),

    'max_attempts' => env('VERIFICATION_MAX_ATTEMPTS', 3),
];
