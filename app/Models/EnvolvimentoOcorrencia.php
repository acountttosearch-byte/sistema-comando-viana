<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvolvimentoOcorrencia extends Model
{
    protected $table = 'envolvimento_ocorrencia';
    protected $fillable = ['ocorrencia_id', 'pessoa_id', 'tipo_envolvimento_id', 'descricao'];

    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function pessoa() { return $this->belongsTo(Pessoa::class, 'pessoa_id'); }
    public function tipoEnvolvimento() { return $this->belongsTo(TipoEnvolvimento::class, 'tipo_envolvimento_id'); }
}