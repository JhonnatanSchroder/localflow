<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $fillable = ['message_id', 'from', 'chat_id', 'body', 'direction'];
}
