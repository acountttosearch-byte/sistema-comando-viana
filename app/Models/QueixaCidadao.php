<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueixaCidadao extends Model
{
    use HasFactory;

    protected $table = 'queixas_cidadao';
    protected $fillable = ['protocolo', 'nome_cidadao', 'bi', 'telefone', 'email', 'tipo_queixa', 'descricao', 'local', 'ficheiro_anexo', 'estado', 'ocorrencia_id', 'analisado_por', 'justificacao_rejeicao'];

    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function analisadoPor() { return $this->belongsTo(Agente::class, 'analisado_por'); }

    public static function gerarProtocolo(): string
    {
        $ano = date('Y');
        $num = static::whereYear('created_at', $ano)->count() + 1;
        return 'QX-' . $ano . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}