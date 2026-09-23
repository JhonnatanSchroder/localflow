<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * Chama o agente de IA para responder uma mensagem.
     *
     * Usado por n8n para integração com WhatsApp
     */
    public function reply(Request $request, AgentService $agentService): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        try {
            $resposta = $agentService->reply(
                $validated['phone'],
                $validated['message']
            );

            return response()->json([
                'status' => 'success',
                'resposta' => $resposta,
                'phone' => $validated['phone'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar mensagem: '.$e->getMessage(),
            ], 500);
        }
    }
}
