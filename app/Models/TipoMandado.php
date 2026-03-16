<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMandado extends Model
{
    protected $table = 'tipos_mandado';
    protected $fillable = ['nome'];
}