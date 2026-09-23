<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessWhatsappMessage;
use App\Models\WhatsappMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WahaWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('whatsapp-agent.webhook_secret');
        $providedSecret = (string) ($request->header('X-WAHA-Secret')
            ?: $request->bearerToken()
            ?: $request->query('secret'));

        abort_if($secret === '' || ! hash_equals($secret, $providedSecret), 401);

        $event = (string) $request->input('event', 'message');
        $message = $request->input('payload', $request->all());
        $fromMe = (bool) Arr::get($message, 'fromMe', false);
        $body = trim((string) (Arr::get($message, 'body') ?: Arr::get($message, 'text.body')));
        $chatId = strtolower(trim((string) (Arr::get($message, 'from') ?: Arr::get($message, 'chatId'))));
        $from = preg_replace('/\D/', '', $chatId);

        if ($event !== 'message' || $fromMe || $body === '' || $from === '' || str_ends_with($chatId, '@g.us')) {
            return response()->json(['accepted' => false]);
        }

        $messageId = (string) (Arr::get($message, 'id._serialized') ?: Arr::get($message, 'id') ?: sha1($from.$body.Arr::get($message, 'timestamp')));
        $incoming = WhatsappMessage::firstOrCreate(
            ['message_id' => $messageId],
            ['from' => $from, 'chat_id' => $chatId, 'body' => $body, 'direction' => 'in'],
        );

        if ($incoming->wasRecentlyCreated) {
            ProcessWhatsappMessage::dispatch($incoming->id);
        }

        return response()->json(['accepted' => true], 202);
    }
}
