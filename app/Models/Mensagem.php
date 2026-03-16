<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mensagem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mensagens';
    protected $fillable = ['remetente_id', 'destinatario_id', 'unidade_destino_id', 'titulo', 'mensagem', 'ficheiro_anexo', 'lida', 'data_leitura', 'prioridade'];
    protected function casts(): array { return ['lida' => 'boolean', 'data_leitura' => 'datetime']; }

    public function remetente() { return $this->belongsTo(Agente::class, 'remetente_id'); }
    public function destinatario() { return $this->belongsTo(Agente::class, 'destinatario_id'); }
    public function unidadeDestino() { return $this->belongsTo(Unidade::class, 'unidade_destino_id'); }

    public function scopeNaoLidas($query) { return $query->where('lida', false); }
}