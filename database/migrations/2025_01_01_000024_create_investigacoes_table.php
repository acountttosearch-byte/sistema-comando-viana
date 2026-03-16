<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigacoes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_investigacao', 30)->unique();
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias');
            $table->foreignId('investigador_id')->constrained('agentes');
            $table->foreignId('estado_id')->constrained('estados_investigacao');
            $table->text('resumo')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->date('prazo')->nullable();
            $table->integer('progresso')->default(0); // 0-100
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigacoes');
    }
};