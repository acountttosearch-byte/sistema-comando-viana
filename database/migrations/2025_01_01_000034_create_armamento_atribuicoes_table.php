<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('armamento_atribuicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('armamento_id')->constrained('armamento');
            $table->foreignId('agente_id')->constrained('agentes');
            $table->date('data_atribuicao');
            $table->date('data_devolucao')->nullable();
            $table->enum('estado', ['atribuido', 'devolvido'])->default('atribuido');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('armamento_atribuicoes');
    }
};