<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geolocalizacao_ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ocorrencia_id')->unique()->constrained('ocorrencias')->onDelete('cascade');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('bairro_id')->nullable()->constrained('bairros');
            $table->foreignId('zona_id')->nullable()->constrained('zonas');
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geolocalizacao_ocorrencias');
    }
};