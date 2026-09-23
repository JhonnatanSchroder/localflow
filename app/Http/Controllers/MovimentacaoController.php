<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimentacaoRequest;
use App\Http\Requests\UpdateMovimentacaoRequest;
use App\Models\Contrato;
use App\Models\Movimentacao;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class MovimentacaoController extends Controller
{
    public function index(): Response
    {
        return inertia('Movimentacoes/Index', [
            'movimentacoes' => Movimentacao::query()
                ->with('contrato.cliente')
                ->latest('data')
                ->latest('id')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('Movimentacoes/Create', [
            'contratos' => $this->contratos(),
        ]);
    }

    public function store(StoreMovimentacaoRequest $request): RedirectResponse
    {
        $movimentacao = Movimentacao::create($request->validated());
        $movimentacao->contrato->sincronizarStatus();

        return to_route('movimentacoes.index')->with('toast', [
            'type' => 'success',
            'message' => 'Movimentação cadastrada com sucesso.',
        ]);
    }

    public function edit(Movimentacao $movimentacao): Response
    {
        return inertia('Movimentacoes/Edit', [
            'movimentacao' => $movimentacao->load('contrato.cliente'),
        ]);
    }

    public function update(UpdateMovimentacaoRequest $request, Movimentacao $movimentacao): RedirectResponse
    {
        $movimentacao->update($request->validated());
        $movimentacao->contrato->sincronizarStatus();

        return to_route('movimentacoes.index')->with('toast', [
            'type' => 'success',
            'message' => 'Movimentação atualizada com sucesso.',
        ]);
    }

    public function destroy(Movimentacao $movimentacao): RedirectResponse
    {
        $contrato = $movimentacao->contrato;
        $movimentacao->delete();
        $contrato->sincronizarStatus();

        return to_route('movimentacoes.index')->with('toast', [
            'type' => 'success',
            'message' => 'Movimentação excluída com sucesso.',
        ]);
    }

    private function contratos(): mixed
    {
        return Contrato::query()
            ->with('cliente')
            ->latest('id')
            ->get(['id', 'cliente_id', 'endereco']);
    }
}
