<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processos_criminais', function (Blueprint $table) {
            $table->id();
            $table->string('numero_processo')->unique();
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias')->cascadeOnDelete();
            $table->foreignId('agente_responsavel_id')->constrained('agentes');
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->enum('estado', ['em_instrucao', 'concluido', 'remetido_mp', 'arquivado'])->default('em_instrucao');
            $table->date('data_abertura');
            $table->date('data_conclusao')->nullable();
            $table->date('data_remessa')->nullable();
            $table->text('resumo')->nullable();
            $table->text('parecer_final')->nullable();
            $table->string('destino_remessa')->nullable();
            $table->boolean('confidencial')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processos_criminais');
    }
};
