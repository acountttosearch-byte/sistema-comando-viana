<?php

namespace App\Http\Controllers;

use App\Models\Patrulha;
use App\Models\PatrulhaIncidente;
use App\Models\Log;
use Illuminate\Http\Request;

class PatrulhaController extends Controller
{
    public function index(Request $request)
    {
        $q = Patrulha::with(['turno', 'zona', 'viatura', 'agenteLider', 'unidade', 'agentes']);
        if ($request->filled('data')) $q->where('data', $request->data);
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        return response()->json($q->orderByDesc('data')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|date',
            'turno_id' => 'required|exists:turnos,id',
            'zona_id' => 'required|exists:zonas_patrulha,id',
            'agente_lider_id' => 'required|exists:agentes,id',
            'unidade_id' => 'required|exists:unidades,id',
            'agentes' => 'required|array|min:1',
        ]);

        $p = Patrulha::create($request->except('agentes'));
        $agentes = collect($request->agentes)->mapWithKeys(fn($id) => [$id => ['funcao' => $id == $request->agente_lider_id ? 'lider' : 'apoio']]);
        $p->agentes()->attach($agentes);

        Log::registar('criar', 'patrulhas', $p->id, "Patrulha planeada");
        return response()->json(['success' => true, 'patrulha' => $p->load('agentes')], 201);
    }

    public function actualizarEstado(Request $request, Patrulha $patrulha)
    {
        $request->validate(['estado' => 'required|in:planeada,em_curso,concluida,cancelada']);
        $dados = ['estado' => $request->estado];
        if ($request->estado === 'em_curso') $dados['hora_inicio'] = now()->format('H:i');
        if ($request->estado === 'concluida') $dados['hora_fim'] = now()->format('H:i');
        $patrulha->update($dados);
        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    }

    public function registarIncidente(Request $request, Patrulha $patrulha)
    {
        $request->validate(['descricao' => 'required|string']);
        $inc = PatrulhaIncidente::create([
            'patrulha_id' => $patrulha->id,
            'ocorrencia_id' => $request->ocorrencia_id,
            'hora_registo' => now()->format('H:i'),
            'local' => $request->local,
            'descricao' => $request->descricao,
        ]);
        return response()->json(['success' => true, 'incidente' => $inc], 201);
    }
}