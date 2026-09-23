<?php

use App\Models\Cliente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Cliente::class)->constrained();
            $table->string('endereco')->nullable(

            );
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->decimal('valor_pc_dia', 10, 2)->default(0.60);
            $table->integer('qtd_frete')->nullable();
            $table->decimal('valor_frete', 10, 2)->default(15.00);
            $table->string('status', 50)->default('ATIVO');
            $table->string('ultima_cobranca')->nullable();
            $table->string('proxima_cobranca');
            $table->string('obs')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
