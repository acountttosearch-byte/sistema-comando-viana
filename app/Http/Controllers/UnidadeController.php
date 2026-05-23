<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    public function index(Request $request)
    {
        $q = Unidade::with(['tipoUnidade', 'unidadePai']);
        if ($request->filled('tipo_unidade_id')) $q->where('tipo_unidade_id', $request->tipo_unidade_id);

        return response()->json($q->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:200',
            'tipo_unidade_id' => 'required|exists:tipos_unidade,id',
            'unidade_pai_id' => 'nullable|exists:unidades,id',
            'endereco' => 'nullable|string|max:300',
            'municipio' => 'nullable|string|max:100',
            'telefone' => ['nullable', 'string', 'max:20', 'regex:/^(?:\+?244)?\s?9\d{2}\s?\d{3}\s?\d{3}$/'],
            'email' => 'nullable|email|max:150',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $u = Unidade::create($dados + ['estado' => 'activo']);
        Log::registar('criar', 'unidades', $u->id, "Unidade {$u->nome} criada");

        return response()->json(['success' => true, 'unidade' => $u], 201);
    }

    public function update(Request $request, Unidade $unidade)
    {
        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:200',
            'tipo_unidade_id' => 'nullable|exists:tipos_unidade,id',
            'unidade_pai_id' => 'nullable|exists:unidades,id',
            'endereco' => 'nullable|string|max:300',
            'municipio' => 'nullable|string|max:100',
            'telefone' => ['nullable', 'string', 'max:20', 'regex:/^(?:\+?244)?\s?9\d{2}\s?\d{3}\s?\d{3}$/'],
            'email' => 'nullable|email|max:150',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        abort_if(isset($dados['unidade_pai_id']) && (int) $dados['unidade_pai_id'] === (int) $unidade->id, 422, 'Uma unidade nao pode ser propria unidade superior.');

        $unidade->update($dados);
        Log::registar('editar', 'unidades', $unidade->id, 'Unidade actualizada');

        return response()->json(['success' => true, 'unidade' => $unidade->fresh()]);
    }

    public function toggleEstado(Unidade $unidade)
    {
        abort_if($unidade->agentes()->where('estado', 'activo')->exists() && $unidade->estado === 'activo', 422, 'Nao e possivel inactivar uma unidade com agentes activos.');

        $novo = $unidade->estado === 'activo' ? 'inactivo' : 'activo';
        $unidade->update(['estado' => $novo]);

        return response()->json(['success' => true, 'message' => "Unidade {$novo}."]);
    }

    public function esquadras()
    {
        return response()->json(Unidade::esquadras()->activas()->get());
    }

    public function estatisticas(Unidade $unidade)
    {
        return response()->json([
            'agentes_activos' => $unidade->agentes()->activos()->count(),
            'ocorrencias_total' => $unidade->ocorrencias()->count(),
            'viaturas' => $unidade->viaturas()->operacionais()->count(),
        ]);
    }
}
