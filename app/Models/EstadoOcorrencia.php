<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoOcorrencia extends Model
{
    protected $table = 'estados_ocorrencia';
    protected $fillable = ['nome', 'cor', 'ordem'];

    public function ocorrencias() { return $this->hasMany(Ocorrencia::class, 'estado_id'); }
}