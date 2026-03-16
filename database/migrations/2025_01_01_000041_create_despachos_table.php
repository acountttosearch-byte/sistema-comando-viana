<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'critica']);
            $table->foreignId('despachado_para')->constrained('agentes');
            $table->foreignId('despachado_por')->constrained('agentes');
            $table->foreignId('unidade_destino')->constrained('unidades');
            $table->text('instrucoes')->nullable();
            $table->enum('estado', ['pendente', 'aceite', 'em_curso', 'concluido', 'rejeitado'])->default('pendente');
            $table->dateTime('data_despacho');
            $table->dateTime('data_resposta')->nullable();
            $table->integer('tempo_resposta_minutos')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('data_despacho');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};