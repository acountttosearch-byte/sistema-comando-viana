<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrulhas', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->foreignId('turno_id')->constrained('turnos');
            $table->foreignId('zona_id')->constrained('zonas_patrulha');
            $table->foreignId('viatura_id')->nullable()->constrained('viaturas');
            $table->foreignId('agente_lider_id')->constrained('agentes');
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->enum('estado', ['planeada', 'em_curso', 'concluida', 'cancelada'])->default('planeada');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fim')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrulhas');
    }
};