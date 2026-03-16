<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alerta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tipo_alerta_id', 'titulo', 'descricao', 'foto', 'pessoa_id', 'ocorrencia_id', 'prioridade', 'estado', 'criado_por', 'data_expiracao'];
    protected function casts(): array { return ['data_expiracao' => 'datetime']; }

    public function tipoAlerta() { return $this->belongsTo(TipoAlerta::class, 'tipo_alerta_id'); }
    public function pessoa() { return $this->belongsTo(Pessoa::class, 'pessoa_id'); }
    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function criadoPor() { return $this->belongsTo(Agente::class, 'criado_por'); }
    public function destinatarios() { return $this->hasMany(AlertaDestinatario::class, 'alerta_id'); }

    public function scopeActivos($query) { return $query->where('estado', 'activo'); }
}