<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEnvolvimento extends Model
{
    protected $table = 'tipos_envolvimento';
    protected $fillable = ['nome'];
}