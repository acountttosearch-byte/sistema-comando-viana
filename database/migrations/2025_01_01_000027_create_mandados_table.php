<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandados', function (Blueprint $table) {
            $table->id();
            $table->string('numero_mandado', 30)->unique();
            $table->foreignId('tipo_mandado_id')->constrained('tipos_mandado');
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias');
            $table->foreignId('pessoa_id')->nullable()->constrained('pessoas');
            $table->string('tribunal', 200)->nullable();
            $table->string('juiz', 200)->nullable();
            $table->date('data_emissao');
            $table->date('data_validade')->nullable();
            $table->enum('estado', ['pendente', 'executado', 'expirado', 'cancelado'])->default('pendente');
            $table->text('descricao');
            $table->foreignId('agente_responsavel_id')->constrained('agentes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandados');
    }
};