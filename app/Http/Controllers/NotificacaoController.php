<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;

class NotificacaoController extends Controller
{
    public function index()
    {
        return response()->json(Notificacao::where('user_id', auth()->id())->orderByDesc('created_at')->limit(50)->get());
    }

    public function naoLidas()
    {
        return response()->json(['total' => Notificacao::where('user_id', auth()->id())->naoLidas()->count()]);
    }

    public function marcarLida(Notificacao $notificacao)
    {
        $notificacao->update(['lida' => true, 'data_leitura' => now()]);
        return response()->json(['success' => true]);
    }

    public function marcarTodasLidas()
    {
        Notificacao::where('user_id', auth()->id())->naoLidas()->update(['lida' => true, 'data_leitura' => now()]);
        return response()->json(['success' => true]);
    }
}