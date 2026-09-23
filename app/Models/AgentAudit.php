<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentAudit extends Model
{
    protected $fillable = ['phone', 'action', 'arguments', 'result'];

    protected function casts(): array
    {
        return ['arguments' => 'array', 'result' => 'array'];
    }
}
