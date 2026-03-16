<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome', 'alcunha', 'data_nascimento', 'bi', 'sexo',
        'nacionalidade', 'telefone', 'morada', 'bairro',
        'foto', 'caracteristicas_fisicas', 'observacoes'
    ];

    protected function casts(): array { return ['data_nascimento' => 'date']; }

    public function envolvimentos() { return $this->hasMany(EnvolvimentoOcorrencia::class, 'pessoa_id'); }
    public function detencoes() { return $this->hasMany(Detencao::class, 'pessoa_id'); }
    public function mandados() { return $this->hasMany(Mandado::class, 'pessoa_id'); }
    public function alertas() { return $this->hasMany(Alerta::class, 'pessoa_id'); }

    public function ocorrencias()
    {
        return $this->belongsToMany(Ocorrencia::class, 'envolvimento_ocorrencia', 'pessoa_id', 'ocorrencia_id')
                     ->withPivot('tipo_envolvimento_id', 'descricao')->withTimestamps();
    }
}