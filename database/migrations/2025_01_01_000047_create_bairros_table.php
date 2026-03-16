<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bairros', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('municipio', 100)->default('Viana');
            $table->foreignId('unidade_responsavel_id')->nullable()->constrained('unidades');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bairros');
    }
};