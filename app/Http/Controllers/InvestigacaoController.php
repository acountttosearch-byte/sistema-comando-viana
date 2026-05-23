<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\Investigacao;
use App\Models\Log;
use App\Models\NotaInvestigacao;
use App\Models\Ocorrencia;
use Illuminate\Http\Request;

class InvestigacaoController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Investigacao::with(['ocorrencia.tipoCrime', 'investigador', 'estado']);

        if ($this->temVisaoGlobal()) {
            // visao global
        } elseif ($this->eChefeEsquadra()) {
            $q->whereHas('ocorrencia', fn($q2) => $q2->where('unidade_id', $this->unidadeAtualId()));
        } elseif ($user->agente) {
            $q->where('investigador_id', $user->agente->id);
        }

        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('investigador_id') && $this->temVisaoGlobal()) $q->where('investigador_id', $request->investigador_id);
        if ($request->filled('data_inicio')) $q->where('data_inicio', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_inicio', '<=', $request->data_fim);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('numero_investigacao', 'like', "%$b%")->orWhere('resumo', 'like', "%$b%"));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderByDesc('created_at')->paginate($perPage));
    }

    public function show(Investigacao $investigacao)
    {
        $this->exigirInvestigacaoPermitida($investigacao);

        return response()->json($investigacao->load([
            'ocorrencia.tipoCrime.categoria', 'ocorrencia.estado',
            'ocorrencia.agenteRegisto', 'ocorrencia.agenteResponsavel',
            'ocorrencia.unidade', 'ocorrencia.envolvimentos.pessoa',
            'ocorrencia.envolvimentos.tipoEnvolvimento',
            'ocorrencia.evidencias.tipoEvidencia',
            'ocorrencia.detencoes.pessoa', 'ocorrencia.detencoes.estado',
            'investigador', 'estado', 'notas.agente',
        ]));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'investigador_id' => 'required|exists:agentes,id',
            'prazo' => 'nullable|date|after_or_equal:today',
            'resumo' => 'nullable|string|max:5000',
        ]);

        $ocorrencia = Ocorrencia::findOrFail($dados['ocorrencia_id']);
        $this->exigirOcorrenciaPermitida($ocorrencia);

        $investigador = Agente::activos()->find($dados['investigador_id']);
        abort_unless($investigador && (int) $investigador->unidade_id === (int) $ocorrencia->unidade_id, 422, 'O investigador deve estar activo e pertencer a unidade da ocorrencia.');

        abort_if(
            Investigacao::where('ocorrencia_id', $dados['ocorrencia_id'])->whereNot('estado_id', 4)->exists(),
            422,
            'Ja existe uma investigacao activa para esta ocorrencia.'
        );

        $inv = Investigacao::create([
            'numero_investigacao' => Investigacao::gerarNumero(),
            'ocorrencia_id' => $dados['ocorrencia_id'],
            'investigador_id' => $dados['investigador_id'],
            'estado_id' => 1,
            'resumo' => $dados['resumo'] ?? null,
            'data_inicio' => now(),
            'prazo' => $dados['prazo'] ?? null,
            'progresso' => 0,
        ]);

        $inv->ocorrencia->update(['estado_id' => 4]);
        Log::registar('criar', 'investigacoes', $inv->id, 'Investigacao aberta');

        return response()->json(['success' => true, 'message' => 'Investigacao aberta.', 'investigacao' => $inv->load(['ocorrencia', 'investigador', 'estado'])], 201);
    }

    public function update(Request $request, Investigacao $investigacao)
    {
        $this->exigirInvestigacaoPermitida($investigacao);

        $dados = $request->validate([
            'estado_id' => 'sometimes|required|exists:estados_investigacao,id',
            'progresso' => 'sometimes|required|integer|min:0|max:100',
            'resumo' => 'nullable|string|max:5000',
        ]);

        if (($dados['estado_id'] ?? null) == 4) {
            $dados['progresso'] = 100;
            $dados['data_fim'] = now();
        }

        $investigacao->update($dados);
        Log::registar('editar', 'investigacoes', $investigacao->id, 'Investigacao actualizada');

        return response()->json(['success' => true, 'message' => 'Actualizada.']);
    }

    public function adicionarNota(Request $request, Investigacao $investigacao)
    {
        $this->exigirInvestigacaoPermitida($investigacao);

        $dados = $request->validate([
            'titulo' => 'nullable|string|max:200',
            'conteudo' => 'required|string|min:3|max:5000',
            'confidencial' => 'sometimes|boolean',
        ]);

        abort_unless($this->agenteAtualId(), 422, 'O utilizador autenticado deve estar associado a um agente.');

        $nota = NotaInvestigacao::create([
            'investigacao_id' => $investigacao->id,
            'agente_id' => $this->agenteAtualId(),
            'titulo' => $dados['titulo'] ?? null,
            'conteudo' => $dados['conteudo'],
            'confidencial' => $dados['confidencial'] ?? false,
        ]);

        return response()->json(['success' => true, 'nota' => $nota], 201);
    }

    public function notas(Investigacao $investigacao)
    {
        $this->exigirInvestigacaoPermitida($investigacao);

        return response()->json($investigacao->notas()->with('agente')->orderByDesc('created_at')->get());
    }
}
