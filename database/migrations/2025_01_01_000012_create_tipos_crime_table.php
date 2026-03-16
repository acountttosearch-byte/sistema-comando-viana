<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_crime', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('codigo', 20)->unique()->nullable();
            $table->foreignId('categoria_id')->constrained('categorias_crime');
            $table->enum('gravidade', ['baixa', 'media', 'alta', 'critica'])->default('media');
            $table->string('descricao', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_crime');
    }
};