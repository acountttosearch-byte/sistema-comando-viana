<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViaturaAtribuicao extends Model
{
    protected $table = 'viatura_atribuicoes';
    protected $fillable = ['viatura_id', 'agente_id', 'data_saida', 'data_retorno', 'quilometragem_saida', 'quilometragem_retorno', 'observacoes'];
    protected function casts(): array { return ['data_saida' => 'datetime', 'data_retorno' => 'datetime']; }

    public function viatura() { return $this->belongsTo(Viatura::class, 'viatura_id'); }
    public function agente() { return $this->belongsTo(Agente::class, 'agente_id'); }
}