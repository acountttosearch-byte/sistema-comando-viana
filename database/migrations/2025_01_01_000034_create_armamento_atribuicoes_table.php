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
            $table->unsignedBigInteger('armamento_id');
            $table->unsignedBigInteger('agente_id');
            $table->date('data_atribuicao');
            $table->date('data_devolucao')->nullable();
            $table->enum('estado', ['atribuido', 'devolvido'])->default('atribuido');
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('armamento_id')->references('id')->on('armamento');
            $table->foreign('agente_id')->references('id')->on('agentes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('armamento_atribuicoes');
    }
};