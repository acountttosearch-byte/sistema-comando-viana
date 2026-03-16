<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cadeia_custodia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidencia_id')->constrained('evidencias')->onDelete('cascade');
            $table->foreignId('agente_origem_id')->constrained('agentes');
            $table->foreignId('agente_destino_id')->constrained('agentes');
            $table->string('local_origem', 200);
            $table->string('local_destino', 200);
            $table->dateTime('data_transferencia');
            $table->string('motivo', 300);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cadeia_custodia');
    }
};