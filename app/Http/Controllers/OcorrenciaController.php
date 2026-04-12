<?php

namespace App\Http\Controllers;

use App\Models\Ocorrencia;
use App\Models\EnvolvimentoOcorrencia;
use App\Models\GeolocalizacaoOcorrencia;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OcorrenciaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;
        $q = Ocorrencia::with(['tipoCrime', 'estado', 'agenteResponsavel', 'unidade']);

        // RBAC: filtrar por perfil
        if (in_array($perfil, ['admin', 'comandante'])) {
            // visão global — sem filtro
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where('unidade_id', $user->unidade_id);
        } else {
            // investigador, agente, operador — apenas dados próprios
            if ($agenteId) {
                $q->where(fn($q2) => $q2->where('agente_registo_id', $agenteId)->orWhere('agente_responsavel_id', $agenteId));
            }
        }

        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('prioridade')) $q->where('prioridade', $request->prioridade);
        if ($request->filled('tipo_crime_id')) $q->where('tipo_crime_id', $request->tipo_crime_id);
        if ($request->filled('data_inicio')) $q->where('data_ocorrencia', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_ocorrencia', '<=', $request->data_fim);
        if ($request->filled('unidade_id') && in_array($perfil, ['admin', 'comandante'])) {
            $q->where('unidade_id', $request->unidade_id);
        }
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('numero_ocorrencia', 'like', "%$b%")->orWhere('descricao', 'like', "%$b%")->orWhere('local', 'like', "%$b%"));
        }

        return response()->json($q->orderByDesc('data_ocorrencia')->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_crime_id' => 'required|exists:tipos_crime,id',
            'descricao' => 'required|string',
            'data_ocorrencia' => 'required|date',
            'local' => 'required|string|max:500',
            'prioridade' => 'required|in:baixa,media,alta,critica',
            'unidade_id' => 'required|exists:unidades,id',
        ]);

        return DB::transaction(function () use ($request) {
            $agente = auth()->user()->agente;

            $oc = Ocorrencia::create([
                'numero_ocorrencia' => Ocorrencia::gerarNumero(),
                'tipo_crime_id' => $request->tipo_crime_id,
                'descricao' => $request->descricao,
                'data_ocorrencia' => $request->data_ocorrencia,
                'hora_ocorrencia' => $request->hora_ocorrencia,
                'local' => $request->local,
                'bairro' => $request->bairro,
                'prioridade' => $request->prioridade,
                'estado_id' => 1,
                'agente_registo_id' => $agente->id,
                'agente_responsavel_id' => $request->agente_responsavel_id,
                'unidade_id' => $request->unidade_id,
                'confidencial' => $request->confidencial ?? false,
            ]);

            if ($request->filled('latitude') && $request->filled('longitude')) {
                GeolocalizacaoOcorrencia::create([
                    'ocorrencia_id' => $oc->id,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'bairro_id' => $request->bairro_id,
                ]);
            }

            Log::registar('criar', 'ocorrencias', $oc->id, "Ocorrência {$oc->numero_ocorrencia} registada");

            return response()->json([
                'success' => true, 'message' => 'Ocorrência registada.',
                'ocorrencia' => $oc->load(['tipoCrime', 'estado', 'unidade']),
            ], 201);
        });
    }

    public function show(Ocorrencia $ocorrencia)
    {
        return response()->json($ocorrencia->load([
            'tipoCrime.categoria', 'estado', 'agenteRegisto', 'agenteResponsavel',
            'unidade', 'envolvimentos.pessoa', 'envolvimentos.tipoEnvolvimento',
            'evidencias.tipoEvidencia', 'detencoes.pessoa', 'detencoes.estado',
            'investigacoes.investigador', 'investigacoes.estado',
            'despachos.agenteDestino', 'mandados', 'geolocalizacao',
        ]));
    }

    public function update(Request $request, Ocorrencia $ocorrencia)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;

        // Apenas admin, comandante, chefe_esquadra ou o agente responsável podem editar
        if (!in_array($perfil, ['admin', 'comandante', 'chefe_esquadra'])) {
            if ($ocorrencia->agente_responsavel_id !== $agenteId && $ocorrencia->agente_registo_id !== $agenteId) {
                return response()->json(['error' => 'Sem permissão para editar esta ocorrência.'], 403);
            }
        }

        $ocorrencia->update($request->only(['descricao', 'prioridade', 'estado_id', 'agente_responsavel_id', 'local', 'bairro']));
        Log::registar('editar', 'ocorrencias', $ocorrencia->id, "Ocorrência actualizada");
        return response()->json(['success' => true, 'message' => 'Actualizada.', 'ocorrencia' => $ocorrencia->fresh(['tipoCrime', 'estado', 'unidade'])]);
    }

    public function adicionarEnvolvido(Request $request, Ocorrencia $ocorrencia)
    {
        $request->validate([
            'pessoa_id' => 'required|exists:pessoas,id',
            'tipo_envolvimento_id' => 'required|exists:tipos_envolvimento,id',
        ]);

        EnvolvimentoOcorrencia::create([
            'ocorrencia_id' => $ocorrencia->id,
            'pessoa_id' => $request->pessoa_id,
            'tipo_envolvimento_id' => $request->tipo_envolvimento_id,
            'descricao' => $request->descricao,
        ]);

        Log::registar('criar', 'envolvimento_ocorrencia', $ocorrencia->id, "Pessoa adicionada à ocorrência");
        return response()->json(['success' => true, 'message' => 'Pessoa adicionada.']);
    }
}