<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagamentoRequest;
use App\Http\Requests\UpdatePagamentoRequest;
use App\Models\Contrato;
use App\Models\Pagamento;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class PagamentoController extends Controller
{
    /**
     * Display a listing of all payments.
     */
    public function index(): Response
    {
        $pagamentos = Pagamento::query()
            ->with(['contrato.cliente'])
            ->latest('data')
            ->paginate(15);

        return inertia('Pagamentos/Index', [
            'pagamentos' => $pagamentos,
        ]);
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(): Response
    {
        return inertia('Pagamentos/Create', [
            'contratos' => Contrato::query()
                ->with('cliente')
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'label' => "[#{$c->id}] {$c->cliente?->nome} - {$c->endereco}",
                ]),
        ]);
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(StorePagamentoRequest $request): RedirectResponse
    {
        Pagamento::create($request->validated());

        $contrato = Contrato::find($request->contrato_id);
        $contrato->sincronizarStatus();

        return to_route('pagamentos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Pagamento registrado com sucesso.',
        ]);
    }

    /**
     * Show the form for editing a payment.
     */
    public function edit(Pagamento $pagamento): Response
    {
        return inertia('Pagamentos/Edit', [
            'pagamento' => $pagamento->load('contrato.cliente'),
            'contratos' => Contrato::query()
                ->with('cliente')
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'label' => "[#{$c->id}] {$c->cliente?->nome} - {$c->endereco}",
                ]),
        ]);
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(UpdatePagamentoRequest $request, Pagamento $pagamento): RedirectResponse
    {
        $pagamento->update($request->validated());
        $pagamento->contrato->sincronizarStatus();

        return to_route('pagamentos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Pagamento atualizado com sucesso.',
        ]);
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Pagamento $pagamento): RedirectResponse
    {
        $contrato = $pagamento->contrato;
        $pagamento->delete();
        $contrato->sincronizarStatus();

        return to_route('pagamentos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Pagamento removido com sucesso.',
        ]);
    }
}
