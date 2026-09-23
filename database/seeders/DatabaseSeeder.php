<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Pagamento;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'jhonnatan',
            'email' => 'jhonnatangustavo012@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        // $clientes = Cliente::factory(10)
        //     ->has(
        //         Contrato::factory(2)
        //             ->has(Pagamento::factory(3), 'pagamentos'),
        //         'contratos',
        //     )
        //     ->create();

        // $clientes->load('contratos');

        // $clientes->each(function (Cliente $cliente): void {
        //     $cliente->contratos->each(function (Contrato $contrato): void {
        //         $contrato->movimentacoes()->create([
        //             'data' => $contrato->data_inicio,
        //             'tipo' => 'RETIRADA',
        //             'qtd' => fake()->numberBetween(10, 50),
        //         ]);
        //     });
        // });
    }
}
