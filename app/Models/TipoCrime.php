<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCrime extends Model
{
    protected $table = 'tipos_crime';
    protected $fillable = ['nome', 'codigo', 'categoria_id', 'gravidade', 'descricao'];

    public function categoria() { return $this->belongsTo(CategoriaCrime::class, 'categoria_id'); }
    public function ocorrencias() { return $this->hasMany(Ocorrencia::class, 'tipo_crime_id'); }
}