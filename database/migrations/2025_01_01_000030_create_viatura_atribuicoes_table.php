<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viatura_atribuicoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('viatura_id');
            $table->unsignedBigInteger('agente_id');
            $table->dateTime('data_saida');
            $table->dateTime('data_retorno')->nullable();
            $table->integer('quilometragem_saida');
            $table->integer('quilometragem_retorno')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('viatura_id')->references('id')->on('viaturas');
            $table->foreign('agente_id')->references('id')->on('agentes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viatura_atribuicoes');
    }
};