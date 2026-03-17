<?php

namespace App\Http\Controllers;

use App\Models\Viatura;
use App\Models\ViaturaAtribuicao;
use App\Models\Log;
use Illuminate\Http\Request;

class ViaturaController extends Controller
{
    public function index(Request $request)
    {
        $q = Viatura::with('unidade');
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        return response()->json($q->orderBy('matricula')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula' => 'required|string|max:20|unique:viaturas,matricula',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'unidade_id' => 'required|exists:unidades,id',
        ]);
        $v = Viatura::create($request->all());
        Log::registar('criar', 'viaturas', $v->id, "Viatura {$v->matricula} registada");
        return response()->json(['success' => true, 'viatura' => $v], 201);
    }

    public function atribuir(Request $request, Viatura $viatura)
    {
        $request->validate(['agente_id' => 'required|exists:agentes,id', 'quilometragem_saida' => 'required|integer']);
        $at = ViaturaAtribuicao::create([
            'viatura_id' => $viatura->id, 'agente_id' => $request->agente_id,
            'data_saida' => now(), 'quilometragem_saida' => $request->quilometragem_saida,
        ]);
        return response()->json(['success' => true, 'atribuicao' => $at], 201);
    }

    public function devolver(Request $request, ViaturaAtribuicao $atribuicao)
    {
        $atribuicao->update([
            'data_retorno' => now(),
            'quilometragem_retorno' => $request->quilometragem_retorno,
            'observacoes' => $request->observacoes,
        ]);
        if ($request->quilometragem_retorno) {
            $atribuicao->viatura->update(['quilometragem' => $request->quilometragem_retorno]);
        }
        return response()->json(['success' => true, 'message' => 'Viatura devolvida.']);
    }
}