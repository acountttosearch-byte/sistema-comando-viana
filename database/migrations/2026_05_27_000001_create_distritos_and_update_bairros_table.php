<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120)->unique();
            $table->timestamps();
        });

        Schema::table('bairros', function (Blueprint $table) {
            $table->foreignId('distrito_id')->nullable()->after('nome')->constrained('distritos');
            $table->foreignId('esquadra_id')->nullable()->after('distrito_id')->constrained('unidades');
            $table->index(['distrito_id', 'esquadra_id']);
        });
    }

    public function down(): void
    {
        Schema::table('bairros', function (Blueprint $table) {
            $table->dropForeign(['distrito_id']);
            $table->dropForeign(['esquadra_id']);
            $table->dropIndex(['distrito_id', 'esquadra_id']);
            $table->dropColumn(['distrito_id', 'esquadra_id']);
        });

        Schema::dropIfExists('distritos');
    }
};
