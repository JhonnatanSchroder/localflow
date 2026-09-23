<?php

return [
    'waha_url' => rtrim((string) env('WAHA_URL', 'http://localhost:3000'), '/'),
    'waha_api_key' => env('WAHA_API_KEY'),
    'session' => env('WAHA_SESSION', 'default'),
    'webhook_secret' => env('WAHA_WEBHOOK_SECRET'),
    'openai_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
    // Telefones em formato internacional, separados por vírgula. Ex.: 5511999999999
    'allowed_numbers' => array_values(array_filter(array_map(
        static fn (string $number): string => preg_replace('/\D/', '', $number),
        explode(',', (string) env('WHATSAPP_AGENT_ALLOWED_NUMBERS', '')),
    ))),
    'allowed_chat_ids' => array_values(array_filter(array_map(
        static fn (string $chatId): string => strtolower(trim($chatId)),
        explode(',', (string) env('WHATSAPP_AGENT_ALLOWED_CHAT_IDS', '')),
    ))),
];
