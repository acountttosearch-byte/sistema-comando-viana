<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Models\Log;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        return response()->json(Configuracao::orderBy('grupo')->orderBy('chave')->get()->groupBy('grupo'));
    }

    public function update(Request $request)
    {
        $request->validate(['configuracoes' => 'required|array', 'configuracoes.*.chave' => 'required|string', 'configuracoes.*.valor' => 'required']);
        foreach ($request->configuracoes as $c) {
            Configuracao::definir($c['chave'], $c['valor']);
        }
        Log::registar('editar', 'configuracoes', null, 'Configurações actualizadas');
        return response()->json(['success' => true, 'message' => 'Configurações guardadas.']);
    }
}