<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerta_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alerta_id')->constrained('alertas')->onDelete('cascade');
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->boolean('visualizado')->default(false);
            $table->dateTime('data_visualizacao')->nullable();
            $table->foreignId('visualizado_por')->nullable()->constrained('agentes');
            $table->timestamps();

            $table->unique(['alerta_id', 'unidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerta_destinatarios');
    }
};