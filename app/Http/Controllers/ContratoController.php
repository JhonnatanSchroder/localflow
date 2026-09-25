<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContratoRequest;
use App\Http\Requests\UpdateContratoRequest;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Movimentacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $contratos = Contrato::query()
            ->with(['cliente', 'movimentacoes', 'pagamentos'])
            ->select('contratos.*')
            ->withSum('pagamentos as total_pago', 'valor')
            ->selectSub(
                Movimentacao::query()
                    ->selectRaw("GREATEST(COALESCE(SUM(CASE WHEN tipo = 'RETIRADA' THEN qtd WHEN tipo = 'DEVOLUCAO' THEN -qtd ELSE 0 END), 0), 0)")
                    ->whereColumn('contrato_id', 'contratos.id'),
                'pecas_atuais',
            )
            ->get()
            ->map(function (Contrato $contrato): Contrato {
                $contrato->sincronizarStatus();
                $contrato->setAttribute('total_calculado', $contrato->totalCalculado());
                $contrato->setAttribute('total_final', $contrato->valorFinal());
                $contrato->setAttribute('total_pago', (float) ($contrato->total_pago ?? 0));

                return $contrato;
            });

        return inertia('Contratos/Index', compact('contratos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return inertia('Contratos/Create', [
            'clientes' => Cliente::query()->orderBy('nome')->get(['id', 'nome', 'endereco']),
        ])->with('toast', [
            'type' => 'success',
            'message' => 'Contrato cadastrado com sucesso.!',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContratoRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $quantidadeInicial = $dados['qtd_inicial'];
        unset($dados['qtd_inicial']);

        DB::transaction(function () use ($dados, $quantidadeInicial): void {
            $contrato = Contrato::create($dados);

            if ($quantidadeInicial > 0) {
                $contrato->movimentacoes()->create([
                    'data' => $contrato->data_inicio,
                    'tipo' => 'RETIRADA',
                    'qtd' => $quantidadeInicial,
                ]);
            }

            $contrato->sincronizarStatus();
        });

        return to_route('contratos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Contrato cadastrado com sucesso.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contrato $contrato)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contrato $contrato): Response
    {
        return inertia('Contratos/Edit', [
            'contrato' => $contrato,
            'clientes' => Cliente::query()->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContratoRequest $request, Contrato $contrato): RedirectResponse
    {
        dd($contrato);
        $contrato->update($request->validated());
        $contrato->sincronizarStatus();


        return to_route('contratos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Contrato atualizado com sucesso.',
        ]);
    }

    public function devolver(Contrato $contrato): RedirectResponse
    {
        DB::transaction(function () use ($contrato): void {
            $contrato->refresh();
            $pecasAtuais = $contrato->pecasAtuais();

            if ($pecasAtuais > 0) {
                $contrato->movimentacoes()->create([
                    'data' => now()->toDateString(),
                    'tipo' => 'DEVOLUCAO',
                    'qtd' => $pecasAtuais,
                ]);
            }

            $contrato->sincronizarStatus();
        });

        return to_route('contratos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Peças devolvidas. Contrato encerrado operacionalmente.',
        ]);
    }

    public function finalizar(Contrato $contrato): RedirectResponse
    {
        $contrato->sincronizarStatus();
        $contrato->refresh();

        if ($contrato->status !== 'DEVOLVIDO') {
            return to_route('contratos.index')->with('toast', [
                'type' => 'warning',
                'message' => 'O contrato só pode ser finalizado após a devolução das peças.',
            ]);
        }

        $totalPago = (float) $contrato->pagamentos()->sum('valor');
        $totalContrato = $contrato->valorFinal();
        $valorRestante = $totalContrato - $totalPago;

        if ($valorRestante > 0) {
            return to_route('contratos.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Ainda falta pagar R$ '.number_format($valorRestante, 2, ',', '.').'.',
            ]);
        }

        $contrato->update(['status' => 'FINALIZADO']);

        return to_route('contratos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Contrato finalizado com sucesso.',
        ]);
    }

    public function quitar(Contrato $contrato): RedirectResponse
    {
        $contrato->sincronizarStatus();
        $contrato->refresh();

        if ($contrato->status !== 'DEVOLVIDO') {
            return to_route('contratos.index')->with('toast', [
                'type' => 'warning',
                'message' => 'O contrato precisa estar devolvido antes da quitação.',
            ]);
        }

        $valorRestante = round(
            $contrato->valorFinal() - (float) $contrato->pagamentos()->sum('valor'),
            2,
        );

        if ($valorRestante <= 0) {
            $contrato->sincronizarStatus();

            return to_route('contratos.index')->with('toast', [
                'type' => 'success',
                'message' => 'O contrato já está quitado.',
            ]);
        }

        $contrato->pagamentos()->create([
            'data' => now()->toDateString(),
            'valor' => $valorRestante,
        ]);
        $contrato->sincronizarStatus();

        return to_route('contratos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Contrato quitado e finalizado com sucesso.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contrato $contrato): RedirectResponse
    {
        $contrato->delete();

        return to_route('contratos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Contrato excluído com sucesso.',
        ]);
    }
}
