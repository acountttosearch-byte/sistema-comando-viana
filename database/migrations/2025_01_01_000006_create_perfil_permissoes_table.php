<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_permissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_id')->constrained('perfis')->onDelete('cascade');
            $table->foreignId('permissao_id')->constrained('permissoes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['perfil_id', 'permissao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_permissoes');
    }
};