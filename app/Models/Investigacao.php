<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Investigacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'investigacoes';

    protected $fillable = [
        'numero_investigacao', 'ocorrencia_id', 'investigador_id',
        'estado_id', 'resumo', 'data_inicio', 'data_fim', 'prazo', 'progresso'
    ];

    protected function casts(): array
    {
        return ['data_inicio' => 'date', 'data_fim' => 'date', 'prazo' => 'date'];
    }

    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function investigador() { return $this->belongsTo(Agente::class, 'investigador_id'); }
    public function estado() { return $this->belongsTo(EstadoInvestigacao::class, 'estado_id'); }
    public function notas() { return $this->hasMany(NotaInvestigacao::class, 'investigacao_id'); }

    public static function gerarNumero(): string
    {
        $prefixo = Configuracao::valor('prefixo_investigacao', 'INV-VNA');
        $ano = date('Y');
        $num = static::whereYear('created_at', $ano)->count() + 1;
        return $prefixo . '-' . $ano . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}