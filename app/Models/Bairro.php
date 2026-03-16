<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bairro extends Model
{
    protected $fillable = ['nome', 'municipio', 'unidade_responsavel_id'];

    public function unidadeResponsavel() { return $this->belongsTo(Unidade::class, 'unidade_responsavel_id'); }
    public function zonas() { return $this->hasMany(Zona::class, 'bairro_id'); }
    public function geolocalizacoes() { return $this->hasMany(GeolocalizacaoOcorrencia::class, 'bairro_id'); }
}