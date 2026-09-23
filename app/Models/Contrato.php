<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Database\Factories\ContratoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Contrato extends Model
{
    /** @use HasFactory<ContratoFactory> */
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'endereco',
        'data_inicio',
        'data_fim',
        'valor_pc_dia',
        'cobrar_sabado',
        'qtd_frete',
        'valor_frete',
        'status',
        'ultima_cobranca',
        'proxima_cobranca',
        'obs',
    ];

    protected function casts(): array
    {
        return [
            'data_fim_manual' => 'boolean',
            'cobrar_sabado' => 'boolean'
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function pecasAtuais(): int
    {
        $saldo = $this->movimentacoes()
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'RETIRADA' THEN qtd WHEN tipo = 'DEVOLUCAO' THEN -qtd ELSE 0 END), 0) as saldo")
            ->value('saldo');

        return max((int) $saldo, 0);
    }

    public function totalCalculado(): float
    {

        $dataFinal = $this->data_fim
    ? Carbon::parse($this->data_fim)
    : today();

        $movimentacoes = $this->movimentacoes()
            ->whereBetween('data', [$this->data_inicio, $dataFinal->toDateString()])
            ->orderBy('data')
            ->orderBy('id')
            ->get();

        $valorDiasPeca = 0;
        $pecas = 0;

        foreach (
            CarbonPeriod::create(
                Carbon::parse($this->data_inicio),
                $dataFinal
            ) as $data
        ) {
            $movimentacoesDoDia = $movimentacoes->where(
                'data',
                $data->toDateString()
            );

            $pecas += $movimentacoesDoDia
                ->where('tipo', 'RETIRADA')
                ->sum('qtd');

            if (! $data->isSunday() && ($data->isSaturday() || $this->cobrar_sabado)) {
                $valorDiasPeca += $pecas;
            }

            $pecas -= $movimentacoesDoDia
                ->where('tipo', 'DEVOLUCAO')
                ->sum('qtd');

            $pecas = max($pecas, 0);
        }

        $valorPecas = $valorDiasPeca * (float) $this->valor_pc_dia;

        $valorFretes = (int) ($this->qtd_frete ?? 0)
            * (float) $this->valor_frete;

        return $valorPecas + $valorFretes;
    }

    public function sincronizarStatus(): void
{
    $pecasAtuais = $this->pecasAtuais();

    // Se já existe uma data_fim, não devemos apagá-la.
    if ($this->data_fim) {
        $total = $this->totalCalculado();

        $totalPago = (float) $this->pagamentos()->sum('valor');

        $this->status = $totalPago >= $total
            ? 'FINALIZADO'
            : 'DEVOLVIDO';

        $this->saveQuietly();

        return;
    }

    // Ainda existem peças com o cliente.
    if ($pecasAtuais > 0) {
        $this->status = 'ATIVO';

        $this->saveQuietly();

        return;
    }

    // Não existem peças e ainda não temos data_fim.
    $dataUltimaDevolucao = $this->movimentacoes()
        ->where('tipo', 'DEVOLUCAO')
        ->latest('data')
        ->value('data');

    if ($dataUltimaDevolucao) {
        $this->data_fim = $dataUltimaDevolucao;

        $total = $this->totalCalculado();

        $totalPago = (float) $this->pagamentos()->sum('valor');

        $this->status = $totalPago >= $total
            ? 'FINALIZADO'
            : 'DEVOLVIDO';
    }

    $this->saveQuietly();
}
}
