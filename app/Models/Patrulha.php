<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patrulha extends Model
{
    use HasFactory;

    protected $fillable = ['data', 'turno_id', 'zona_id', 'viatura_id', 'agente_lider_id', 'unidade_id', 'estado', 'hora_inicio', 'hora_fim', 'observacoes'];
    protected function casts(): array { return ['data' => 'date']; }

    public function turno() { return $this->belongsTo(Turno::class, 'turno_id'); }
    public function zona() { return $this->belongsTo(ZonaPatrulha::class, 'zona_id'); }
    public function viatura() { return $this->belongsTo(Viatura::class, 'viatura_id'); }
    public function agenteLider() { return $this->belongsTo(Agente::class, 'agente_lider_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function incidentes() { return $this->hasMany(PatrulhaIncidente::class, 'patrulha_id'); }

    public function agentes()
    {
        return $this->belongsToMany(Agente::class, 'patrulha_agentes', 'patrulha_id', 'agente_id')
                     ->withPivot('funcao')->withTimestamps();
    }
}