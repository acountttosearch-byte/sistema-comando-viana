<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queixas_cidadao', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo', 30)->unique();
            $table->string('nome_cidadao', 200);
            $table->string('bi', 30)->nullable();
            $table->string('telefone', 20);
            $table->string('email', 150)->nullable();
            $table->string('tipo_queixa', 100);
            $table->text('descricao');
            $table->string('local', 300)->nullable();
            $table->string('ficheiro_anexo', 500)->nullable();
            $table->enum('estado', ['recebida', 'em_analise', 'convertida', 'rejeitada'])->default('recebida');
            $table->foreignId('ocorrencia_id')->nullable()->constrained('ocorrencias');
            $table->foreignId('analisado_por')->nullable()->constrained('agentes');
            $table->text('justificacao_rejeicao')->nullable();
            $table->timestamps();

            $table->index('protocolo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queixas_cidadao');
    }
};