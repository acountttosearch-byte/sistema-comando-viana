<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracao extends Model
{
    protected $table = 'configuracoes';
    protected $fillable = ['chave', 'valor', 'tipo', 'grupo', 'descricao'];

    public static function valor(string $chave, $default = null)
    {
        return Cache::remember("config_{$chave}", 3600, function () use ($chave, $default) {
            $config = static::where('chave', $chave)->first();
            if (!$config) return $default;
            return match ($config->tipo) {
                'boolean' => filter_var($config->valor, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $config->valor,
                'json' => json_decode($config->valor, true),
                default => $config->valor,
            };
        });
    }

    public static function definir(string $chave, $valor): void
    {
        static::updateOrCreate(['chave' => $chave], ['valor' => is_array($valor) ? json_encode($valor) : (string) $valor]);
        Cache::forget("config_{$chave}");
    }
}