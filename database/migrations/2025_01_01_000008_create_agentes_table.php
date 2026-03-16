<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nome', 200);
            $table->string('nip', 50)->unique(); // Número de Identidade Profissional
            $table->string('bi', 30)->unique()->nullable();
            $table->date('data_nascimento')->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('morada', 300)->nullable();
            $table->string('foto', 500)->nullable();
            $table->foreignId('patente_id')->nullable()->constrained('patentes');
            $table->string('cargo', 100)->nullable();
            $table->foreignId('unidade_id')->constrained('unidades');
            $table->date('data_admissao')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'suspenso', 'transferido'])->default('activo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('unidade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};