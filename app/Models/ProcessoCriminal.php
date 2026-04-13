<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcessoCriminal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'processos_criminais';

    protected $fillable = [
        'numero_processo', 'ocorrencia_id', 'agente_responsavel_id',
        'unidade_id', 'estado', 'data_abertura', 'data_conclusao',
        'data_remessa', 'resumo', 'parecer_final', 'destino_remessa',
        'confidencial'
    ];

    protected function casts(): array
    {
        return [
            'data_abertura' => 'date',
            'data_conclusao' => 'date',
            'data_remessa' => 'date',
            'confidencial' => 'boolean',
        ];
    }

    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function agenteResponsavel() { return $this->belongsTo(Agente::class, 'agente_responsavel_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }

    public static function gerarNumero(): string
    {
        $prefixo = Configuracao::valor('prefixo_processo', 'PC-VNA');
        $ano = date('Y');
        $num = static::whereYear('created_at', $ano)->count() + 1;
        return $prefixo . '-' . $ano . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
