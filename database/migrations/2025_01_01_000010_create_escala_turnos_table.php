<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escala_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')->constrained('agentes')->onDelete('cascade');
            $table->foreignId('turno_id')->constrained('turnos');
            $table->date('data');
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->enum('estado', ['confirmado', 'falta', 'substituido', 'folga'])->default('confirmado');
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['data', 'unidade_id']);
            $table->unique(['agente_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escala_turnos');
    }
};