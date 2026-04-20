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
        $request->validate([
            'nome' => 'required|string|max:200',
            'bi' => 'nullable|string|max:20|unique:pessoas,bi',
            'telefone' => 'nullable|string|max:20|unique:pessoas,telefone',
            'sexo' => 'nullable|in:M,F',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'nacionalidade' => 'nullable|string|max:100',
            'morada' => 'nullable|string|max:300',
            'bairro' => 'nullable|string|max:100',
            'alcunha' => 'nullable|string|max:100',
        ], [
            'bi.unique' => 'Já existe uma pessoa registada com este número de BI.',
            'telefone.unique' => 'Já existe uma pessoa registada com este número de telefone.',
            'data_nascimento.before_or_equal' => 'A data de nascimento não pode ser no futuro.',
        ]);
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
        $request->validate([
            'nome' => 'sometimes|required|string|max:200',
            'bi' => 'nullable|string|max:20|unique:pessoas,bi,' . $pessoa->id,
            'telefone' => 'nullable|string|max:20|unique:pessoas,telefone,' . $pessoa->id,
            'data_nascimento' => 'nullable|date|before_or_equal:today',
        ], [
            'bi.unique' => 'Já existe uma pessoa registada com este número de BI.',
            'telefone.unique' => 'Já existe uma pessoa registada com este número de telefone.',
            'data_nascimento.before_or_equal' => 'A data de nascimento não pode ser no futuro.',
        ]);
        $pessoa->update($request->all());
        Log::registar('editar', 'pessoas', $pessoa->id, "Pessoa actualizada");
        return response()->json(['success' => true, 'pessoa' => $pessoa->fresh()]);
    }
}