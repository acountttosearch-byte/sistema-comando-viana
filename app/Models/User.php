<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'email', 'password', 'perfil_id', 'estado',
        'ultimo_acesso', 'ip_ultimo_acesso'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acesso' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

    public function agente()
    {
        return $this->hasOne(Agente::class, 'user_id');
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'user_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    public function temPermissao(string $permissao): bool
    {
        return $this->perfil->temPermissao($permissao);
    }

    public function temPerfil(string $nomePerfil): bool
    {
        return $this->perfil->nome === $nomePerfil;
    }

    public function temAlgumPerfil(array $perfis): bool
    {
        return in_array($this->perfil->nome, $perfis);
    }

    public function isAdmin(): bool
    {
        return $this->perfil->nome === 'admin';
    }

    public function isComandante(): bool
    {
        return $this->perfil->nome === 'comandante';
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function getNomeAttribute()
    {
        return $this->agente?->nome ?? explode('@', $this->email)[0];
    }

    public function getUnidadeIdAttribute()
    {
        return $this->agente?->unidade_id;
    }
}