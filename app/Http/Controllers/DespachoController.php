<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\Log;
use Illuminate\Http\Request;

class DespachoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;
        $q = Despacho::with(['ocorrencia.tipoCrime', 'agenteDestino', 'agenteOrigem', 'unidade']);

        // RBAC: admin/comandante/chefe = visão ampla, outros = só despachos recebidos
        if (in_array($perfil, ['admin', 'comandante'])) {
            // visão global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where(fn($q2) => $q2->where('unidade_destino', $user->unidade_id)
                ->orWhere('despachado_por', $agenteId));
        } else {
            // investigador, agente, operador — apenas despachos que lhe são destinados
            if ($agenteId) $q->where('despachado_para', $agenteId);
        }

        if ($request->filled('estado')) $q->where('estado', $request->estado);
        return response()->json($q->orderByDesc('data_despacho')->paginate(20));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;

        // Apenas admin, comandante, chefe_esquadra podem criar despachos
        if (!in_array($perfil, ['admin', 'comandante', 'chefe_esquadra'])) {
            return response()->json(['error' => 'Sem permissão para criar despachos.'], 403);
        }

        $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'prioridade' => 'required|in:baixa,media,alta,critica',
            'despachado_para' => 'required|exists:agentes,id',
            'unidade_destino' => 'required|exists:unidades,id',
        ]);

        $d = Despacho::create([
            'ocorrencia_id' => $request->ocorrencia_id,
            'prioridade' => $request->prioridade,
            'despachado_para' => $request->despachado_para,
            'despachado_por' => auth()->user()->agente->id,
            'unidade_destino' => $request->unidade_destino,
            'instrucoes' => $request->instrucoes,
            'estado' => 'pendente',
            'data_despacho' => now(),
        ]);

        $d->ocorrencia->update(['estado_id' => 3, 'agente_responsavel_id' => $request->despachado_para]);
        Log::registar('criar', 'despachos', $d->id, "Despacho criado");
        return response()->json(['success' => true, 'despacho' => $d], 201);
    }

    public function responder(Request $request, Despacho $despacho)
    {
        $request->validate(['estado' => 'required|in:aceite,em_curso,concluido,rejeitado']);
        $despacho->update([
            'estado' => $request->estado,
            'data_resposta' => now(),
            'tempo_resposta_minutos' => now()->diffInMinutes($despacho->data_despacho),
        ]);
        return response()->json(['success' => true, 'message' => 'Despacho respondido.']);
    }
}