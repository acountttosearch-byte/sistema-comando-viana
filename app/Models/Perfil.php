<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfis';
    protected $fillable = ['nome', 'descricao'];

    public function users() { return $this->hasMany(User::class, 'perfil_id'); }

    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'perfil_permissoes', 'perfil_id', 'permissao_id');
    }

    public function temPermissao(string $nomePermissao): bool
    {
        return $this->permissoes()->where('nome', $nomePermissao)->exists();
    }
}