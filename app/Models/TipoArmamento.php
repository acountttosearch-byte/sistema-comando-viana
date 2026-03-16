<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoArmamento extends Model
{
    protected $table = 'tipos_armamento';
    protected $fillable = ['nome'];
}