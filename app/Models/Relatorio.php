<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relatorio extends Model
{
    protected $fillable = ['tipo_relatorio_id', 'periodo_inicio', 'periodo_fim', 'unidade_id', 'gerado_por', 'ficheiro', 'dados'];
    protected function casts(): array { return ['periodo_inicio' => 'date', 'periodo_fim' => 'date', 'dados' => 'array']; }

    public function tipoRelatorio() { return $this->belongsTo(TipoRelatorio::class, 'tipo_relatorio_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function geradoPor() { return $this->belongsTo(Agente::class, 'gerado_por'); }
}