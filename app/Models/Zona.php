<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $fillable = ['nome', 'bairro_id', 'nivel_risco'];

    public function bairro() { return $this->belongsTo(Bairro::class, 'bairro_id'); }
}