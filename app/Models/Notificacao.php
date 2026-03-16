<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    protected $table = 'notificacoes';
    protected $fillable = ['user_id', 'tipo', 'titulo', 'mensagem', 'link', 'lida', 'data_leitura'];
    protected function casts(): array { return ['lida' => 'boolean', 'data_leitura' => 'datetime']; }

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function scopeNaoLidas($query) { return $query->where('lida', false); }
}