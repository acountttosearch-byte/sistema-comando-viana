<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Pessoa;
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

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderBy('nome')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'min:3', 'max:200', 'regex:/^[\pL\s\'-]+$/u'],
            'alcunha' => 'nullable|string|max:100',
            'bi' => ['nullable', 'string', 'max:20', 'regex:/^\d{9,10}[A-Za-z]{2}\d{3}$/', 'unique:pessoas,bi'],
            'sexo' => 'nullable|in:M,F',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'nacionalidade' => 'nullable|string|max:100',
            'telefone' => ['nullable', 'string', 'max:20', 'regex:/^(?:\+?244)?\s?9\d{2}\s?\d{3}\s?\d{3}$/', 'unique:pessoas,telefone'],
            'morada' => 'nullable|string|max:300',
            'bairro' => 'nullable|string|max:150',
            'caracteristicas_fisicas' => 'nullable|string|max:2000',
            'observacoes' => 'nullable|string|max:2000',
        ], [
            'nome.regex' => 'O nome deve conter apenas letras, espacos, apostrofos ou hifens.',
            'bi.regex' => 'Informe um BI angolano valido. Ex: 001234567LA042.',
            'telefone.regex' => 'Informe um telefone angolano valido. Ex: +244 923 000 000.',
            'bi.unique' => 'Ja existe uma pessoa registada com este numero de BI.',
            'telefone.unique' => 'Ja existe uma pessoa registada com este numero de telefone.',
            'data_nascimento.before_or_equal' => 'A data de nascimento nao pode ser no futuro.',
        ]);

        $p = Pessoa::create($dados);
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
        $dados = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'min:3', 'max:200', 'regex:/^[\pL\s\'-]+$/u'],
            'alcunha' => 'nullable|string|max:100',
            'bi' => ['nullable', 'string', 'max:20', 'regex:/^\d{9,10}[A-Za-z]{2}\d{3}$/', 'unique:pessoas,bi,' . $pessoa->id],
            'sexo' => 'nullable|in:M,F',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'nacionalidade' => 'nullable|string|max:100',
            'telefone' => ['nullable', 'string', 'max:20', 'regex:/^(?:\+?244)?\s?9\d{2}\s?\d{3}\s?\d{3}$/', 'unique:pessoas,telefone,' . $pessoa->id],
            'morada' => 'nullable|string|max:300',
            'bairro' => 'nullable|string|max:150',
            'caracteristicas_fisicas' => 'nullable|string|max:2000',
            'observacoes' => 'nullable|string|max:2000',
        ], [
            'nome.regex' => 'O nome deve conter apenas letras, espacos, apostrofos ou hifens.',
            'bi.regex' => 'Informe um BI angolano valido. Ex: 001234567LA042.',
            'telefone.regex' => 'Informe um telefone angolano valido. Ex: +244 923 000 000.',
            'bi.unique' => 'Ja existe uma pessoa registada com este numero de BI.',
            'telefone.unique' => 'Ja existe uma pessoa registada com este numero de telefone.',
            'data_nascimento.before_or_equal' => 'A data de nascimento nao pode ser no futuro.',
        ]);

        $pessoa->update($dados);
        Log::registar('editar', 'pessoas', $pessoa->id, 'Pessoa actualizada');

        return response()->json(['success' => true, 'pessoa' => $pessoa->fresh()]);
    }
}
