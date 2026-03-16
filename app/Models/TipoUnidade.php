<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUnidade extends Model
{
    protected $table = 'tipos_unidade';
    protected $fillable = ['nome', 'descricao'];

    public function unidades()
    {
        return $this->hasMany(Unidade::class, 'tipo_unidade_id');
    }
}