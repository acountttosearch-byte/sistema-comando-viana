<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Viatura extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['matricula', 'marca', 'modelo', 'ano', 'cor', 'unidade_id', 'estado', 'quilometragem'];

    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function atribuicoes() { return $this->hasMany(ViaturaAtribuicao::class, 'viatura_id'); }
    public function manutencoes() { return $this->hasMany(ViaturaManutencao::class, 'viatura_id'); }
    public function patrulhas() { return $this->hasMany(Patrulha::class, 'viatura_id'); }

    public function scopeOperacionais($query) { return $query->where('estado', 'operacional'); }
    public function scopeDaUnidade($query, $uid) { return $query->where('unidade_id', $uid); }
}