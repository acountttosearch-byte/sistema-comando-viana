<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\Despacho;
use App\Models\Log;
use App\Models\Ocorrencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DespachoController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $perfil = $this->perfilNome();
        $agenteId = $this->agenteAtualId();
        $q = Despacho::with(['ocorrencia.tipoCrime', 'agenteDestino', 'agenteOrigem', 'unidade']);

        if (in_array($perfil, ['admin', 'comandante'], true)) {
            // visao global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where(fn($q2) => $q2->where('unidade_destino', $this->unidadeAtualId())->orWhere('despachado_por', $agenteId));
        } elseif ($agenteId) {
            $q->where('despachado_para', $agenteId);
        }

        if ($request->filled('estado')) $q->where('estado', $request->estado);

        return response()->json($q->orderByDesc('data_despacho')->paginate(20));
    }

    public function store(Request $request)
    {
        if (!in_array($this->perfilNome(), ['admin', 'comandante', 'chefe_esquadra'], true)) {
            return response()->json(['error' => 'Sem permissao para criar despachos.'], 403);
        }

        $dados = $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'prioridade' => ['required', Rule::in(['baixa', 'media', 'alta', 'critica'])],
            'despachado_para' => 'required|exists:agentes,id',
            'unidade_destino' => 'required|exists:unidades,id',
            'instrucoes' => 'nullable|string|max:2000',
        ]);

        $ocorrencia = Ocorrencia::findOrFail($dados['ocorrencia_id']);
        $this->exigirOcorrenciaPermitida($ocorrencia);
        $this->exigirUnidadePermitida((int) $dados['unidade_destino']);

        $agenteDestino = Agente::activos()->find($dados['despachado_para']);
        abort_unless($agenteDestino && (int) $agenteDestino->unidade_id === (int) $dados['unidade_destino'], 422, 'O agente de destino deve estar activo e pertencer a unidade de destino.');

        abort_if($ocorrencia->despachos()->whereIn('estado', ['pendente', 'aceite', 'em_curso'])->exists(), 422, 'Esta ocorrencia ja possui um despacho activo.');

        $agenteOrigem = $this->agenteAtual();
        abort_unless($agenteOrigem, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $d = Despacho::create([
            'ocorrencia_id' => $dados['ocorrencia_id'],
            'prioridade' => $dados['prioridade'],
            'despachado_para' => $dados['despachado_para'],
            'despachado_por' => $agenteOrigem->id,
            'unidade_destino' => $dados['unidade_destino'],
            'instrucoes' => $dados['instrucoes'] ?? null,
            'estado' => 'pendente',
            'data_despacho' => now(),
        ]);

        $d->ocorrencia->update(['estado_id' => 3, 'agente_responsavel_id' => $dados['despachado_para']]);
        Log::registar('criar', 'despachos', $d->id, 'Despacho criado');

        return response()->json(['success' => true, 'despacho' => $d], 201);
    }

    public function responder(Request $request, Despacho $despacho)
    {
        abort_unless($this->temVisaoGlobal() || $despacho->despachado_para === $this->agenteAtualId(), 403, 'Apenas o destinatario pode responder ao despacho.');

        $dados = $request->validate(['estado' => ['required', Rule::in(['aceite', 'em_curso', 'concluido', 'rejeitado'])]]);

        $transicoes = [
            'pendente' => ['aceite', 'rejeitado'],
            'aceite' => ['em_curso', 'concluido'],
            'em_curso' => ['concluido'],
            'concluido' => ['concluido'],
            'rejeitado' => ['rejeitado'],
        ];
        abort_unless(in_array($dados['estado'], $transicoes[$despacho->estado] ?? [], true), 422, 'Transicao de estado invalida para este despacho.');

        $despacho->update([
            'estado' => $dados['estado'],
            'data_resposta' => now(),
            'tempo_resposta_minutos' => now()->diffInMinutes($despacho->data_despacho),
        ]);

        return response()->json(['success' => true, 'message' => 'Despacho respondido.']);
    }
}
