<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = ['user_id', 'acao', 'tabela', 'registro_id', 'descricao', 'dados_anteriores', 'dados_novos', 'ip', 'user_agent'];
    protected function casts(): array { return ['dados_anteriores' => 'array', 'dados_novos' => 'array']; }

    public function user() { return $this->belongsTo(User::class, 'user_id'); }

    public static function registar(string $acao, string $tabela = null, int $registroId = null, string $descricao = null, array $dadosAnteriores = null, array $dadosNovos = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'acao' => $acao, 'tabela' => $tabela,
            'registro_id' => $registroId, 'descricao' => $descricao,
            'dados_anteriores' => $dadosAnteriores, 'dados_novos' => $dadosNovos,
            'ip' => request()->ip(), 'user_agent' => request()->userAgent(),
        ]);
    }
}