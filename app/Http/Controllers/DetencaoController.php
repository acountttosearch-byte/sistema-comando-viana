<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Detencao;
use App\Models\Log;
use App\Models\Ocorrencia;
use Illuminate\Http\Request;

class DetencaoController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;
        $q = Detencao::with(['pessoa', 'ocorrencia', 'agenteResponsavel', 'unidade', 'estado']);

        if (in_array($perfil, ['admin', 'comandante'], true)) {
            // visao global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where('unidade_id', $this->unidadeAtualId());
        } elseif ($agenteId) {
            $q->where('agente_responsavel_id', $agenteId);
        }

        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('data_inicio')) $q->where('data_detencao', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_detencao', '<=', $request->data_fim);
        if ($request->filled('unidade_id') && $this->temVisaoGlobal()) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2
                ->where('numero_detencao', 'like', "%$b%")
                ->orWhere('motivo', 'like', "%$b%")
                ->orWhere('local_detencao', 'like', "%$b%")
                ->orWhereHas('pessoa', fn($q3) => $q3->where('nome', 'like', "%$b%")->orWhere('bi', 'like', "%$b%"))
            );
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderByDesc('data_detencao')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'pessoa_id' => 'required|exists:pessoas,id',
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'data_detencao' => 'required|date|before_or_equal:now',
            'local_detencao' => 'required|string|min:3|max:300',
            'motivo' => 'required|string|min:10|max:2000',
            'observacoes' => 'nullable|string|max:2000',
        ]);

        $ocorrencia = Ocorrencia::findOrFail($dados['ocorrencia_id']);
        $this->exigirOcorrenciaPermitida($ocorrencia);

        abort_unless(
            $ocorrencia->envolvimentos()->where('pessoa_id', $dados['pessoa_id'])->exists(),
            422,
            'A pessoa deve estar associada a ocorrencia antes da detencao.'
        );

        abort_if(
            Detencao::where('pessoa_id', $dados['pessoa_id'])->whereNot('estado_id', 4)->exists(),
            422,
            'Esta pessoa ja possui uma detencao aberta.'
        );

        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $dt = Detencao::create([
            'numero_detencao' => Detencao::gerarNumero(),
            'pessoa_id' => $dados['pessoa_id'],
            'ocorrencia_id' => $dados['ocorrencia_id'],
            'data_detencao' => $dados['data_detencao'],
            'local_detencao' => $dados['local_detencao'],
            'agente_responsavel_id' => $ag->id,
            'unidade_id' => $ag->unidade_id,
            'motivo' => $dados['motivo'],
            'estado_id' => 1,
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        Log::registar('criar', 'detencoes', $dt->id, "Detencao {$dt->numero_detencao} registada");

        return response()->json(['success' => true, 'message' => 'Detencao registada.', 'detencao' => $dt->load(['pessoa', 'ocorrencia'])], 201);
    }

    public function show(Detencao $detencao)
    {
        $this->exigirDetencaoPermitida($detencao);

        return response()->json($detencao->load([
            'pessoa', 'ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade', 'estado',
        ]));
    }

    public function actualizarEstado(Request $request, Detencao $detencao)
    {
        $this->exigirDetencaoPermitida($detencao);

        $dados = $request->validate([
            'estado_id' => 'required|exists:estados_detencao,id',
            'observacoes' => 'nullable|string|max:2000',
        ]);

        abort_if((int) $detencao->estado_id === 4, 422, 'Uma detencao libertada nao pode voltar a mudar de estado.');

        $detencao->update([
            'estado_id' => $dados['estado_id'],
            'data_libertacao' => (int) $dados['estado_id'] === 4 ? now() : $detencao->data_libertacao,
            'observacoes' => $dados['observacoes'] ?? $detencao->observacoes,
        ]);

        Log::registar('editar', 'detencoes', $detencao->id, 'Estado da detencao actualizado');

        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    }
}
