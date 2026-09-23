<?php

namespace Database\Factories;

use App\Models\Movimentacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movimentacao>
 */
class MovimentacaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contrato_id' => ContratoFactory::new(),
            'data' => fake()->date(),
            'tipo' => fake()->randomElement(['RETIRADA', 'DEVOLUCAO']),
            'qtd' => fake()->randomNumber(2),

        ];
    }
}
