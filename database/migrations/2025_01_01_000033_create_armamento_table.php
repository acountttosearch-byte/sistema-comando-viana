<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('armamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_armamento_id')->constrained('tipos_armamento');
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 100)->unique();
            $table->string('calibre', 30)->nullable();
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->enum('estado', ['operacional', 'manutencao', 'perdida', 'apreendida', 'abatida'])->default('operacional');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('armamento');
    }
};