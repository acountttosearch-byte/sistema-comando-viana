<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoDetencao extends Model
{
    protected $table = 'estados_detencao';
    protected $fillable = ['nome'];

    public function detencoes() { return $this->hasMany(Detencao::class, 'estado_id'); }
}