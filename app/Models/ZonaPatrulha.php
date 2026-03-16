<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaPatrulha extends Model
{
    protected $table = 'zonas_patrulha';
    protected $fillable = ['nome', 'descricao', 'unidade_id', 'nivel_risco'];

    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function patrulhas() { return $this->hasMany(Patrulha::class, 'zona_id'); }
}