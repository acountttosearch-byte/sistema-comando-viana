<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoInvestigacao extends Model
{
    protected $table = 'estados_investigacao';
    protected $fillable = ['nome', 'cor', 'ordem'];
}