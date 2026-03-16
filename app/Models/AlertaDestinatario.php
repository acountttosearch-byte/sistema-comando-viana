<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertaDestinatario extends Model
{
    protected $table = 'alerta_destinatarios';
    protected $fillable = ['alerta_id', 'unidade_id', 'visualizado', 'data_visualizacao', 'visualizado_por'];
    protected function casts(): array { return ['visualizado' => 'boolean', 'data_visualizacao' => 'datetime']; }

    public function alerta() { return $this->belongsTo(Alerta::class, 'alerta_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function agente() { return $this->belongsTo(Agente::class, 'visualizado_por'); }
}