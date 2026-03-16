<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CadeiaCustodia extends Model
{
    protected $table = 'cadeia_custodia';

    protected $fillable = [
        'evidencia_id', 'agente_origem_id', 'agente_destino_id',
        'local_origem', 'local_destino', 'data_transferencia', 'motivo', 'observacoes'
    ];

    protected function casts(): array { return ['data_transferencia' => 'datetime']; }

    public function evidencia() { return $this->belongsTo(Evidencia::class, 'evidencia_id'); }
    public function agenteOrigem() { return $this->belongsTo(Agente::class, 'agente_origem_id'); }
    public function agenteDestino() { return $this->belongsTo(Agente::class, 'agente_destino_id'); }
}