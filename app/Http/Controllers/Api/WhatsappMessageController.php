<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMessage;
use Illuminate\Http\JsonResponse;

class WhatsappMessageController extends Controller
{
    /**
     * Retorna mensagens não processadas para n8n.
     *
     * Usado para polling de novas mensagens
     */
    public function unprocessed(): JsonResponse
    {
        $messages = WhatsappMessage::query()
            ->where('direction', 'in')
            ->where(function ($query) {
                $query->where('processed', false)
                    ->orWhereNull('processed');
            })
            ->latest('created_at')
            ->limit(10)
            ->get([
                'id',
                'message_id',
                'from',
                'chat_id',
                'body',
                'created_at',
            ]);

        return response()->json([
            'status' => 'success',
            'count' => $messages->count(),
            'data' => $messages,
        ]);
    }

    /**
     * Marca uma mensagem como processada.
     *
     * Chamado por n8n após processar a mensagem
     */
    public function markProcessed(int $messageId): JsonResponse
    {
        $message = WhatsappMessage::find($messageId);

        if (! $message) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mensagem não encontrada',
            ], 404);
        }

        $message->update(['processed' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mensagem marcada como processada',
        ]);
    }
}
