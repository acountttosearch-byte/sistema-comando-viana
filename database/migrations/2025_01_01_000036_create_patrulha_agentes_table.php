<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrulha_agentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrulha_id')->constrained('patrulhas')->onDelete('cascade');
            $table->foreignId('agente_id')->constrained('agentes');
            $table->enum('funcao', ['lider', 'apoio'])->default('apoio');
            $table->timestamps();

            $table->unique(['patrulha_id', 'agente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrulha_agentes');
    }
};