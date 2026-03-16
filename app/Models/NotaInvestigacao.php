<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaInvestigacao extends Model
{
    protected $table = 'notas_investigacao';
    protected $fillable = ['investigacao_id', 'agente_id', 'titulo', 'conteudo', 'confidencial'];

    protected function casts(): array { return ['confidencial' => 'boolean']; }

    public function investigacao() { return $this->belongsTo(Investigacao::class, 'investigacao_id'); }
    public function agente() { return $this->belongsTo(Agente::class, 'agente_id'); }
}