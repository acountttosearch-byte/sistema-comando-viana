<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrulha_incidentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrulha_id')->constrained('patrulhas')->onDelete('cascade');
            $table->foreignId('ocorrencia_id')->nullable()->constrained('ocorrencias');
            $table->time('hora_registo');
            $table->string('local', 300)->nullable();
            $table->text('descricao');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrulha_incidentes');
    }
};