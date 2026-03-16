<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    protected $fillable = [
        'ocorrencia_id', 'prioridade', 'despachado_para', 'despachado_por',
        'unidade_destino', 'instrucoes', 'estado', 'data_despacho',
        'data_resposta', 'tempo_resposta_minutos'
    ];

    protected function casts(): array { return ['data_despacho' => 'datetime', 'data_resposta' => 'datetime']; }

    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function agenteDestino() { return $this->belongsTo(Agente::class, 'despachado_para'); }
    public function agenteOrigem() { return $this->belongsTo(Agente::class, 'despachado_por'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_destino'); }
}