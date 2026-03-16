<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas_patrulha', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('descricao', 500)->nullable();
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->enum('nivel_risco', ['baixo', 'medio', 'alto', 'critico'])->default('medio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas_patrulha');
    }
};