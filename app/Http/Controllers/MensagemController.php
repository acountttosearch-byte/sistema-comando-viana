<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\Mensagem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MensagemController extends Controller
{
    use AuthorizesOperationalAccess;

    public function inbox()
    {
        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        return response()->json(
            Mensagem::with('remetente')
                ->where(fn($q) => $q->where('destinatario_id', $ag->id)->orWhere('unidade_destino_id', $ag->unidade_id))
                ->orderByDesc('created_at')->paginate(20)
        );
    }

    public function enviadas()
    {
        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        return response()->json(
            Mensagem::with('destinatario')
                ->where('remetente_id', $ag->id)
                ->orderByDesc('created_at')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'titulo' => 'required|string|min:3|max:200',
            'mensagem' => 'required|string|min:3|max:5000',
            'destinatario_id' => 'required_without:unidade_destino_id|nullable|exists:agentes,id',
            'unidade_destino_id' => 'required_without:destinatario_id|nullable|exists:unidades,id',
            'prioridade' => ['nullable', Rule::in(['normal', 'urgente'])],
        ]);

        abort_if(!empty($dados['destinatario_id']) && !empty($dados['unidade_destino_id']), 422, 'Escolha um destinatario ou uma unidade, nao ambos.');

        if (!empty($dados['destinatario_id'])) {
            abort_unless(Agente::activos()->whereKey($dados['destinatario_id'])->exists(), 422, 'O destinatario deve ser um agente activo.');
        }

        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $msg = Mensagem::create([
            'titulo' => $dados['titulo'],
            'mensagem' => $dados['mensagem'],
            'destinatario_id' => $dados['destinatario_id'] ?? null,
            'unidade_destino_id' => $dados['unidade_destino_id'] ?? null,
            'remetente_id' => $ag->id,
            'prioridade' => $dados['prioridade'] ?? 'normal',
        ]);

        return response()->json(['success' => true, 'mensagem' => $msg], 201);
    }

    public function marcarLida(Mensagem $mensagem)
    {
        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');
        abort_unless($mensagem->destinatario_id === $ag->id || $mensagem->unidade_destino_id === $ag->unidade_id, 403, 'Sem permissao para esta mensagem.');

        $mensagem->update(['lida' => true, 'data_leitura' => now()]);

        return response()->json(['success' => true]);
    }

    public function naoLidas()
    {
        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $count = Mensagem::where(fn($q) => $q->where('destinatario_id', $ag->id)->orWhere('unidade_destino_id', $ag->unidade_id))
            ->naoLidas()->count();

        return response()->json(['total' => $count]);
    }
}
