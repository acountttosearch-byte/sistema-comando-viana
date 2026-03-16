<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_alteracoes', function (Blueprint $table) {
            $table->id();
            $table->string('tabela', 100);
            $table->unsignedBigInteger('registro_id');
            $table->string('campo', 100);
            $table->text('valor_antigo')->nullable();
            $table->text('valor_novo')->nullable();
            $table->foreignId('alterado_por')->constrained('users');
            $table->timestamps();

            $table->index(['tabela', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_alteracoes');
    }
};