<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\EnvolvimentoOcorrencia;
use App\Models\GeolocalizacaoOcorrencia;
use App\Models\Log;
use App\Models\Ocorrencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OcorrenciaController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;
        $q = Ocorrencia::with(['tipoCrime', 'estado', 'agenteResponsavel', 'unidade']);

        if (in_array($perfil, ['admin', 'comandante'], true)) {
            // visao global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where('unidade_id', $this->unidadeAtualId());
        } elseif ($agenteId) {
            $q->where(fn($q2) => $q2->where('agente_registo_id', $agenteId)->orWhere('agente_responsavel_id', $agenteId));
        }

        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('prioridade')) $q->where('prioridade', $request->prioridade);
        if ($request->filled('tipo_crime_id')) $q->where('tipo_crime_id', $request->tipo_crime_id);
        if ($request->filled('data_inicio')) $q->where('data_ocorrencia', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_ocorrencia', '<=', $request->data_fim);
        if ($request->filled('unidade_id') && $this->temVisaoGlobal()) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('numero_ocorrencia', 'like', "%$b%")->orWhere('descricao', 'like', "%$b%")->orWhere('local', 'like', "%$b%"));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderByDesc('data_ocorrencia')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'tipo_crime_id' => 'required|exists:tipos_crime,id',
            'descricao' => 'required|string|min:10|max:5000',
            'data_ocorrencia' => 'required|date|before_or_equal:today',
            'hora_ocorrencia' => 'nullable|date_format:H:i',
            'local' => 'required|string|min:3|max:500',
            'bairro' => 'nullable|string|max:150',
            'prioridade' => ['required', Rule::in(['baixa', 'media', 'alta', 'critica'])],
            'unidade_id' => 'required|exists:unidades,id',
            'agente_responsavel_id' => 'nullable|exists:agentes,id',
            'confidencial' => 'sometimes|boolean',
            'latitude' => 'nullable|required_with:longitude|numeric|between:-18.5,-5.0',
            'longitude' => 'nullable|required_with:latitude|numeric|between:11.0,25.0',
            'bairro_id' => 'nullable|exists:bairros,id',
        ]);

        if (!empty($dados['hora_ocorrencia'])) {
            $momentoOcorrencia = Carbon::createFromFormat('Y-m-d H:i', $dados['data_ocorrencia'] . ' ' . $dados['hora_ocorrencia']);
            abort_if($momentoOcorrencia->isFuture(), 422, 'A hora da ocorrencia nao pode estar no futuro.');
        }

        $this->exigirUnidadePermitida((int) $dados['unidade_id']);

        if (!empty($dados['agente_responsavel_id'])) {
            $agenteResponsavel = Agente::activos()->find($dados['agente_responsavel_id']);
            abort_unless($agenteResponsavel && (int) $agenteResponsavel->unidade_id === (int) $dados['unidade_id'], 422, 'O agente responsavel deve estar activo e pertencer a unidade selecionada.');

            if (!$this->temVisaoGlobal() && !$this->eChefeEsquadra() && (int) $dados['agente_responsavel_id'] !== $this->agenteAtualId()) {
                abort(403, 'Agentes so podem atribuir ocorrencias a si mesmos.');
            }
        }

        return DB::transaction(function () use ($dados) {
            $agente = $this->agenteAtual();
            abort_unless($agente, 422, 'O utilizador autenticado deve estar associado a um agente.');

            $oc = Ocorrencia::create([
                'numero_ocorrencia' => Ocorrencia::gerarNumero(),
                'tipo_crime_id' => $dados['tipo_crime_id'],
                'descricao' => $dados['descricao'],
                'data_ocorrencia' => $dados['data_ocorrencia'],
                'hora_ocorrencia' => $dados['hora_ocorrencia'] ?? null,
                'local' => $dados['local'],
                'bairro' => $dados['bairro'] ?? null,
                'prioridade' => $dados['prioridade'],
                'estado_id' => 1,
                'agente_registo_id' => $agente->id,
                'agente_responsavel_id' => $dados['agente_responsavel_id'] ?? null,
                'unidade_id' => $dados['unidade_id'],
                'confidencial' => $dados['confidencial'] ?? false,
            ]);

            if (!empty($dados['latitude']) && !empty($dados['longitude'])) {
                GeolocalizacaoOcorrencia::create([
                    'ocorrencia_id' => $oc->id,
                    'latitude' => $dados['latitude'],
                    'longitude' => $dados['longitude'],
                    'bairro_id' => $dados['bairro_id'] ?? null,
                ]);
            }

            Log::registar('criar', 'ocorrencias', $oc->id, "Ocorrencia {$oc->numero_ocorrencia} registada");

            return response()->json([
                'success' => true,
                'message' => 'Ocorrencia registada.',
                'ocorrencia' => $oc->load(['tipoCrime', 'estado', 'unidade']),
            ], 201);
        });
    }

    public function show(Ocorrencia $ocorrencia)
    {
        $this->exigirOcorrenciaPermitida($ocorrencia);

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
        $this->exigirOcorrenciaPermitida($ocorrencia);

        $dados = $request->validate([
            'descricao' => 'sometimes|required|string|min:10|max:5000',
            'prioridade' => ['sometimes', 'required', Rule::in(['baixa', 'media', 'alta', 'critica'])],
            'estado_id' => 'sometimes|required|exists:estados_ocorrencia,id',
            'agente_responsavel_id' => 'nullable|exists:agentes,id',
            'local' => 'sometimes|required|string|min:3|max:500',
            'bairro' => 'nullable|string|max:150',
        ]);

        if (array_key_exists('estado_id', $dados) && !$this->temVisaoGlobal() && !$this->eChefeEsquadra()) {
            abort(403, 'Apenas a chefia pode alterar o estado da ocorrencia.');
        }

        if (!empty($dados['agente_responsavel_id'])) {
            $agenteResponsavel = Agente::activos()->find($dados['agente_responsavel_id']);
            abort_unless($agenteResponsavel && (int) $agenteResponsavel->unidade_id === (int) $ocorrencia->unidade_id, 422, 'O agente responsavel deve estar activo e pertencer a unidade da ocorrencia.');
        }

        $ocorrencia->update($dados);
        Log::registar('editar', 'ocorrencias', $ocorrencia->id, 'Ocorrencia actualizada');

        return response()->json([
            'success' => true,
            'message' => 'Actualizada.',
            'ocorrencia' => $ocorrencia->fresh(['tipoCrime', 'estado', 'unidade']),
        ]);
    }

    public function adicionarEnvolvido(Request $request, Ocorrencia $ocorrencia)
    {
        $this->exigirOcorrenciaPermitida($ocorrencia);

        $dados = $request->validate([
            'pessoa_id' => 'required|exists:pessoas,id',
            'tipo_envolvimento_id' => 'required|exists:tipos_envolvimento,id',
            'descricao' => 'nullable|string|max:500',
        ]);

        EnvolvimentoOcorrencia::create([
            'ocorrencia_id' => $ocorrencia->id,
            'pessoa_id' => $dados['pessoa_id'],
            'tipo_envolvimento_id' => $dados['tipo_envolvimento_id'],
            'descricao' => $dados['descricao'] ?? null,
        ]);

        Log::registar('criar', 'envolvimento_ocorrencia', $ocorrencia->id, 'Pessoa adicionada a ocorrencia');

        return response()->json(['success' => true, 'message' => 'Pessoa adicionada.']);
    }
}
