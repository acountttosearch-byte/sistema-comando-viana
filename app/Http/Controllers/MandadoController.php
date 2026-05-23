<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Log;
use App\Models\Mandado;
use App\Models\Ocorrencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MandadoController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $q = Mandado::with(['tipoMandado', 'ocorrencia', 'pessoa', 'agenteResponsavel']);
        if ($request->filled('estado')) $q->where('estado', $request->estado);

        if (!$this->temVisaoGlobal() && $this->eChefeEsquadra()) {
            $q->whereHas('ocorrencia', fn($q2) => $q2->where('unidade_id', $this->unidadeAtualId()));
        } elseif (!$this->temVisaoGlobal() && !$this->eChefeEsquadra()) {
            $q->where('agente_responsavel_id', $this->agenteAtualId());
        }

        return response()->json($q->orderByDesc('data_emissao')->paginate(20));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'tipo_mandado_id' => 'required|exists:tipos_mandado,id',
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'pessoa_id' => 'nullable|exists:pessoas,id',
            'tribunal' => 'nullable|string|max:200',
            'juiz' => 'nullable|string|max:200',
            'data_emissao' => 'required|date|before_or_equal:today',
            'data_validade' => 'nullable|date|after_or_equal:data_emissao',
            'descricao' => 'required|string|min:10|max:2000',
        ]);

        $ocorrencia = Ocorrencia::findOrFail($dados['ocorrencia_id']);
        $this->exigirOcorrenciaPermitida($ocorrencia);

        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $m = Mandado::create([
            'numero_mandado' => 'MD-' . date('Y') . '-' . str_pad(Mandado::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT),
            'tipo_mandado_id' => $dados['tipo_mandado_id'],
            'ocorrencia_id' => $dados['ocorrencia_id'],
            'pessoa_id' => $dados['pessoa_id'] ?? null,
            'tribunal' => $dados['tribunal'] ?? null,
            'juiz' => $dados['juiz'] ?? null,
            'data_emissao' => $dados['data_emissao'],
            'data_validade' => $dados['data_validade'] ?? null,
            'descricao' => $dados['descricao'],
            'estado' => 'pendente',
            'agente_responsavel_id' => $ag->id,
        ]);

        Log::registar('criar', 'mandados', $m->id, 'Mandado emitido');

        return response()->json(['success' => true, 'mandado' => $m], 201);
    }

    public function actualizarEstado(Request $request, Mandado $mandado)
    {
        $mandado->loadMissing('ocorrencia');
        $this->exigirOcorrenciaPermitida($mandado->ocorrencia);

        $dados = $request->validate(['estado' => ['required', Rule::in(['pendente', 'executado', 'expirado', 'cancelado'])]]);

        abort_if($mandado->estado !== 'pendente' && $dados['estado'] !== $mandado->estado, 422, 'Mandados finalizados nao podem mudar de estado.');

        $mandado->update(['estado' => $dados['estado']]);

        return response()->json(['success' => true]);
    }
}
