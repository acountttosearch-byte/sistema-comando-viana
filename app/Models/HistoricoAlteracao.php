<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoAlteracao extends Model
{
    protected $table = 'historico_alteracoes';

    protected $fillable = [
        'tabela', 'registro_id', 'campo',
        'valor_antigo', 'valor_novo', 'alterado_por'
    ];

    public function alteradoPor()
    {
        return $this->belongsTo(User::class, 'alterado_por');
    }
}