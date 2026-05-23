<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Alerta;
use App\Models\AlertaDestinatario;
use App\Models\Log;
use App\Models\Ocorrencia;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertaController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $q = Alerta::with(['tipoAlerta', 'pessoa', 'criadoPor'])->orderByDesc('created_at');
        if ($request->filled('estado')) $q->where('estado', $request->estado);

        return response()->json($q->paginate(20));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'tipo_alerta_id' => 'required|exists:tipos_alerta,id',
            'titulo' => 'required|string|min:3|max:200',
            'descricao' => 'required|string|min:10|max:5000',
            'prioridade' => ['required', Rule::in(['urgente', 'alta', 'normal'])],
            'pessoa_id' => 'nullable|exists:pessoas,id',
            'ocorrencia_id' => 'nullable|exists:ocorrencias,id',
            'data_expiracao' => 'nullable|date|after:now',
        ]);

        if (!empty($dados['ocorrencia_id'])) {
            $this->exigirOcorrenciaPermitida(Ocorrencia::findOrFail($dados['ocorrencia_id']));
        }

        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $alerta = Alerta::create([
            'tipo_alerta_id' => $dados['tipo_alerta_id'],
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'],
            'prioridade' => $dados['prioridade'],
            'pessoa_id' => $dados['pessoa_id'] ?? null,
            'ocorrencia_id' => $dados['ocorrencia_id'] ?? null,
            'estado' => 'activo',
            'criado_por' => $ag->id,
            'data_expiracao' => $dados['data_expiracao'] ?? null,
        ]);

        foreach (Unidade::activas()->pluck('id') as $uid) {
            AlertaDestinatario::create(['alerta_id' => $alerta->id, 'unidade_id' => $uid]);
        }

        Log::registar('criar', 'alertas', $alerta->id, 'Alerta emitido');

        return response()->json(['success' => true, 'alerta' => $alerta], 201);
    }

    public function confirmarVisualizacao(Alerta $alerta)
    {
        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        AlertaDestinatario::where('alerta_id', $alerta->id)->where('unidade_id', $ag->unidade_id)
            ->update(['visualizado' => true, 'data_visualizacao' => now(), 'visualizado_por' => $ag->id]);

        return response()->json(['success' => true]);
    }

    public function resolver(Alerta $alerta)
    {
        abort_unless($this->temVisaoGlobal() || $this->eChefeEsquadra() || $alerta->criado_por === $this->agenteAtualId(), 403, 'Sem permissao para resolver este alerta.');
        abort_unless($alerta->estado === 'activo', 422, 'Apenas alertas activos podem ser resolvidos.');

        $alerta->update(['estado' => 'resolvido']);
        Log::registar('editar', 'alertas', $alerta->id, 'Alerta resolvido');

        return response()->json(['success' => true, 'message' => 'Alerta resolvido.']);
    }
}
