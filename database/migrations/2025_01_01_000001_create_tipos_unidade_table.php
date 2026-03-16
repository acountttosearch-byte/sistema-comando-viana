<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_unidade', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100); // comando_municipal, esquadra, posto_policial
            $table->string('descricao', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_unidade');
    }
};