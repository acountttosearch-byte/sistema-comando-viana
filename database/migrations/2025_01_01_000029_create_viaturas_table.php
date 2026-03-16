<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viaturas', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 20)->unique();
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->integer('ano')->nullable();
            $table->string('cor', 50)->nullable();
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->enum('estado', ['operacional', 'manutencao', 'inactiva', 'abatida'])->default('operacional');
            $table->integer('quilometragem')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaturas');
    }
};