<?php

namespace App\Http\Controllers;

use App\Models\Mandado;
use App\Models\Log;
use Illuminate\Http\Request;

class MandadoController extends Controller
{
    public function index(Request $request)
    {
        $q = Mandado::with(['tipoMandado', 'ocorrencia', 'pessoa', 'agenteResponsavel']);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        return response()->json($q->orderByDesc('data_emissao')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_mandado_id' => 'required|exists:tipos_mandado,id',
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'descricao' => 'required|string',
            'data_emissao' => 'required|date',
        ]);

        $m = Mandado::create(array_merge($request->all(), [
            'numero_mandado' => 'MD-' . date('Y') . '-' . str_pad(Mandado::count() + 1, 5, '0', STR_PAD_LEFT),
            'estado' => 'pendente',
            'agente_responsavel_id' => auth()->user()->agente->id,
        ]));
        Log::registar('criar', 'mandados', $m->id, "Mandado emitido");
        return response()->json(['success' => true, 'mandado' => $m], 201);
    }

    public function actualizarEstado(Request $request, Mandado $mandado)
    {
        $request->validate(['estado' => 'required|in:pendente,executado,expirado,cancelado']);
        $mandado->update(['estado' => $request->estado]);
        return response()->json(['success' => true]);
    }
}