<?php

use App\Jobs\ProcessWhatsappMessage;
use App\Models\WhatsappMessage;
use App\Services\WhatsApp\AgentService;
use App\Services\WhatsApp\WahaClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    config()->set('whatsapp-agent.webhook_secret', 'test-secret');
});

test('accepts an authenticated incoming WAHA message and queues it once', function (): void {
    Queue::fake();
    $payload = [
        'event' => 'message',
        'payload' => [
            'id' => 'waha-message-1',
            'from' => '5511999999999@c.us',
            'body' => 'Liste os contratos ativos',
            'fromMe' => false,
        ],
    ];

    $this->postJson(route('api.whatsapp.waha'), $payload, ['X-WAHA-Secret' => 'test-secret'])
        ->assertAccepted()
        ->assertJsonPath('accepted', true);
    $this->postJson(route('api.whatsapp.waha'), $payload, ['X-WAHA-Secret' => 'test-secret'])
        ->assertAccepted();

    expect(WhatsappMessage::count())->toBe(1);
    expect(WhatsappMessage::first()->chat_id)->toBe('5511999999999@c.us');
    Queue::assertPushed(ProcessWhatsappMessage::class, 1);
});

test('ignores WAHA group messages', function (): void {
    Queue::fake();

    $this->postJson(route('api.whatsapp.waha'), [
        'event' => 'message',
        'payload' => [
            'id' => 'waha-group-message-1',
            'from' => '120363424860715245@g.us',
            'body' => 'Mensagem do grupo',
            'fromMe' => false,
        ],
    ], ['X-WAHA-Secret' => 'test-secret'])
        ->assertOk()
        ->assertJsonPath('accepted', false);

    expect(WhatsappMessage::count())->toBe(0);
    Queue::assertNothingPushed();
});

test('rejects a WAHA request without the secret', function (): void {
    $this->postJson(route('api.whatsapp.waha'), [])->assertUnauthorized();
});

test('processes an allowed number when allowed chat ids are not configured', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'http://waha.test/api/sendText' => Http::response(['id' => 'sent-message-id'], 201),
    ]);
    config()->set('whatsapp-agent.waha_url', 'http://waha.test');
    config()->set('whatsapp-agent.session', 'default');
    config()->set('whatsapp-agent.allowed_numbers', ['5511999999999']);
    config()->set('whatsapp-agent.allowed_chat_ids', null);
    $message = WhatsappMessage::create([
        'message_id' => 'waha-message-allowed-number',
        'from' => '5511999999999',
        'chat_id' => '5511999999999@c.us',
        'body' => 'listar contratos ativos',
        'direction' => 'in',
    ]);

    (new ProcessWhatsappMessage($message->id))->handle(
        app(AgentService::class),
        app(WahaClient::class),
    );

    $this->assertDatabaseHas('whatsapp_messages', [
        'message_id' => 'out-'.$message->id,
        'from' => '5511999999999',
        'direction' => 'out',
    ]);
    Http::assertSentCount(1);
});
