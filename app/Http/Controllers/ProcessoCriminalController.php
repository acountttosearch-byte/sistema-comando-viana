<?php

namespace App\Http\Controllers;

use App\Models\ProcessoCriminal;
use App\Models\Log;
use Illuminate\Http\Request;

class ProcessoCriminalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;
        $q = ProcessoCriminal::with(['ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade']);

        // RBAC
        if (in_array($perfil, ['admin', 'comandante'])) {
            // visão global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where('unidade_id', $user->unidade_id);
        } else {
            if ($agenteId) {
                $q->where('agente_responsavel_id', $agenteId);
            }
        }

        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('unidade_id') && in_array($perfil, ['admin', 'comandante'])) {
            $q->where('unidade_id', $request->unidade_id);
        }
        if ($request->filled('data_inicio')) $q->where('data_abertura', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_abertura', '<=', $request->data_fim);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2
                ->where('numero_processo', 'like', "%$b%")
                ->orWhere('resumo', 'like', "%$b%")
                ->orWhereHas('ocorrencia', fn($q3) => $q3->where('numero_ocorrencia', 'like', "%$b%"))
            );
        }

        return response()->json($q->orderByDesc('data_abertura')->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'resumo' => 'nullable|string',
        ]);

        $agente = auth()->user()->agente;

        // Verificar se já existe processo para esta ocorrência
        $existe = ProcessoCriminal::where('ocorrencia_id', $request->ocorrencia_id)->first();
        if ($existe) {
            return response()->json(['error' => 'Já existe um processo criminal para esta ocorrência.'], 422);
        }

        $proc = ProcessoCriminal::create([
            'numero_processo' => ProcessoCriminal::gerarNumero(),
            'ocorrencia_id' => $request->ocorrencia_id,
            'agente_responsavel_id' => $agente->id,
            'unidade_id' => $agente->unidade_id,
            'estado' => 'em_instrucao',
            'data_abertura' => now(),
            'resumo' => $request->resumo,
            'confidencial' => $request->confidencial ?? false,
        ]);

        Log::registar('criar', 'processos_criminais', $proc->id, "Processo {$proc->numero_processo} aberto");

        return response()->json([
            'success' => true,
            'message' => 'Processo criminal aberto.',
            'processo' => $proc->load(['ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade']),
        ], 201);
    }

    public function show(ProcessoCriminal $processo)
    {
        return response()->json($processo->load([
            'ocorrencia.tipoCrime.categoria', 'ocorrencia.estado',
            'ocorrencia.agenteRegisto', 'ocorrencia.agenteResponsavel',
            'ocorrencia.unidade',
            'ocorrencia.envolvimentos.pessoa', 'ocorrencia.envolvimentos.tipoEnvolvimento',
            'ocorrencia.evidencias.tipoEvidencia',
            'ocorrencia.detencoes.pessoa', 'ocorrencia.detencoes.estado',
            'ocorrencia.investigacoes.investigador', 'ocorrencia.investigacoes.estado',
            'ocorrencia.despachos.agenteDestino',
            'ocorrencia.mandados',
            'agenteResponsavel', 'unidade',
        ]));
    }

    public function update(Request $request, ProcessoCriminal $processo)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;

        if (!in_array($perfil, ['admin', 'comandante', 'chefe_esquadra', 'investigador'])) {
            if ($processo->agente_responsavel_id !== $user->agente?->id) {
                return response()->json(['error' => 'Sem permissão.'], 403);
            }
        }

        $dados = $request->only(['estado', 'resumo', 'parecer_final', 'destino_remessa', 'confidencial']);

        if ($request->estado === 'concluido' && !$processo->data_conclusao) {
            $dados['data_conclusao'] = now();
        }
        if ($request->estado === 'remetido_mp' && !$processo->data_remessa) {
            $dados['data_remessa'] = now();
            $dados['data_conclusao'] = $dados['data_conclusao'] ?? $processo->data_conclusao ?? now();
        }

        $processo->update($dados);
        Log::registar('editar', 'processos_criminais', $processo->id, "Processo actualizado — Estado: {$processo->estado}");

        return response()->json(['success' => true, 'message' => 'Processo actualizado.', 'processo' => $processo->fresh()]);
    }
}
