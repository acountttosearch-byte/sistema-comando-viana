<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Log;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    public function index(Request $request)
    {
        $q = Unidade::with(['tipoUnidade', 'unidadePai']);
        if ($request->filled('tipo_unidade_id')) $q->where('tipo_unidade_id', $request->tipo_unidade_id);
        return response()->json($q->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:200', 'tipo_unidade_id' => 'required|exists:tipos_unidade,id']);
        $u = Unidade::create($request->all());
        Log::registar('criar', 'unidades', $u->id, "Unidade {$u->nome} criada");
        return response()->json(['success' => true, 'unidade' => $u], 201);
    }

    public function update(Request $request, Unidade $unidade)
    {
        $request->validate(['nome' => 'required|string|max:200']);
        $unidade->update($request->all());
        Log::registar('editar', 'unidades', $unidade->id, "Unidade actualizada");
        return response()->json(['success' => true, 'unidade' => $unidade->fresh()]);
    }

    public function toggleEstado(Unidade $unidade)
    {
        $novo = $unidade->estado === 'activo' ? 'inactivo' : 'activo';
        $unidade->update(['estado' => $novo]);
        return response()->json(['success' => true, 'message' => "Unidade {$novo}."]);
    }

    public function esquadras()
    {
        return response()->json(Unidade::esquadras()->activas()->get());
    }

    public function estatisticas(Unidade $unidade)
    {
        return response()->json([
            'agentes_activos' => $unidade->agentes()->activos()->count(),
            'ocorrencias_total' => $unidade->ocorrencias()->count(),
            'viaturas' => $unidade->viaturas()->operacionais()->count(),
        ]);
    }
}