<?php

namespace App\Http\Controllers;

use App\Models\Investigacao;
use App\Models\NotaInvestigacao;
use App\Models\Log;
use Illuminate\Http\Request;

class InvestigacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Investigacao::with(['ocorrencia.tipoCrime', 'investigador', 'estado']);
        if ($user->temPerfil('investigador')) $q->where('investigador_id', $user->agente->id);
        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('investigador_id')) $q->where('investigador_id', $request->investigador_id);
        if ($request->filled('data_inicio')) $q->where('data_inicio', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_inicio', '<=', $request->data_fim);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('numero_investigacao', 'like', "%$b%")->orWhere('resumo', 'like', "%$b%"));
        }
        return response()->json($q->orderByDesc('created_at')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'investigador_id' => 'required|exists:agentes,id',
        ]);

        $inv = Investigacao::create([
            'numero_investigacao' => Investigacao::gerarNumero(),
            'ocorrencia_id' => $request->ocorrencia_id,
            'investigador_id' => $request->investigador_id,
            'estado_id' => 1,
            'resumo' => $request->resumo,
            'data_inicio' => now(),
            'prazo' => $request->prazo,
            'progresso' => 0,
        ]);

        $inv->ocorrencia->update(['estado_id' => 4]);
        Log::registar('criar', 'investigacoes', $inv->id, "Investigação aberta");
        return response()->json(['success' => true, 'message' => 'Investigação aberta.', 'investigacao' => $inv->load(['ocorrencia', 'investigador', 'estado'])], 201);
    }

    public function update(Request $request, Investigacao $investigacao)
    {
        $investigacao->update($request->only(['estado_id', 'progresso', 'resumo']));
        if ($request->estado_id == 4) $investigacao->update(['data_fim' => now()]);
        Log::registar('editar', 'investigacoes', $investigacao->id, "Investigação actualizada");
        return response()->json(['success' => true, 'message' => 'Actualizada.']);
    }

    public function adicionarNota(Request $request, Investigacao $investigacao)
    {
        $request->validate(['titulo' => 'required|string|max:200', 'conteudo' => 'required|string']);
        $nota = NotaInvestigacao::create([
            'investigacao_id' => $investigacao->id,
            'agente_id' => auth()->user()->agente->id,
            'titulo' => $request->titulo,
            'conteudo' => $request->conteudo,
            'confidencial' => $request->confidencial ?? false,
        ]);
        return response()->json(['success' => true, 'nota' => $nota], 201);
    }

    public function notas(Investigacao $investigacao)
    {
        return response()->json($investigacao->notas()->with('agente')->orderByDesc('created_at')->get());
    }
}