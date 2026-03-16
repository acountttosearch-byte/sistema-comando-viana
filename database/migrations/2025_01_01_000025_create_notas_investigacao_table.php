<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_investigacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigacao_id')->constrained('investigacoes')->onDelete('cascade');
            $table->foreignId('agente_id')->constrained('agentes');
            $table->string('titulo', 200);
            $table->text('conteudo');
            $table->boolean('confidencial')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_investigacao');
    }
};