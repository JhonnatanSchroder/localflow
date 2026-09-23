<?php

namespace App\Models;

use Database\Factories\MovimentacaoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimentacao extends Model
{
    /** @use HasFactory<MovimentacaoFactory> */
    use HasFactory;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'contrato_id',
        'data',
        'tipo',
        'qtd',
    ];

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }
}
