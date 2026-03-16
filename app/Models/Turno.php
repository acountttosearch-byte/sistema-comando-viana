<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $fillable = ['nome', 'hora_inicio', 'hora_fim'];

    public function escalas() { return $this->hasMany(EscalaTurno::class, 'turno_id'); }
    public function patrulhas() { return $this->hasMany(Patrulha::class, 'turno_id'); }
}