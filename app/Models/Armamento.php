<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Armamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'armamento';
    protected $fillable = ['tipo_armamento_id', 'marca', 'modelo', 'numero_serie', 'calibre', 'unidade_id', 'estado'];

    public function tipoArmamento() { return $this->belongsTo(TipoArmamento::class, 'tipo_armamento_id'); }
    public function unidade() { return $this->belongsTo(Unidade::class, 'unidade_id'); }
    public function atribuicoes() { return $this->hasMany(ArmamentoAtribuicao::class, 'armamento_id'); }

    public function atribuicaoActual()
    {
        return $this->hasOne(ArmamentoAtribuicao::class, 'armamento_id')->where('estado', 'atribuido')->latest();
    }
}