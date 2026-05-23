<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Log;
use App\Models\Ocorrencia;
use App\Models\ProcessoCriminal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProcessoCriminalController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;
        $q = ProcessoCriminal::with(['ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade']);

        if (in_array($perfil, ['admin', 'comandante'], true)) {
            // visao global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where('unidade_id', $this->unidadeAtualId());
        } elseif ($agenteId) {
            $q->where('agente_responsavel_id', $agenteId);
        }

        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('unidade_id') && $this->temVisaoGlobal()) $q->where('unidade_id', $request->unidade_id);
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

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderByDesc('data_abertura')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'resumo' => 'nullable|string|max:5000',
            'confidencial' => 'sometimes|boolean',
        ]);

        $ocorrencia = Ocorrencia::findOrFail($dados['ocorrencia_id']);
        $this->exigirOcorrenciaPermitida($ocorrencia);

        abort_if(
            ProcessoCriminal::where('ocorrencia_id', $dados['ocorrencia_id'])->exists(),
            422,
            'Ja existe um processo criminal para esta ocorrencia.'
        );

        $agente = $this->agenteAtual();
        abort_unless($agente, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $proc = ProcessoCriminal::create([
            'numero_processo' => ProcessoCriminal::gerarNumero(),
            'ocorrencia_id' => $dados['ocorrencia_id'],
            'agente_responsavel_id' => $agente->id,
            'unidade_id' => $ocorrencia->unidade_id,
            'estado' => 'em_instrucao',
            'data_abertura' => now(),
            'resumo' => $dados['resumo'] ?? null,
            'confidencial' => $dados['confidencial'] ?? false,
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
        $this->exigirProcessoPermitido($processo);

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
        $this->exigirProcessoPermitido($processo);

        $dados = $request->validate([
            'estado' => ['required', Rule::in(['em_instrucao', 'concluido', 'remetido_mp', 'arquivado'])],
            'resumo' => 'nullable|string|max:5000',
            'parecer_final' => 'nullable|string|max:5000',
            'destino_remessa' => 'nullable|string|max:200',
            'confidencial' => 'sometimes|boolean',
        ]);

        $transicoes = [
            'em_instrucao' => ['em_instrucao', 'concluido', 'arquivado'],
            'concluido' => ['concluido', 'remetido_mp', 'arquivado'],
            'remetido_mp' => ['remetido_mp', 'arquivado'],
            'arquivado' => ['arquivado'],
        ];

        abort_unless(in_array($dados['estado'], $transicoes[$processo->estado] ?? [], true), 422, 'Transicao de estado invalida para este processo.');

        if (in_array($dados['estado'], ['concluido', 'arquivado'], true)) {
            $parecer = trim($dados['parecer_final'] ?? $processo->parecer_final ?? '');
            abort_unless(strlen($parecer) >= 10, 422, 'Informe um parecer final antes de concluir ou arquivar o processo.');
        }

        if ($dados['estado'] === 'remetido_mp') {
            abort_unless(!empty($dados['destino_remessa']), 422, 'Informe o destino da remessa ao Ministerio Publico.');
        }

        if (!$this->temVisaoGlobal() && array_key_exists('confidencial', $dados)) {
            unset($dados['confidencial']);
        }

        if ($dados['estado'] === 'concluido' && !$processo->data_conclusao) {
            $dados['data_conclusao'] = now();
        }

        if ($dados['estado'] === 'remetido_mp' && !$processo->data_remessa) {
            $dados['data_remessa'] = now();
            $dados['data_conclusao'] = $dados['data_conclusao'] ?? $processo->data_conclusao ?? now();
        }

        $processo->update($dados);
        Log::registar('editar', 'processos_criminais', $processo->id, "Processo actualizado - Estado: {$processo->estado}");

        return response()->json(['success' => true, 'message' => 'Processo actualizado.', 'processo' => $processo->fresh()]);
    }
}
