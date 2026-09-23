<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Jobs\ProcessWhatsappMessage;
use App\Models\WhatsappMessage;
use Illuminate\Contracts\Console\Kernel;

$m = WhatsappMessage::create([
    'message_id' => 'manual-'.time(),
    'from' => '559491407933',
    'body' => 'Liste os contratos ativos',
    'direction' => 'in',
]);

ProcessWhatsappMessage::dispatchSync($m->id);

echo "created {$m->id}\n";
