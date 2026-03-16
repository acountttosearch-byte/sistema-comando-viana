<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mandado extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_mandado', 'tipo_mandado_id', 'ocorrencia_id', 'pessoa_id',
        'tribunal', 'juiz', 'data_emissao', 'data_validade', 'estado',
        'descricao', 'agente_responsavel_id'
    ];

    protected function casts(): array { return ['data_emissao' => 'date', 'data_validade' => 'date']; }

    public function tipoMandado() { return $this->belongsTo(TipoMandado::class, 'tipo_mandado_id'); }
    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function pessoa() { return $this->belongsTo(Pessoa::class, 'pessoa_id'); }
    public function agenteResponsavel() { return $this->belongsTo(Agente::class, 'agente_responsavel_id'); }
}