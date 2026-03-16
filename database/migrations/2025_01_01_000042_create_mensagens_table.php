<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remetente_id')->constrained('agentes');
            $table->foreignId('destinatario_id')->nullable()->constrained('agentes');
            $table->foreignId('unidade_destino_id')->nullable()->constrained('unidades');
            $table->string('titulo', 200);
            $table->text('mensagem');
            $table->string('ficheiro_anexo', 500)->nullable();
            $table->boolean('lida')->default(false);
            $table->dateTime('data_leitura')->nullable();
            $table->enum('prioridade', ['normal', 'urgente'])->default('normal');
            $table->timestamps();
            $table->softDeletes();

            $table->index('destinatario_id');
            $table->index('lida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensagens');
    }
};