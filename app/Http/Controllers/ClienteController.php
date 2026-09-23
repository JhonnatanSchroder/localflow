<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ClienteController extends Controller
{
    public function index(): Response
    {
        return inertia('Clientes/Index', [
            'clientes' => Cliente::query()->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('Clientes/Create');
    }

    public function edit(Cliente $cliente): Response
    {
        return inertia('Clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        Cliente::create($request->validated());

        return to_route('clientes.create')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Cliente cadastrado com sucesso.',
            ]);
    }

    /**
     * Cadastro rápido usado pelo modal da tela de novo contrato.
     */
    public function storeRapido(StoreClienteRequest $request): JsonResponse
    {
        $cliente = Cliente::create($request->validated());

        return response()->json([
            'cliente' => $cliente->only(['id', 'nome']),
        ], 201);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated());

        return to_route('clientes.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Cliente atualizado com sucesso.',
            ]);
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return to_route('clientes.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Cliente excluído com sucesso.',
            ]);
    }
}
