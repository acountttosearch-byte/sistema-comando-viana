<?php

namespace App\Http\Controllers;

use App\Models\EscalaTurno;
use App\Models\Turno;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    public function turnos()
    {
        return response()->json(Turno::all());
    }

    public function escala(Request $request)
    {
        $q = EscalaTurno::with(['agente', 'turno', 'unidade']);
        if ($request->filled('data')) $q->where('data', $request->data);
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);
        return response()->json($q->orderBy('data')->get());
    }

    public function definirEscala(Request $request)
    {
        $request->validate(['escalas' => 'required|array', 'escalas.*.agente_id' => 'required|exists:agentes,id', 'escalas.*.turno_id' => 'required|exists:turnos,id', 'escalas.*.data' => 'required|date', 'escalas.*.unidade_id' => 'required|exists:unidades,id']);
        foreach ($request->escalas as $e) {
            EscalaTurno::updateOrCreate(
                ['agente_id' => $e['agente_id'], 'data' => $e['data']],
                ['turno_id' => $e['turno_id'], 'unidade_id' => $e['unidade_id'], 'estado' => 'confirmado']
            );
        }
        return response()->json(['success' => true, 'message' => 'Escala definida.']);
    }
}