<?php

namespace App\Services\WhatsApp;

use App\Models\AgentAudit;
use App\Models\Cliente;
use App\Models\Contrato;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\WhatsappConversation;

class AgentService
{
    private const MAX_TOOL_ROUNDS = 4;

private function conversation(string $phone): WhatsappConversation
{
    return WhatsappConversation::firstOrCreate(
        ['phone' => $phone],
        [
            'pending_action' => null,
            'pending_data' => null,
            'pending_at' => null,
        ]
    );
}

private function setPendingConfirmation(
    string $phone,
    string $action,
    array $data
): void {
    $conversation = $this->conversation($phone);

    $conversation->update([
        'pending_action' => $action,
        'pending_data' => $data,
        'pending_at' => now(),
    ]);
}

private function clearPendingConfirmation(string $phone): void
{
    $this->conversation($phone)->clearPendingAction();
}

private function isConfirmationYes(string $message): bool
{
    $message = mb_strtolower(trim($message));

    return in_array($message, [
        'sim',
        's',
        'confirmo',
        'confirmado',
        'pode',
        'pode sim',
        'pode fazer',
        'ok',
        'okay',
        'beleza',
        'blz',
        'isso',
        'isso mesmo',
        'correto',
        'pode registrar',
        'pode lançar',
    ], true);
}

private function isConfirmationNo(string $message): bool
{
    $message = mb_strtolower(trim($message));

    return in_array($message, [
        'não',
        'nao',
        'n',
        'cancela',
        'cancelar',
        'cancelado',
        'deixa',
        'deixa pra lá',
        'deixa pra la',
        'não quero',
        'nao quero',
    ], true);
}


