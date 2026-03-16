<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'nome', 'nip', 'bi', 'data_nascimento', 'sexo',
        'telefone', 'morada', 'foto', 'patente_id', 'cargo',
        'unidade_id', 'data_admissao', 'estado'
    ];

    protected function casts(): array
    {
        return ['data_nascimento' => 'date', 'data_admissao' => 'date'];
    }

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function patente() { return $this->belongsTo(Patente::class, 'patente_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function ocorrenciasRegistadas() { return $this->hasMany(Ocorrencia::class, 'agente_registo_id'); }
    public function ocorrenciasResponsavel() { return $this->hasMany(Ocorrencia::class, 'agente_responsavel_id'); }
    public function detencoes() { return $this->hasMany(Detencao::class, 'agente_responsavel_id'); }
    public function investigacoes() { return $this->hasMany(Investigacao::class, 'investigador_id'); }
    public function escalaTurnos() { return $this->hasMany(EscalaTurno::class, 'agente_id'); }
    public function patrulhasLideradas() { return $this->hasMany(Patrulha::class, 'agente_lider_id'); }
    public function mensagensEnviadas() { return $this->hasMany(Mensagem::class, 'remetente_id'); }
    public function mensagensRecebidas() { return $this->hasMany(Mensagem::class, 'destinatario_id'); }
    public function alertasCriados() { return $this->hasMany(Alerta::class, 'criado_por'); }
    public function despachosRecebidos() { return $this->hasMany(Despacho::class, 'despachado_para'); }
    public function despachosEmitidos() { return $this->hasMany(Despacho::class, 'despachado_por'); }
    public function evidenciasRegistadas() { return $this->hasMany(Evidencia::class, 'agente_registo_id'); }

    public function patrulhas()
    {
        return $this->belongsToMany(Patrulha::class, 'patrulha_agentes', 'agente_id', 'patrulha_id')
                     ->withPivot('funcao')->withTimestamps();
    }

    public function armamentoAtribuido()
    {
        return $this->hasMany(ArmamentoAtribuicao::class, 'agente_id')->where('estado', 'atribuido');
    }

    public function viaturasAtribuidas() { return $this->hasMany(ViaturaAtribuicao::class, 'agente_id'); }

    public function scopeActivos($query) { return $query->where('estado', 'activo'); }
    public function scopeDaUnidade($query, $unidadeId) { return $query->where('unidade_id', $unidadeId); }

    public function getPatenteNomeAttribute() { return $this->patente?->nome ?? '-'; }
    public function getUnidadeNomeAttribute() { return $this->unidade?->nome ?? '-'; }
    public function getPerfilNomeAttribute() { return $this->user?->perfil?->nome ?? '-'; }
}