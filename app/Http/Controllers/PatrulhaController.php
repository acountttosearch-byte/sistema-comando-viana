<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\Log;
use App\Models\Ocorrencia;
use App\Models\Patrulha;
use App\Models\PatrulhaIncidente;
use App\Models\Viatura;
use App\Models\ZonaPatrulha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatrulhaController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $perfil = auth()->user()->perfil->nome;
        $q = Patrulha::with(['turno', 'zona', 'viatura', 'agenteLider', 'unidade', 'agentes']);

        if (in_array($perfil, ['admin', 'comandante'], true)) {
            // visao global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->where('unidade_id', $this->unidadeAtualId());
        }

        if ($request->filled('data')) $q->where('data', $request->data);
        if ($request->filled('unidade_id') && $this->temVisaoGlobal()) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('estado')) $q->where('estado', $request->estado);

        return response()->json($q->orderByDesc('data')->paginate(20));
    }

    public function store(Request $request)
    {
        if (!in_array($this->perfilNome(), ['admin', 'comandante', 'chefe_esquadra'], true)) {
            return response()->json(['error' => 'Sem permissao para registar patrulhas.'], 403);
        }

        $dados = $request->validate([
            'data' => 'required|date|after_or_equal:today',
            'turno_id' => 'required|exists:turnos,id',
            'zona_id' => 'required|exists:zonas_patrulha,id',
            'agente_lider_id' => 'required|exists:agentes,id',
            'unidade_id' => 'required|exists:unidades,id',
            'viatura_id' => 'nullable|exists:viaturas,id',
            'agentes' => 'required|array|min:1',
            'agentes.*' => 'required|integer|distinct|exists:agentes,id',
        ]);

        $this->exigirUnidadePermitida((int) $dados['unidade_id']);

        $zona = ZonaPatrulha::find($dados['zona_id']);
        abort_unless($zona && (int) $zona->unidade_id === (int) $dados['unidade_id'], 422, 'A zona deve pertencer a unidade da patrulha.');
        abort_unless(in_array((int) $dados['agente_lider_id'], array_map('intval', $dados['agentes']), true), 422, 'O lider deve estar incluido na equipa da patrulha.');

        $agentesValidos = Agente::activos()->whereIn('id', $dados['agentes'])->where('unidade_id', $dados['unidade_id'])->pluck('id')->map(fn($id) => (int) $id)->all();
        abort_unless(count($agentesValidos) === count($dados['agentes']), 422, 'Todos os agentes da patrulha devem estar activos e pertencer a unidade selecionada.');

        if (!empty($dados['viatura_id'])) {
            $viatura = Viatura::where('id', $dados['viatura_id'])->where('unidade_id', $dados['unidade_id'])->where('estado', 'operacional')->first();
            abort_unless($viatura, 422, 'A viatura deve estar operacional e pertencer a unidade da patrulha.');
        }

        $p = Patrulha::create([
            'data' => $dados['data'],
            'turno_id' => $dados['turno_id'],
            'zona_id' => $dados['zona_id'],
            'agente_lider_id' => $dados['agente_lider_id'],
            'unidade_id' => $dados['unidade_id'],
            'viatura_id' => $dados['viatura_id'] ?? null,
            'estado' => 'planeada',
        ]);

        $agentes = collect($dados['agentes'])->mapWithKeys(fn($id) => [(int) $id => ['funcao' => (int) $id === (int) $dados['agente_lider_id'] ? 'lider' : 'apoio']]);
        $p->agentes()->attach($agentes);

        Log::registar('criar', 'patrulhas', $p->id, 'Patrulha planeada');

        return response()->json(['success' => true, 'patrulha' => $p->load('agentes')], 201);
    }

    public function actualizarEstado(Request $request, Patrulha $patrulha)
    {
        $this->exigirUnidadePermitida($patrulha->unidade_id);

        $dados = $request->validate(['estado' => ['required', Rule::in(['planeada', 'em_curso', 'concluida', 'cancelada'])]]);

        $transicoes = [
            'planeada' => ['planeada', 'em_curso', 'cancelada'],
            'em_curso' => ['em_curso', 'concluida', 'cancelada'],
            'concluida' => ['concluida'],
            'cancelada' => ['cancelada'],
        ];

        abort_unless(in_array($dados['estado'], $transicoes[$patrulha->estado] ?? [], true), 422, 'Transicao de estado invalida para esta patrulha.');

        $update = ['estado' => $dados['estado']];
        if ($dados['estado'] === 'em_curso' && !$patrulha->hora_inicio) $update['hora_inicio'] = now()->format('H:i');
        if ($dados['estado'] === 'concluida' && !$patrulha->hora_fim) $update['hora_fim'] = now()->format('H:i');
        $patrulha->update($update);

        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    }

    public function registarIncidente(Request $request, Patrulha $patrulha)
    {
        $this->exigirUnidadePermitida($patrulha->unidade_id);
        abort_unless($patrulha->estado === 'em_curso', 422, 'Incidentes so podem ser registados em patrulhas em curso.');

        $dados = $request->validate([
            'ocorrencia_id' => 'nullable|exists:ocorrencias,id',
            'local' => 'nullable|string|max:300',
            'descricao' => 'required|string|min:5|max:2000',
        ]);

        if (!empty($dados['ocorrencia_id'])) {
            $ocorrencia = Ocorrencia::findOrFail($dados['ocorrencia_id']);
            $this->exigirOcorrenciaPermitida($ocorrencia);
        }

        $inc = PatrulhaIncidente::create([
            'patrulha_id' => $patrulha->id,
            'ocorrencia_id' => $dados['ocorrencia_id'] ?? null,
            'hora_registo' => now()->format('H:i'),
            'local' => $dados['local'] ?? null,
            'descricao' => $dados['descricao'],
        ]);

        return response()->json(['success' => true, 'incidente' => $inc], 201);
    }
}
