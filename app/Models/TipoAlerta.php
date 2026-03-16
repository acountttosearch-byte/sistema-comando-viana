<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAlerta extends Model
{
    protected $table = 'tipos_alerta';
    protected $fillable = ['nome', 'icone'];
}