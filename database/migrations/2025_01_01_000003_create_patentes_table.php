<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patentes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('abreviatura', 20)->nullable();
            $table->integer('nivel_hierarquico')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patentes');
    }
};