    public function reply(string $phone, string $message): string
    {
        abort_if(! config('whatsapp-agent.openai_key'), 503, 'OPENAI_API_KEY não configurada.');

        // Try quick local handlers for common commands before calling OpenAI
        $quick = $this->quickReply($message);
        if ($quick !== null) {
            return $quick;
        }

        $input = $message;
        $previousResponseId = null;

        $maxAttempts = 3;
        $attempt = 0;

        for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
            $payload = [
                'model' => config('whatsapp-agent.openai_model'),
                'instructions' => $this->instructions(),
                'tools' => $this->tools(),
                'input' => $input,
            ];
            if ($previousResponseId) {
                $payload['previous_response_id'] = $previousResponseId;
            }

            // call OpenAI with simple retry/backoff for transient errors
            $response = null;
            $lastException = null;
            while ($attempt < $maxAttempts) {
                try {
                    Log::info('AgentService: calling OpenAI', ['phone' => $phone, 'round' => $round, 'attempt' => $attempt + 1]);
                    $resp = Http::withToken(config('whatsapp-agent.openai_key'))
                        ->acceptJson()
                        ->timeout((int) config('whatsapp-agent.openai_timeout', 60))
                        ->post('https://api.openai.com/v1/responses', $payload);

                    $response = $resp->successful() ? $resp->json() : null;
                    Log::info('AgentService: OpenAI response', ['status' => $resp->status(), 'phone' => $phone]);
                    break;
                } catch (\Throwable $e) {
                    $lastException = $e;
                    Log::warning('AgentService: OpenAI call failed', ['exception' => $e, 'phone' => $phone, 'attempt' => $attempt + 1]);
                    $attempt++;
                    usleep(200000 * $attempt);
                }
            }

            if (! is_array($response)) {
                Log::error('AgentService: OpenAI did not return valid JSON', ['phone' => $phone]);
                if ($lastException) {
                    Log::error('AgentService: last exception', ['exception' => $lastException]);
                }

                return 'Desculpe, o serviço de IA está temporariamente indisponível. Tente novamente mais tarde.';
            }

            // audit the OpenAI call
            try {
                AgentAudit::create([
                    'phone' => $phone,
                    'action' => 'openai_call',
                    'arguments' => $payload,
                    'result' => $response,
                ]);
            } catch (\Throwable $e) {
                Log::warning('AgentService: failed to record audit', ['exception' => $e]);
            }

            $output = $response['output'] ?? [];
            // normalize older `output_text` field if present
            if (empty($output) && isset($response['output_text'])) {
                $output = [['type' => 'message', 'text' => $response['output_text']]];
            }

            $calls = collect($output)
                ->where('type', 'function_call')
                ->values();

            if ($calls->isEmpty()) {
                $text = trim($this->responseText($response, $output));
                $text = $text !== '' ? $text : 'Não consegui gerar uma resposta agora.';

                return mb_substr($text, 0, (int) config('whatsapp-agent.max_reply_chars', 3000));
            }

            $input = $calls->map(function (array $call) use ($phone): array {
                try {
                    $arguments = json_decode((string) ($call['arguments'] ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
                    $result = $this->executeTool($phone, $call['name'] ?? '', $arguments);
                } catch (\Throwable $exception) {
                    $result = ['erro' => $exception instanceof ValidationException
                        ? implode(' ', Arr::flatten($exception->errors()))
                        : 'Não foi possível executar esta operação.'];
                }

                return [
                    'type' => 'function_call_output',
                    'call_id' => $call['call_id'] ?? null,
                    'output' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            })->all();
            $previousResponseId = $response['id'] ?? $previousResponseId;
        }

        return 'Não consegui concluir a solicitação. Tente novamente com mais detalhes.';
    }

    /** @return array<int, array<string, mixed>> */
    private function tools(): array
    {
        return [
            $this->tool('consultar_contratos', 'Lista contratos, com valores e peças em aberto.', [
                'nome_cliente' => ['type' => 'string'],
                'status' => ['type' => 'string', 'enum' => ['ATIVO', 'DEVOLVIDO', 'FINALIZADO']],
            ]),
            $this->tool('consultar_cliente', 'Busca um cliente pelo nome ou telefone.', [
                'busca' => ['type' => 'string'],
            ], ['busca']),
            $this->tool('registrar_movimentacao', 'Registra retirada ou devolução em contrato existente.', [
                'contrato_id' => ['type' => 'integer'],
                'tipo' => ['type' => 'string', 'enum' => ['RETIRADA', 'DEVOLUCAO']],
                'qtd' => ['type' => 'integer', 'minimum' => 1],
                'data' => ['type' => 'string', 'description' => 'Data em YYYY-MM-DD; omita para hoje.'],
            ], ['contrato_id', 'tipo', 'qtd']),
            $this->tool('registrar_pagamento', 'Registra um pagamento em contrato existente.', [
                'contrato_id' => ['type' => 'integer'],
                'valor' => ['type' => 'number', 'exclusiveMinimum' => 0],
                'data' => ['type' => 'string', 'description' => 'Data em YYYY-MM-DD; omita para hoje.'],
            ], ['contrato_id', 'valor']),
        ];
    }

    /** @param array<string, array<string, mixed>> $properties @param array<int, string> $required */
    private function tool(string $name, string $description, array $properties, array $required = []): array
    {
        return [
            'type' => 'function',
            'name' => $name,
            'description' => $description,
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
                'additionalProperties' => false,
            ],
            'strict' => false,
        ];
    }

    /** @param array<string, mixed> $arguments @return array<string, mixed> */
    private function executeTool(string $phone, string $name, array $arguments): array
    {
        return match ($name) {
            'consultar_contratos' => $this->contracts($arguments),
            'consultar_cliente' => $this->client($arguments),
            'registrar_movimentacao' => $this->movement($phone, $arguments),
            'registrar_pagamento' => $this->payment($phone, $arguments),
            default => throw ValidationException::withMessages(['ferramenta' => 'Ferramenta não permitida.']),
        };
    }

    /** @param array<string, mixed> $filters @return array<string, mixed> */
    private function contracts(array $filters): array
    {
        $contracts = Contrato::query()->with('cliente:id,nome,telefone')->latest('id');
        if (! empty($filters['nome_cliente'])) {
            $contracts->whereHas('cliente', fn ($query) => $query->where('nome', 'like', '%'.$filters['nome_cliente'].'%'));
        }
        if (! empty($filters['status'])) {
            $contracts->where('status', $filters['status']);
        }

        return ['contratos' => $contracts->limit(10)->get()->map(function (Contrato $contract): array {
            $contract->sincronizarStatus();

            return $this->contractData($contract);
        })->all()];
    }

    /** @param array<string, mixed> $arguments @return array<string, mixed> */
    private function client(array $arguments): array
    {
        $search = (string) ($arguments['busca'] ?? '');
        if ($search === '') {
            throw ValidationException::withMessages(['busca' => 'Informe o cliente a buscar.']);
        }

        $digits = preg_replace('/\D/', '', $search);

        return ['clientes' => Cliente::query()
            ->where(function ($query) use ($search, $digits): void {
                $query->where('nome', 'like', '%'.$search.'%');

                if ($digits !== '') {
                    $query->orWhere('telefone', 'like', '%'.$digits.'%');
                }
            })
            ->limit(10)->get(['id', 'nome', 'telefone', 'endereco', 'status'])->all()];
    }

    /** @param array<string, mixed> $arguments @return array<string, mixed> */
    private function movement(string $phone, array $arguments): array
    {
        $this->validateDate($arguments['data'] ?? null);
        if (! isset($arguments['contrato_id'], $arguments['tipo'], $arguments['qtd']) || (int) $arguments['qtd'] < 1) {
            throw ValidationException::withMessages(['dados' => 'Contrato, tipo e quantidade válida são obrigatórios.']);
        }

        $contract = DB::transaction(function () use ($arguments): Contrato {
            $contract = Contrato::lockForUpdate()->findOrFail((int) $arguments['contrato_id']);
            $quantity = (int) $arguments['qtd'];
            if ($arguments['tipo'] === 'DEVOLUCAO' && $quantity > $contract->pecasAtuais()) {
                throw ValidationException::withMessages(['qtd' => 'A devolução supera a quantidade de peças em aberto.']);
            }
            $contract->movimentacoes()->create([
                'data' => $arguments['data'] ?? now()->toDateString(),
                'tipo' => $arguments['tipo'],
                'qtd' => $quantity,
            ]);
            $contract->sincronizarStatus();

            return $contract->fresh();
        });

        return $this->audit($phone, 'registrar_movimentacao', $arguments, $this->contractData($contract));
    }

    /** @param array<string, mixed> $arguments @return array<string, mixed> */
    private function payment(string $phone, array $arguments): array
    {
        $this->validateDate($arguments['data'] ?? null);
        if (! isset($arguments['contrato_id'], $arguments['valor']) || (float) $arguments['valor'] <= 0) {
            throw ValidationException::withMessages(['dados' => 'Contrato e valor positivo são obrigatórios.']);
        }

        $contract = DB::transaction(function () use ($arguments): Contrato {
            $contract = Contrato::lockForUpdate()->findOrFail((int) $arguments['contrato_id']);
            $contract->pagamentos()->create([
                'data' => $arguments['data'] ?? now()->toDateString(),
                'valor' => round((float) $arguments['valor'], 2),
            ]);
            $contract->sincronizarStatus();

            return $contract->fresh();
        });

        return $this->audit($phone, 'registrar_pagamento', $arguments, $this->contractData($contract));
    }

    private function validateDate(mixed $date): void
    {
        if ($date !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            throw ValidationException::withMessages(['data' => 'Use a data no formato YYYY-MM-DD.']);
        }
    }

    /** @param array<string, mixed> $arguments @param array<string, mixed> $result @return array<string, mixed> */
    private function audit(string $phone, string $action, array $arguments, array $result): array
    {
        AgentAudit::create(compact('phone', 'action', 'arguments', 'result'));

        return $result;
    }

    /** @return array<string, mixed> */
    private function contractData(Contrato $contract): array
    {
        return [
            'id' => $contract->id,
            'cliente' => $contract->cliente?->nome,
            'endereco' => $contract->endereco,
            'status' => $contract->status,
            'pecas_atuais' => $contract->pecasAtuais(),
            'total' => round($contract->totalCalculado(), 2),
            'total_pago' => round((float) $contract->pagamentos()->sum('valor'), 2),
        ];
    }

    private function instructions(): string
    {
        return <<<'TEXT'
Você é um assistente administrativo especializado em gestão de contratos e clientes.

REGRAS OBRIGATÓRIAS:
1. Sempre responda em português brasileiro, de forma CONCISA (máximo 2 linhas quando possível).
2. NÃO use ferramentas desnecessariamente — responda direto quando souber.
3. Para informações de sistema, use as ferramentas APENAS quando necessário.
4. Confirme SEMPRE antes de registrar pagamento/devolução/retirada: ID do contrato, valor/qtd, data.
5. NUNCA invente IDs, valores, datas ou resultados.
6. Se não sabe, diga "Desculpa, não consegui encontrar essa informação".

OPERAÇÕES PERMITIDAS:
- Consultar: contratos (por cliente/status), clientes (por nome/telefone)
- Registrar: movimentações (retirada/devolução), pagamentos
- NÃO permitido: editar/deletar clientes ou contratos

EXEMPLOS:
- "Quanto devo?" → Busca contratos do cliente e calcula
- "Quando vence?" → Retorna próxima data de cobrança
- "Confirma pagamento de R$500?" → Pede confirmação antes de registrar
- "Quais contratos temos?" → Lista contratos ativos

IMPORTANTE: Mantenha as respostas curtas, diretas e úteis.
TEXT;
    }

    /** @param array<string, mixed> $response @param array<int, mixed> $output */
    private function responseText(array $response, array $output): string
    {
        $outputText = $response['output_text'] ?? null;
        if (is_string($outputText) && $outputText !== '') {
            return $outputText;
        }

        return collect($output)
            ->flatMap(function (mixed $item): array {
                if (! is_array($item)) {
                    return [];
                }

                $text = $item['text'] ?? null;
                if (is_string($text) && $text !== '') {
                    return [$text];
                }

                $content = $item['content'] ?? null;
                if (! is_array($content)) {
                    return [];
                }

                return collect($content)
                    ->map(fn (mixed $part): ?string => is_array($part) && is_string($part['text'] ?? null) ? $part['text'] : null)
                    ->filter()
                    ->all();
            })
            ->filter()
            ->implode("\n");
    }

    /**
     * Atalhos locais para as 80% de perguntas mais comuns.
     * Responde aqui evita latência de chamar OpenAI.
     */
    private function quickReply(string $message): ?string
    {
        $text = mb_strtolower(trim($message));

        // === LISTAR CONTRATOS ===
        if (preg_match('/^(?:listar\s+)?contratos(?:\s+ativos)?$/u', $text)) {
            $data = $this->contracts(['status' => 'ATIVO']);
            $lines = collect($data['contratos'])->map(function ($c) {
                return "[#{$c['id']}] {$c['cliente']} — R$ ".number_format($c['total'], 2, ',', '.')." — Peças: {$c['pecas_atuais']}";
            })->all();

            return empty($lines) ? 'Não há contratos ativos.' : implode("\n", array_slice($lines, 0, 8));
        }

        // === BUSCAR CLIENTE ===
        $term = null;
        if (preg_match('/clientes?\s*:\s*(.+)$/ui', trim($message), $matches)) {
            $term = trim($matches[1]);
        } elseif (preg_match('/(?:buscar|dados|me traga|traga|procura|procure|quem é).*?clientes?\s+(.+)$/ui', trim($message), $matches)) {
            $term = trim($matches[1]);
        } elseif (preg_match('/^\+?[\d\s().-]{8,}$/', trim($message))) {
            $term = trim($message);
        }

        if ($term !== null && $term !== '') {
            $result = $this->client(['busca' => $term]);
            if (empty($result['clientes'])) {
                return 'Nenhum cliente encontrado.';
            }

            $lines = collect($result['clientes'])->map(function ($c) {
                return "[#{$c['id']}] {$c['nome']} — {$c['telefone']} — {$c['endereco']}";
            })->all();

            return implode("\n", array_slice($lines, 0, 5));
        }

        // === CONTRATOS DE UM CLIENTE (por nome) ===
        if (preg_match('/contratos?\s+(?:de|do)\s+(.+?)(?:\s+(?:status|ativo|bloqueado))?$/ui', trim($message), $matches)) {
            $name = trim($matches[1]);
            $data = $this->contracts(['nome_cliente' => $name]);
            if (empty($data['contratos'])) {
                return "Nenhum contrato encontrado para {$name}.";
            }

            $lines = collect($data['contratos'])->map(function ($c) {
                return "[#{$c['id']}] {$c['cliente']} — R$ ".number_format($c['total'], 2, ',', '.')." — {$c['status']}";
            })->all();

            return implode("\n", array_slice($lines, 0, 5));
        }

        return null;
    }
}
