<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArmamentoAtribuicao extends Model
{
    protected $table = 'armamento_atribuicoes';
    protected $fillable = ['armamento_id', 'agente_id', 'data_atribuicao', 'data_devolucao', 'estado', 'observacoes'];
    protected function casts(): array { return ['data_atribuicao' => 'date', 'data_devolucao' => 'date']; }

    public function armamento() { return $this->belongsTo(Armamento::class, 'armamento_id'); }
    public function agente() { return $this->belongsTo(Agente::class, 'agente_id'); }
}