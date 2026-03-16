<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->foreignId('bairro_id')->constrained('bairros');
            $table->enum('nivel_risco', ['baixo', 'medio', 'alto', 'critico'])->default('medio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas');
    }
};