<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaCrime extends Model
{
    protected $table = 'categorias_crime';
    protected $fillable = ['nome', 'descricao'];

    public function tiposCrime() { return $this->hasMany(TipoCrime::class, 'categoria_id'); }
}