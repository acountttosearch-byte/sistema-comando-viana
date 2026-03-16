<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->foreignId('tipo_unidade_id')->constrained('tipos_unidade');
            $table->foreignId('unidade_pai_id')->nullable()->constrained('unidades');
            $table->string('endereco', 300)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};