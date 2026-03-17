<?php

namespace App\Http\Controllers;

use App\Models\Armamento;
use App\Models\ArmamentoAtribuicao;
use App\Models\Log;
use Illuminate\Http\Request;

class ArmamentoController extends Controller
{
    public function index(Request $request)
    {
        $q = Armamento::with(['tipoArmamento', 'unidade', 'atribuicaoActual.agente']);
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        return response()->json($q->orderBy('numero_serie')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_armamento_id' => 'required|exists:tipos_armamento,id',
            'numero_serie' => 'required|string|unique:armamento,numero_serie',
            'unidade_id' => 'required|exists:unidades,id',
        ]);
        $a = Armamento::create($request->all());
        Log::registar('criar', 'armamento', $a->id, "Armamento registado");
        return response()->json(['success' => true, 'armamento' => $a], 201);
    }

    public function atribuir(Request $request, Armamento $armamento)
    {
        $request->validate(['agente_id' => 'required|exists:agentes,id']);
        ArmamentoAtribuicao::create([
            'armamento_id' => $armamento->id, 'agente_id' => $request->agente_id,
            'data_atribuicao' => now(), 'estado' => 'atribuido',
        ]);
        Log::registar('criar', 'armamento_atribuicoes', $armamento->id, "Arma atribuída");
        return response()->json(['success' => true, 'message' => 'Armamento atribuído.']);
    }

    public function devolver(Armamento $armamento)
    {
        $at = $armamento->atribuicaoActual;
        if ($at) $at->update(['estado' => 'devolvido', 'data_devolucao' => now()]);
        return response()->json(['success' => true, 'message' => 'Armamento devolvido.']);
    }
}