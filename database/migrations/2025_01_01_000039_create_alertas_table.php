<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_alerta_id')->constrained('tipos_alerta');
            $table->string('titulo', 200);
            $table->text('descricao');
            $table->string('foto', 500)->nullable();
            $table->foreignId('pessoa_id')->nullable()->constrained('pessoas');
            $table->foreignId('ocorrencia_id')->nullable()->constrained('ocorrencias');
            $table->enum('prioridade', ['urgente', 'alta', 'normal'])->default('normal');
            $table->enum('estado', ['activo', 'cancelado', 'resolvido'])->default('activo');
            $table->foreignId('criado_por')->constrained('agentes');
            $table->dateTime('data_expiracao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};