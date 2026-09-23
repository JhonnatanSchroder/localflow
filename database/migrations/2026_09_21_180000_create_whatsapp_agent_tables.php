<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('message_id')->unique();
            $table->string('from');
            $table->text('body');
            $table->string('direction', 10);
            $table->timestamps();
        });

        Schema::create('agent_audits', function (Blueprint $table): void {
            $table->id();
            $table->string('phone');
            $table->string('action');
            $table->json('arguments');
            $table->json('result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_audits');
        Schema::dropIfExists('whatsapp_messages');
    }
};
