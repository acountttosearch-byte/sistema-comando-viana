<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    protected $table = 'permissoes';
    protected $fillable = ['nome', 'descricao', 'modulo'];

    public function perfis()
    {
        return $this->belongsToMany(Perfil::class, 'perfil_permissoes', 'permissao_id', 'perfil_id');
    }
}