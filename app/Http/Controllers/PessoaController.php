<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Log;
use Illuminate\Http\Request;

class PessoaController extends Controller
{
    public function index(Request $request)
    {
        $q = Pessoa::query();
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('nome', 'like', "%$b%")->orWhere('bi', 'like', "%$b%")->orWhere('alcunha', 'like', "%$b%"));
        }
        if ($request->filled('sexo')) $q->where('sexo', $request->sexo);
        if ($request->filled('nacionalidade')) $q->where('nacionalidade', $request->nacionalidade);
        if ($request->filled('bairro')) $q->where('bairro', 'like', "%{$request->bairro}%");
        return response()->json($q->orderBy('nome')->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:200']);
        $p = Pessoa::create($request->all());
        Log::registar('criar', 'pessoas', $p->id, "Pessoa {$p->nome} registada");
        return response()->json(['success' => true, 'pessoa' => $p], 201);
    }

    public function show(Pessoa $pessoa)
    {
        return response()->json($pessoa->load([
            'envolvimentos.ocorrencia.tipoCrime', 'envolvimentos.tipoEnvolvimento',
            'detencoes.estado', 'mandados', 'alertas',
        ]));
    }

    public function update(Request $request, Pessoa $pessoa)
    {
        $pessoa->update($request->all());
        Log::registar('editar', 'pessoas', $pessoa->id, "Pessoa actualizada");
        return response()->json(['success' => true, 'pessoa' => $pessoa->fresh()]);
    }
}