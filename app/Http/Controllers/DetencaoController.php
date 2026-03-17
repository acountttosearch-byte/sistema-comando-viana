<?php

namespace App\Http\Controllers;

use App\Models\Detencao;
use App\Models\Log;
use Illuminate\Http\Request;

class DetencaoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Detencao::with(['pessoa', 'ocorrencia', 'agenteResponsavel', 'unidade', 'estado']);

        if (!$user->isAdmin() && !$user->isComandante()) $q->where('unidade_id', $user->unidade_id);
        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('data_inicio')) $q->where('data_detencao', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_detencao', '<=', $request->data_fim);

        return response()->json($q->orderByDesc('data_detencao')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pessoa_id' => 'required|exists:pessoas,id',
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'data_detencao' => 'required|date',
            'local_detencao' => 'required|string|max:300',
            'motivo' => 'required|string',
        ]);

        $ag = auth()->user()->agente;
        $dt = Detencao::create([
            'numero_detencao' => Detencao::gerarNumero(),
            'pessoa_id' => $request->pessoa_id,
            'ocorrencia_id' => $request->ocorrencia_id,
            'data_detencao' => $request->data_detencao,
            'local_detencao' => $request->local_detencao,
            'agente_responsavel_id' => $ag->id,
            'unidade_id' => $ag->unidade_id,
            'motivo' => $request->motivo,
            'estado_id' => 1,
            'observacoes' => $request->observacoes,
        ]);

        Log::registar('criar', 'detencoes', $dt->id, "Detenção {$dt->numero_detencao} registada");
        return response()->json(['success' => true, 'message' => 'Detenção registada.', 'detencao' => $dt->load(['pessoa', 'ocorrencia'])], 201);
    }

    public function show(Detencao $detencao)
    {
        return response()->json($detencao->load(['pessoa', 'ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade', 'estado']));
    }

    public function actualizarEstado(Request $request, Detencao $detencao)
    {
        $request->validate(['estado_id' => 'required|exists:estados_detencao,id']);
        $detencao->update([
            'estado_id' => $request->estado_id,
            'data_libertacao' => $request->estado_id == 4 ? now() : $detencao->data_libertacao,
            'observacoes' => $request->observacoes ?? $detencao->observacoes,
        ]);
        Log::registar('editar', 'detencoes', $detencao->id, "Estado da detenção actualizado");
        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    }
}