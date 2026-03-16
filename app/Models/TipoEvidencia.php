<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEvidencia extends Model
{
    protected $table = 'tipos_evidencia';
    protected $fillable = ['nome', 'icone'];

    public function evidencias() { return $this->hasMany(Evidencia::class, 'tipo_evidencia_id'); }
}