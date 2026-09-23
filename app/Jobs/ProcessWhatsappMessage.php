<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Services\WhatsApp\AgentService;
use App\Services\WhatsApp\WahaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $messageId) {}

    public function handle(AgentService $agent, WahaClient $waha): void
    {
        $message = WhatsappMessage::find($this->messageId);

        if (! $message) {
            Log::info('ProcessWhatsappMessage: message not found', ['message_id' => $this->messageId]);

            return;
        }

        $allowedNumbers = config('whatsapp-agent.allowed_numbers');
        $allowedChatIds = config('whatsapp-agent.allowed_chat_ids');

        $allowedNumbers = is_array($allowedNumbers) ? $allowedNumbers : [];
        $allowedChatIds = is_array($allowedChatIds) ? $allowedChatIds : [];

        $isAllowedNumber = in_array($message->from, $allowedNumbers, true);
        $isAllowedChat = $message->chat_id
            && in_array(strtolower($message->chat_id), $allowedChatIds, true);

        if (! $isAllowedNumber && ! $isAllowedChat) {
            Log::info('ProcessWhatsappMessage: ignored unauthorized chat', [
                'message_id' => $this->messageId,
                'from' => $message->from,
                'chat_id' => $message->chat_id,
            ]);

            return;
        }

        try {
            Log::info('ProcessWhatsappMessage: calling agent->reply', ['message_id' => $message->id, 'from' => $message->from]);
            $answer = $agent->reply($message->from, $message->body);
            Log::info('ProcessWhatsappMessage: agent reply received', ['message_id' => $message->id, 'answer_preview' => mb_substr($answer, 0, 200)]);

            $chatId = $message->chat_id ?: $message->from;
            Log::info('ProcessWhatsappMessage: sending via WahaClient', ['message_id' => $message->id, 'chat_id' => $chatId]);
            $waha->sendText($chatId, $answer);
            Log::info('ProcessWhatsappMessage: sendText completed', ['message_id' => $message->id]);

            WhatsappMessage::create([
                'message_id' => 'out-'.$message->id,
                'from' => $message->from,
                'chat_id' => $message->chat_id,
                'body' => $answer,
                'direction' => 'out',
            ]);
            Log::info('ProcessWhatsappMessage: outbound message recorded', ['message_id' => 'out-'.$message->id]);
        } catch (\Throwable $exception) {
            Log::error('Erro no agente WhatsApp', ['message_id' => $message->id, 'exception' => $exception]);
            throw $exception;
        }
    }
}
