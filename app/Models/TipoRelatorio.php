<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoRelatorio extends Model
{
    protected $table = 'tipos_relatorio';
    protected $fillable = ['nome'];
}