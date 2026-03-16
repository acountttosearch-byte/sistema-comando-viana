<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias')->onDelete('cascade');
            $table->foreignId('tipo_evidencia_id')->constrained('tipos_evidencia');
            $table->string('descricao', 500);
            $table->string('ficheiro', 500)->nullable();
            $table->string('localizacao_fisica', 300)->nullable();
            $table->bigInteger('tamanho_ficheiro')->nullable();
            $table->string('hash_ficheiro', 128)->nullable();
            $table->foreignId('agente_registo_id')->constrained('agentes');
            $table->enum('estado', ['em_custodia', 'em_analise', 'tribunal', 'devolvido', 'destruido'])->default('em_custodia');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};