<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http as FacadesHttp;
use Inertia\Response;

class CobrancaController extends Controller
{
    private function buscarCobrancasPendentes()
    {
        return Contrato::query()
            ->with(['cliente:id,nome,telefone', 'movimentacoes'])
            ->withSum('pagamentos as total_pago', 'valor')
            ->where('status', 'ATIVO')
            ->whereDate('proxima_cobranca', '<=', today())
            ->orderBy('proxima_cobranca')
            ->get()
            ->map(function (Contrato $contrato): array {
                $total = $contrato->totalCalculado();
                $totalPago = (float) ($contrato->total_pago ?? 0);

                return [
                    'id' => $contrato->id,
                    'cliente' => [
                        'nome' => $contrato->cliente?->nome ?? 'Cliente não encontrado',
                        'telefone' => $contrato->cliente?->telefone,
                    ],
                    'endereco' => $contrato->endereco,
                    'proxima_cobranca' => $contrato->proxima_cobranca,
                    'pecas_atuais' => $contrato->pecasAtuais(),
                    'total' => round($total, 2),
                    'total_pago' => round($totalPago, 2),
                    'saldo' => max(round($total - $totalPago, 2), 0),
                ];
            });
    }

    public function index(): Response
    {
        $cobrancas = $this->buscarCobrancasPendentes();

        return inertia('Cobrancas/Index', compact('cobrancas'));
    }

    public function confirmar(Request $request, Contrato $contrato): RedirectResponse
    {
        $dados = $request->validate([
            'proxima_cobranca' => ['required', 'date'],
        ]);

        $contrato->update([
            'ultima_cobranca' => today()->toDateString(),
            'proxima_cobranca' => $dados['proxima_cobranca'],
        ]);

        return redirect()
            ->route('cobrancas.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Cobrança confirmada com sucesso',
            ]);
    }

    public function enviarRelatorioWhatsApp()
    {
        // Busca somente cobranças vencidas
        $cobrancas = Contrato::query()
            ->with([
                'cliente:id,nome,telefone',
                'movimentacoes',
            ])
            ->withSum('pagamentos as total_pago', 'valor')
            ->where('status', 'ATIVO')
            ->whereDate('proxima_cobranca', '<', today())
            ->orderBy('proxima_cobranca')
            ->get();

        // Se não houver cobranças vencidas
        if ($cobrancas->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'mensagem' => 'Nenhuma cobrança vencida encontrada.',
            ]);
        }

        // Monta o relatório
        $mensagem = "📋 *COBRANÇAS PENDENTES {$cobrancas->count()}*\n";
        $mensagem .= '📅 '.today()->format('d/m/Y')."\n";
        $mensagem .= "━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($cobrancas as $contrato) {
            $total = $contrato->totalCalculado();

            $totalPago = (float) ($contrato->total_pago ?? 0);

            $pendente = max(
                round($total - $totalPago, 2),
                0
            );

            $diasPendentes = Carbon::parse($contrato->proxima_cobranca)
                ->startOfDay()
                ->diffInDays(today());

            $diasContrato = Carbon::parse($contrato->data_inicio)
                ->startOfDay()
                ->diffInDaysFiltered(
                    fn (Carbon $date) => ! $date->isSunday(),
                    today()->addDay()
                );

            $mensagem .= '👤 *'.($contrato->cliente?->nome ?? 'Cliente não encontrado')."*\n";

            if ($contrato->cliente?->telefone) {
                $mensagem .= '📞 '.$contrato->cliente->telefone."\n";
            }

            $mensagem .= '📄 Contrato: #'.$contrato->id."\n";
            $mensagem .= '📄 Data Inicio: '.Carbon::parse($contrato->data_inicio)->format('d/m/Y')."\n";
            $mensagem .= '📦 Peças atuais: '.$contrato->pecasAtuais()."\n";
            $mensagem .= '📅 Dias de contrato ativo: '.$diasContrato."\n";

            $mensagem .= '📅 Venceu em: '.Carbon::parse($contrato->proxima_cobranca)->format('d/m/Y')."\n";

            $mensagem .= '⏰ Dias pendentes: '.$diasPendentes.($diasPendentes == 1 ? ' dia' : ' dias')."\n";

            $mensagem .= '💰 Total: R$ '.number_format($total, 2, ',', '.')."\n";

            $mensagem .= '💵 Total pago: R$ '.number_format($totalPago, 2, ',', '.')."\n";

            $mensagem .= '🔴 Pendente: R$ '
                .number_format($pendente, 2, ',', '.')
                ."\n";

            if ($contrato->endereco) {
                $mensagem .= '📍 '.$contrato->endereco."\n";
            }

            $mensagem .= "\n━━━━━━━━━━━━━━━━━━\n\n";
        }

        // Números que receberão o relatório
        $numeros = explode(',', env('WAHA_REPORT_PHONES'));

        $resultados = [];

        foreach ($numeros as $numero) {
            $numero = trim($numero);

            // Ignora números vazios
            if ($numero === '') {
                continue;
            }

            $response = FacadesHttp::withHeaders([
                'X-Api-Key' => env('WAHA_API_KEY'),
            ])->post('http://localhost:3000/api/sendText', [
                'chatId' => $numero . '@c.us',
                'text' => $mensagem,
                'session' => env('WAHA_SESSION'),
            ]);

            $resultados[] = [
                'numero' => $numero,
                'status' => $response->status(),
                'sucesso' => $response->successful(),
                'resposta' => $response->json(),
            ];
        }

        // $response = FacadesHttp::withHeaders([
        //     'X-Api-Key' => env('WAHA_API_KEY'),
        // ])->post('http://localhost:3000/api/sendText', [
        //     'chatId' => '559491407933@c.us',
        //     'text' => $mensagem,
        //     'session' => env('WAHA_SESSION'),
        // ]);

        return redirect()
            ->route('cobrancas.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Relatório enviado com sucesso',
            ]);
    }
}
