<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Detencao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'detencoes';

    protected $fillable = [
        'numero_detencao', 'pessoa_id', 'ocorrencia_id',
        'data_detencao', 'local_detencao', 'agente_responsavel_id',
        'unidade_id', 'motivo', 'estado_id', 'data_libertacao', 'observacoes'
    ];

    protected function casts(): array
    {
        return ['data_detencao' => 'datetime', 'data_libertacao' => 'datetime'];
    }

    public function pessoa() { return $this->belongsTo(Pessoa::class, 'pessoa_id'); }
    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function agenteResponsavel() { return $this->belongsTo(Agente::class, 'agente_responsavel_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function estado() { return $this->belongsTo(EstadoDetencao::class, 'estado_id'); }

    public static function gerarNumero(): string
    {
        $prefixo = Configuracao::valor('prefixo_detencao', 'DT-VNA');
        $ano = date('Y');
        $num = static::whereYear('created_at', $ano)->count() + 1;
        return $prefixo . '-' . $ano . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}