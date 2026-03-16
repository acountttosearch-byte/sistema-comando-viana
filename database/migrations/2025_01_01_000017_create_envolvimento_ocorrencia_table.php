<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envolvimento_ocorrencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias')->onDelete('cascade');
            $table->foreignId('pessoa_id')->constrained('pessoas')->onDelete('cascade');
            $table->foreignId('tipo_envolvimento_id')->constrained('tipos_envolvimento');
            $table->text('descricao')->nullable();
            $table->timestamps();

            $table->unique(['ocorrencia_id', 'pessoa_id', 'tipo_envolvimento_id'], 'envolvimento_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envolvimento_ocorrencia');
    }
};