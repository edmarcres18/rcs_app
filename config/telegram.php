<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the Telegram bot integration.
    |
    */

    // Bot API Token
    'api_token' => env('TELEGRAM_BOT_API', ''),

    // Webhook URL (for production)
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL', ''),

    // Bot Command Responses
    'commands' => [
        'start' => 'Good Success! Welcome to MHR Reporting Compliance System Notifications',
        'help' => "Available commands:\n/start - Get started\n/help - Show this message\n/link [email] - Link your account\n/status - Check your account status",
        'unknown' => 'Unknown command. Type /help to see available commands.',
    ],

    // Define chat IDs that should receive admin notifications
    'admin_chat_ids' => array_filter(explode(',', env('TELEGRAM_ADMIN_CHAT_IDS', ''))),

    // Enable/disable debug mode
    'debug' => (bool) env('TELEGRAM_DEBUG', false),

    // Local Development Settings
    'local' => [
        // Whether to use polling instead of webhooks in local environment
        'use_polling' => env('TELEGRAM_LOCAL_USE_POLLING', true),

        // ngrok settings for local webhook testing
        'ngrok' => [
            'enabled' => env('TELEGRAM_USE_NGROK', false),
            'url' => env('TELEGRAM_NGROK_URL', ''),
            'api_url' => env('TELEGRAM_NGROK_API_URL', 'http://127.0.0.1:4040/api/tunnels'),
        ],
    ],

    // Notification Settings
    'notifications' => [
        // Which types of notifications to send via Telegram
        'instruction_assigned' => env('TELEGRAM_NOTIFY_INSTRUCTION_ASSIGNED', true),
        'instruction_replied' => env('TELEGRAM_NOTIFY_INSTRUCTION_REPLIED', true),
        'deadline_reminder' => env('TELEGRAM_NOTIFY_DEADLINE_REMINDER', true),
    ],

    // Bot Command Settings
    'bot_commands' => [
        'start' => [
            'description' => 'Start interacting with the bot',
        ],
        'help' => [
            'description' => 'Show available commands',
        ],
        'link' => [
            'description' => 'Link your Telegram with your MHR account',
            'params' => '[email]',
        ],
        'status' => [
            'description' => 'Check your account linking status',
        ],
    ],
];
