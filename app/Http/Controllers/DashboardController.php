<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Movimentacao;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $totalClients = Cliente::query()->count();
        $totalContracts = Contrato::query()->count();
        $activeContracts = Contrato::query()->where('status', 'ATIVO')->count();

        $paymentsThisMonth = Pagamento::query()
            ->whereBetween('data', [$startOfMonth, $endOfMonth])
            ->count();

        $revenueThisMonth = (float) Pagamento::query()
            ->whereBetween('data', [$startOfMonth, $endOfMonth])
            ->sum('valor');

        $piecesInCirculation = (int) Movimentacao::query()
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'RETIRADA' THEN qtd WHEN tipo = 'DEVOLUCAO' THEN -qtd ELSE 0 END),0) as total")
            ->value('total');

        $overdueContracts = Contrato::query()
            ->whereDate('proxima_cobranca', '<', Carbon::now()->toDateString())
            ->where('status', 'ATIVO')
            ->count();

        // Revenue by month (last 6 months)
        // Build revenue by month in PHP to be DB-agnostic
        $paymentsRangeStart = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
        $payments = Pagamento::query()
            ->where('data', '>=', $paymentsRangeStart)
            ->orderBy('data')
            ->get(['data', 'valor']);

        $revenueByMonth = [];
        foreach ($payments as $p) {
            try {
                $ym = Carbon::parse($p->data)->format('Y-m');
            } catch (\Throwable $e) {
                continue;
            }
            $revenueByMonth[$ym] = ($revenueByMonth[$ym] ?? 0) + (float) $p->valor;
        }

        // ensure months with zero are present for last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i)->format('Y-m');
            $months[] = $m;
            $revenueByMonth[$m] = $revenueByMonth[$m] ?? 0.0;
        }

        // sort by month
        $revenueByMonth = collect($months)->mapWithKeys(fn ($m) => [$m => $revenueByMonth[$m]])->all();

        // Contracts by status
        $contractsByStatus = Contrato::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->all();

        $recentContracts = Contrato::query()
            ->with('cliente:id,nome')
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'cliente_id', 'status', 'endereco', 'created_at'])
            ->map(fn (Contrato $contrato): array => [
                'id' => $contrato->id,
                'cliente' => $contrato->cliente?->nome ?? 'Cliente não encontrado',
                'endereco' => $contrato->endereco,
                'status' => $contrato->status,
                'created_at' => $contrato->created_at?->toDateString(),
            ]);

        $recentPayments = Pagamento::query()
            ->with('contrato.cliente:id,nome')
            ->latest('data')
            ->latest('id')
            ->limit(5)
            ->get(['id', 'contrato_id', 'data', 'valor'])
            ->map(fn (Pagamento $pagamento): array => [
                'id' => $pagamento->id,
                'contrato_id' => $pagamento->contrato_id,
                'cliente' => $pagamento->contrato?->cliente?->nome ?? 'Cliente não encontrado',
                'data' => $pagamento->data,
                'valor' => (float) $pagamento->valor,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_clients' => $totalClients,
                'total_contracts' => $totalContracts,
                'active_contracts' => $activeContracts,
                'payments_this_month' => $paymentsThisMonth,
                'revenue_this_month' => $revenueThisMonth,
                'pieces_in_circulation' => max(0, $piecesInCirculation),
                'overdue_contracts' => $overdueContracts,
                'revenue_by_month' => $revenueByMonth,
                'contracts_by_status' => $contractsByStatus,
            ],
            'recentContracts' => $recentContracts,
            'recentPayments' => $recentPayments,
        ]);
    }
}
