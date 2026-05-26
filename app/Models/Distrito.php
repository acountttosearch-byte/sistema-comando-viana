<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    protected $fillable = ['nome'];

    public function bairros()
    {
        return $this->hasMany(Bairro::class, 'distrito_id');
    }
}
