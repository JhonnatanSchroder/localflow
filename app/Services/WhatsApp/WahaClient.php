<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaClient
{
    public function sendText(string $chatId, string $text): void
    {
        $chatId = str_contains($chatId, '@') ? $chatId : $chatId.'@c.us';

        try {
            Log::info('WahaClient: preparing sendText', ['chat_id' => $chatId, 'text_preview' => mb_substr($text, 0, 200)]);

            $response = Http::baseUrl(config('whatsapp-agent.waha_url'))
                ->when(config('whatsapp-agent.waha_api_key'), fn ($request, $key) => $request->withHeader('X-Api-Key', $key))
                ->timeout(15)
                ->post('/api/sendText', [
                    'session' => config('whatsapp-agent.session'),
                    'chatId' => $chatId,
                    'text' => $text,
                ]);

            Log::info('WahaClient: response received', ['status' => $response->status(), 'body_preview' => mb_substr($response->body(), 0, 1000)]);

            $response->throw();
        } catch (\Throwable $e) {
            Log::error('WahaClient: error sending text', ['chat_id' => $chatId, 'exception' => $e]);
            throw $e;
        }
    }
}
