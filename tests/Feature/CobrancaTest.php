<?php

use App\Models\Contrato;
use App\Models\User;

test('authenticated users see active contracts due for billing', function (): void {
    $user = User::factory()->create();
    $dueContract = Contrato::factory()->create([
        'status' => 'ATIVO',
        'proxima_cobranca' => today()->toDateString(),
    ]);
    $futureContract = Contrato::factory()->create([
        'status' => 'ATIVO',
        'proxima_cobranca' => today()->addDay()->toDateString(),
    ]);
    $returnedContract = Contrato::factory()->create([
        'status' => 'DEVOLVIDO',
        'proxima_cobranca' => today()->subDay()->toDateString(),
    ]);

    $response = $this->actingAs($user)->get(route('cobrancas.index'));

    $response
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Cobrancas/Index')
            ->has('cobrancas', 1)
            ->where('cobrancas.0.id', $dueContract->id),
        );
});
