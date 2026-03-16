<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detencoes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_detencao', 30)->unique();
            $table->foreignId('pessoa_id')->constrained('pessoas');
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias');
            $table->dateTime('data_detencao');
            $table->string('local_detencao', 300);
            $table->foreignId('agente_responsavel_id')->constrained('agentes');
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->text('motivo');
            $table->foreignId('estado_id')->constrained('estados_detencao');
            $table->dateTime('data_libertacao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('data_detencao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detencoes');
    }
};