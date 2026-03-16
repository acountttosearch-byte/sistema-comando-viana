<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viatura_manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viatura_id')->constrained('viaturas');
            $table->string('tipo_manutencao', 150);
            $table->text('descricao')->nullable();
            $table->date('data_entrada');
            $table->date('data_saida')->nullable();
            $table->decimal('custo', 12, 2)->nullable();
            $table->enum('estado', ['em_curso', 'concluida', 'cancelada'])->default('em_curso');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viatura_manutencoes');
    }
};