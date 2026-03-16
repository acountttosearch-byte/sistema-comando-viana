<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->string('alcunha', 100)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('bi', 30)->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->string('nacionalidade', 80)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('morada', 300)->nullable();
            $table->string('bairro', 150)->nullable();
            $table->string('foto', 500)->nullable();
            $table->text('caracteristicas_fisicas')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
            $table->index('bi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};