<?php

namespace App\Http\Controllers;

use App\Models\Mensagem;
use Illuminate\Http\Request;

class MensagemController extends Controller
{
    public function inbox()
    {
        $ag = auth()->user()->agente;
        return response()->json(
            Mensagem::with('remetente')
                ->where(fn($q) => $q->where('destinatario_id', $ag->id)->orWhere('unidade_destino_id', $ag->unidade_id))
                ->orderByDesc('created_at')->paginate(20)
        );
    }

    public function enviadas()
    {
        return response()->json(
            Mensagem::with('destinatario')
                ->where('remetente_id', auth()->user()->agente->id)
                ->orderByDesc('created_at')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:200',
            'mensagem' => 'required|string',
            'destinatario_id' => 'required_without:unidade_destino_id|nullable|exists:agentes,id',
            'unidade_destino_id' => 'required_without:destinatario_id|nullable|exists:unidades,id',
        ]);

        $msg = Mensagem::create(array_merge($request->only(['titulo', 'mensagem', 'destinatario_id', 'unidade_destino_id']), [
            'remetente_id' => auth()->user()->agente->id,
            'prioridade' => $request->prioridade ?? 'normal',
        ]));
        return response()->json(['success' => true, 'mensagem' => $msg], 201);
    }

    public function marcarLida(Mensagem $mensagem)
    {
        $mensagem->update(['lida' => true, 'data_leitura' => now()]);
        return response()->json(['success' => true]);
    }

    public function naoLidas()
    {
        $ag = auth()->user()->agente;
        $count = Mensagem::where(fn($q) => $q->where('destinatario_id', $ag->id)->orWhere('unidade_destino_id', $ag->unidade_id))
            ->naoLidas()->count();
        return response()->json(['total' => $count]);
    }
}