<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatrulhaIncidente extends Model
{
    protected $table = 'patrulha_incidentes';
    protected $fillable = ['patrulha_id', 'ocorrencia_id', 'hora_registo', 'local', 'descricao'];

    public function patrulha() { return $this->belongsTo(Patrulha::class, 'patrulha_id'); }
    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
}