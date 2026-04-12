<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\AlertaDestinatario;
use App\Models\Unidade;
use App\Models\Log;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $q = Alerta::with(['tipoAlerta', 'pessoa', 'criadoPor'])->orderByDesc('created_at');
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        return response()->json($q->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_alerta_id' => 'required|exists:tipos_alerta,id',
            'titulo' => 'required|string|max:200',
            'descricao' => 'required|string',
            'prioridade' => 'required|in:urgente,alta,normal',
        ]);

        $alerta = Alerta::create([
            'tipo_alerta_id' => $request->tipo_alerta_id,
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'prioridade' => $request->prioridade,
            'pessoa_id' => $request->pessoa_id,
            'ocorrencia_id' => $request->ocorrencia_id,
            'estado' => 'activo',
            'criado_por' => auth()->user()->agente->id,
            'data_expiracao' => $request->data_expiracao,
        ]);

        foreach (Unidade::activas()->pluck('id') as $uid) {
            AlertaDestinatario::create(['alerta_id' => $alerta->id, 'unidade_id' => $uid]);
        }

        Log::registar('criar', 'alertas', $alerta->id, "Alerta emitido");
        return response()->json(['success' => true, 'alerta' => $alerta], 201);
    }

    public function confirmarVisualizacao(Alerta $alerta)
    {
        $ag = auth()->user()->agente;
        AlertaDestinatario::where('alerta_id', $alerta->id)->where('unidade_id', $ag->unidade_id)
            ->update(['visualizado' => true, 'data_visualizacao' => now(), 'visualizado_por' => $ag->id]);
        return response()->json(['success' => true]);
    }

    public function resolver(Alerta $alerta)
    {
        $alerta->update(['estado' => 'resolvido']);
        Log::registar('editar', 'alertas', $alerta->id, "Alerta resolvido");
        return response()->json(['success' => true, 'message' => 'Alerta resolvido.']);
    }
}