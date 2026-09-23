<?php

namespace Database\Factories;

use App\Models\Contrato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contrato>
 */
class ContratoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataInicio = fake()->dateTimeBetween('-15 days', 'now');
        $dataFim = fake()->dateTimeBetween($dataInicio, '+30 days');

        return [
            'cliente_id' => ClienteFactory::new(),
            'endereco' => fake()->address(),
            'data_inicio' => $dataInicio->format('Y-m-d'),
            'data_fim' => $dataFim->format('Y-m-d'),
            'valor_pc_dia' => 0.60,
            'qtd_frete' => fake()->numberBetween(0, 4),
            'ultima_cobranca' => fake()->date(),
            'proxima_cobranca' => fake()->date(),
            'obs' => fake()->text(),

        ];
    }
}
