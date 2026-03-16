<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_relatorio_id')->constrained('tipos_relatorio');
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->foreignId('unidade_id')->nullable()->constrained('unidades');
            $table->foreignId('gerado_por')->constrained('agentes');
            $table->string('ficheiro', 500)->nullable();
            $table->json('dados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorios');
    }
};