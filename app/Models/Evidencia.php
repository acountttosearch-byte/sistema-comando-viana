<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evidencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo', 'ocorrencia_id', 'tipo_evidencia_id', 'descricao',
        'ficheiro', 'localizacao_fisica', 'tamanho_ficheiro',
        'hash_ficheiro', 'agente_registo_id', 'estado'
    ];

    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function tipoEvidencia() { return $this->belongsTo(TipoEvidencia::class, 'tipo_evidencia_id'); }
    public function agenteRegisto() { return $this->belongsTo(Agente::class, 'agente_registo_id'); }
    public function cadeiaCustodia() { return $this->hasMany(CadeiaCustodia::class, 'evidencia_id'); }

    public static function gerarCodigo(): string
    {
        $ano = date('Y');
        $num = static::whereYear('created_at', $ano)->count() + 1;
        return 'EV-' . $ano . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}