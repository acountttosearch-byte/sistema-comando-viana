<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patentes')) {
            return;
        }

        $patenteAntiga = 'Che' . 'fe';
        $patenteRemovida = DB::table('patentes')->where('nome', $patenteAntiga)->first();
        $subchefe = DB::table('patentes')->where('nome', 'Subchefe')->first();

        if (!$subchefe) {
            if ($patenteRemovida) {
                DB::table('patentes')->where('id', $patenteRemovida->id)->update([
                    'nome' => 'Subchefe',
                    'abreviatura' => 'SCH',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('patentes')->insert([
                    'nome' => 'Subchefe',
                    'abreviatura' => 'SCH',
                    'nivel_hierarquico' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return;
        }

        if ($patenteRemovida) {
            if (Schema::hasTable('agentes')) {
                DB::table('agentes')
                    ->where('patente_id', $patenteRemovida->id)
                    ->update(['patente_id' => $subchefe->id]);
            }

            DB::table('patentes')->where('id', $patenteRemovida->id)->delete();
        }
    }

    public function down(): void
    {
        // Mantem a nomenclatura institucional corrigida.
    }
};
