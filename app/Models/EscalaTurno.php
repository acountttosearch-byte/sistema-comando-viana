<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscalaTurno extends Model
{
    protected $table = 'escala_turnos';
    protected $fillable = ['agente_id', 'turno_id', 'data', 'unidade_id', 'estado', 'observacoes'];

    protected function casts(): array { return ['data' => 'date']; }

    public function agente() { return $this->belongsTo(Agente::class, 'agente_id'); }
    public function turno() { return $this->belongsTo(Turno::class, 'turno_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
}