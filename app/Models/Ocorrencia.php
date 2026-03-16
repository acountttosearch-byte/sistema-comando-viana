<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ocorrencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_ocorrencia', 'tipo_crime_id', 'descricao',
        'data_ocorrencia', 'hora_ocorrencia', 'local', 'bairro',
        'prioridade', 'estado_id', 'agente_registo_id',
        'agente_responsavel_id', 'unidade_id', 'confidencial'
    ];

    protected function casts(): array
    {
        return ['data_ocorrencia' => 'date', 'confidencial' => 'boolean'];
    }

    public function tipoCrime() { return $this->belongsTo(TipoCrime::class, 'tipo_crime_id'); }
    public function estado() { return $this->belongsTo(EstadoOcorrencia::class, 'estado_id'); }
    public function agenteRegisto() { return $this->belongsTo(Agente::class, 'agente_registo_id'); }
    public function agenteResponsavel() { return $this->belongsTo(Agente::class, 'agente_responsavel_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function detencoes() { return $this->hasMany(Detencao::class, 'ocorrencia_id'); }
    public function evidencias() { return $this->hasMany(Evidencia::class, 'ocorrencia_id'); }
    public function investigacoes() { return $this->hasMany(Investigacao::class, 'ocorrencia_id'); }
    public function despachos() { return $this->hasMany(Despacho::class, 'ocorrencia_id'); }
    public function mandados() { return $this->hasMany(Mandado::class, 'ocorrencia_id'); }
    public function geolocalizacao() { return $this->hasOne(GeolocalizacaoOcorrencia::class, 'ocorrencia_id'); }

    public function envolvimentos() { return $this->hasMany(EnvolvimentoOcorrencia::class, 'ocorrencia_id'); }
    public function suspeitos() { return $this->envolvimentos()->where('tipo_envolvimento_id', 1); }
    public function vitimas() { return $this->envolvimentos()->where('tipo_envolvimento_id', 2); }
    public function testemunhas() { return $this->envolvimentos()->where('tipo_envolvimento_id', 3); }

    public function pessoas()
    {
        return $this->belongsToMany(Pessoa::class, 'envolvimento_ocorrencia', 'ocorrencia_id', 'pessoa_id')
                     ->withPivot('tipo_envolvimento_id', 'descricao')->withTimestamps();
    }

    public function scopeDaUnidade($query, $uid) { return $query->where('unidade_id', $uid); }
    public function scopeDoMes($query, $m = null, $a = null) {
        return $query->whereMonth('data_ocorrencia', $m ?? now()->month)->whereYear('data_ocorrencia', $a ?? now()->year);
    }

    public static function gerarNumero(): string
    {
        $prefixo = Configuracao::valor('prefixo_ocorrencia', 'OC-VNA');
        $ano = date('Y');
        $num = static::whereYear('created_at', $ano)->count() + 1;
        return $prefixo . '-' . $ano . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}