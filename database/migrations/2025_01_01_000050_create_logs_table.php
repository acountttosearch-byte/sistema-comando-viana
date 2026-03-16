<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('acao', 50); // criar, editar, apagar, login, logout, visualizar
            $table->string('tabela', 100)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('descricao')->nullable();
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_novos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('acao');
            $table->index('tabela');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};