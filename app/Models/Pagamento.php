<?php

namespace App\Models;

use Database\Factories\PagamentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    /** @use HasFactory<PagamentoFactory> */
    use HasFactory;

    protected $fillable = [
        'contrato_id',
        'data',
        'valor',
    ];

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);

    }
}
