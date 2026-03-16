<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->string('numero_ocorrencia', 30)->unique();
            $table->foreignId('tipo_crime_id')->constrained('tipos_crime');
            $table->text('descricao');
            $table->date('data_ocorrencia');
            $table->time('hora_ocorrencia')->nullable();
            $table->string('local', 500);
            $table->string('bairro', 150)->nullable();
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'critica'])->default('media');
            $table->foreignId('estado_id')->constrained('estados_ocorrencia');
            $table->foreignId('agente_registo_id')->constrained('agentes');
            $table->foreignId('agente_responsavel_id')->nullable()->constrained('agentes');
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->boolean('confidencial')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('numero_ocorrencia');
            $table->index('data_ocorrencia');
            $table->index('estado_id');
            $table->index('unidade_id');
            $table->index('prioridade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias');
    }
};