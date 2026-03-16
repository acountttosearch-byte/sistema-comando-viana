<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo', 100);
            $table->string('titulo', 200);
            $table->text('mensagem');
            $table->string('link', 500)->nullable();
            $table->boolean('lida')->default(false);
            $table->dateTime('data_leitura')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};