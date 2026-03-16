<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_alerta', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100); // suspeito_procurado, viatura_roubada, desaparecido
            $table->string('icone', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_alerta');
    }
};