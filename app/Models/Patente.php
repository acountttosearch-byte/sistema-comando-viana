<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patente extends Model
{
    protected $fillable = ['nome', 'abreviatura', 'nivel_hierarquico'];

    public function agentes() { return $this->hasMany(Agente::class, 'patente_id'); }
}