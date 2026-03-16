<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViaturaManutencao extends Model
{
    protected $table = 'viatura_manutencoes';
    protected $fillable = ['viatura_id', 'tipo_manutencao', 'descricao', 'data_entrada', 'data_saida', 'custo', 'estado'];
    protected function casts(): array { return ['data_entrada' => 'date', 'data_saida' => 'date', 'custo' => 'decimal:2']; }

    public function viatura() { return $this->belongsTo(Viatura::class, 'viatura_id'); }
}