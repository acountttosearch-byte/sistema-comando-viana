<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeolocalizacaoOcorrencia extends Model
{
    protected $table = 'geolocalizacao_ocorrencias';
    protected $fillable = ['ocorrencia_id', 'latitude', 'longitude', 'bairro_id', 'zona_id'];

    public function ocorrencia() { return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id'); }
    public function bairro() { return $this->belongsTo(Bairro::class, 'bairro_id'); }
    public function zona() { return $this->belongsTo(Zona::class, 'zona_id'); }
}