<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidade extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'tipo_unidade_id', 'unidade_pai_id',
        'endereco', 'municipio', 'telefone', 'email', 'estado'
    ];

    public function tipoUnidade() { return $this->belongsTo(TipoUnidade::class, 'tipo_unidade_id'); }
    public function unidadePai() { return $this->belongsTo(Unidade::class, 'unidade_pai_id'); }
    public function subunidades() { return $this->hasMany(Unidade::class, 'unidade_pai_id'); }
    public function agentes() { return $this->hasMany(Agente::class, 'unidade_id'); }
    public function ocorrencias() { return $this->hasMany(Ocorrencia::class, 'unidade_id'); }
    public function viaturas() { return $this->hasMany(Viatura::class, 'unidade_id'); }
    public function armamento() { return $this->hasMany(Armamento::class, 'unidade_id'); }
    public function patrulhas() { return $this->hasMany(Patrulha::class, 'unidade_id'); }
    public function zonasPatrulha() { return $this->hasMany(ZonaPatrulha::class, 'unidade_id'); }
    public function bairros() { return $this->hasMany(Bairro::class, 'unidade_responsavel_id'); }

    public function scopeActivas($query) { return $query->where('estado', 'activo'); }
    public function scopeEsquadras($query) { return $query->where('tipo_unidade_id', 2); }
    public function scopePostos($query) { return $query->where('tipo_unidade_id', 3); }

    public function getTotalAgentesAttribute()
    {
        return $this->agentes()->where('estado', 'activo')->count();
    }
}