<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappConversation extends Model
{
    protected $fillable = [
        'phone',
        'pending_action',
        'pending_data',
        'pending_at',
    ];

    protected $casts = [
        'pending_data' => 'array',
        'pending_at' => 'datetime',
    ];

    public function hasPendingAction(): bool
    {
        return ! empty($this->pending_action);
    }

    public function clearPendingAction(): void
    {
        $this->update([
            'pending_action' => null,
            'pending_data' => null,
            'pending_at' => null,
        ]);
    }
}

