<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_ocorrencia', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('cor', 7)->nullable(); // hex color
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_ocorrencia');
    }
};