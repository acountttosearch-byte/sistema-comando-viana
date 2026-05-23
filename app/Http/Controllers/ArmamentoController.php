<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\Armamento;
use App\Models\ArmamentoAtribuicao;
use App\Models\Log;
use Illuminate\Http\Request;

class ArmamentoController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $perfil = auth()->user()->perfil->nome;
        $q = Armamento::with(['tipoArmamento', 'unidade', 'atribuicaoActual.agente']);

        if ($perfil === 'chefe_esquadra') $q->where('unidade_id', $this->unidadeAtualId());
        if ($request->filled('unidade_id') && $this->temVisaoGlobal()) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('tipo_armamento_id')) $q->where('tipo_armamento_id', $request->tipo_armamento_id);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('numero_serie', 'like', "%$b%")->orWhere('marca', 'like', "%$b%")->orWhere('modelo', 'like', "%$b%")->orWhere('calibre', 'like', "%$b%"));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderBy('numero_serie')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'tipo_armamento_id' => 'required|exists:tipos_armamento,id',
            'numero_serie' => 'required|string|max:100|unique:armamento,numero_serie',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'calibre' => 'nullable|string|max:50',
            'unidade_id' => 'required|exists:unidades,id',
        ]);

        $this->exigirUnidadePermitida((int) $dados['unidade_id']);

        $a = Armamento::create([
            'tipo_armamento_id' => $dados['tipo_armamento_id'],
            'numero_serie' => strtoupper(trim($dados['numero_serie'])),
            'marca' => $dados['marca'] ?? null,
            'modelo' => $dados['modelo'] ?? null,
            'calibre' => $dados['calibre'] ?? null,
            'unidade_id' => $dados['unidade_id'],
            'estado' => 'operacional',
        ]);

        Log::registar('criar', 'armamento', $a->id, 'Armamento registado');

        return response()->json(['success' => true, 'armamento' => $a], 201);
    }

    public function show(Armamento $armamento)
    {
        $this->exigirUnidadePermitida($armamento->unidade_id);

        return response()->json($armamento->load([
            'tipoArmamento', 'unidade', 'atribuicaoActual.agente',
            'atribuicoes.agente',
        ]));
    }

    public function atribuir(Request $request, Armamento $armamento)
    {
        $this->exigirUnidadePermitida($armamento->unidade_id);

        $dados = $request->validate(['agente_id' => 'required|exists:agentes,id']);

        abort_unless($armamento->estado === 'operacional', 422, 'Apenas armamento operacional pode ser atribuido.');
        abort_if($armamento->atribuicoes()->where('estado', 'atribuido')->exists(), 422, 'Este armamento ja esta atribuido.');

        $agente = Agente::activos()->find($dados['agente_id']);
        abort_unless($agente && (int) $agente->unidade_id === (int) $armamento->unidade_id, 422, 'O agente deve estar activo e pertencer a unidade do armamento.');

        ArmamentoAtribuicao::create([
            'armamento_id' => $armamento->id,
            'agente_id' => $dados['agente_id'],
            'data_atribuicao' => now(),
            'estado' => 'atribuido',
        ]);

        Log::registar('criar', 'armamento_atribuicoes', $armamento->id, 'Arma atribuida');

        return response()->json(['success' => true, 'message' => 'Armamento atribuido.']);
    }

    public function devolver(Armamento $armamento)
    {
        $this->exigirUnidadePermitida($armamento->unidade_id);

        $at = $armamento->atribuicaoActual;
        abort_unless($at, 422, 'Este armamento nao possui atribuicao aberta.');

        $at->update(['estado' => 'devolvido', 'data_devolucao' => now()]);

        return response()->json(['success' => true, 'message' => 'Armamento devolvido.']);
    }
}
