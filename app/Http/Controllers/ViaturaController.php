<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\Log;
use App\Models\Viatura;
use App\Models\ViaturaAtribuicao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViaturaController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $perfil = auth()->user()->perfil->nome;
        $q = Viatura::with('unidade');

        if ($perfil === 'chefe_esquadra') $q->where('unidade_id', $this->unidadeAtualId());
        if ($request->filled('unidade_id') && $this->temVisaoGlobal()) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('matricula', 'like', "%$b%")->orWhere('marca', 'like', "%$b%")->orWhere('modelo', 'like', "%$b%"));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderBy('matricula')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'matricula' => ['required', 'string', 'max:20', 'regex:/^[A-Z]{2}-\d{2}-\d{2}-[A-Z]{2}$/i', 'unique:viaturas,matricula'],
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'ano' => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'cor' => 'nullable|string|max:50',
            'unidade_id' => 'required|exists:unidades,id',
            'quilometragem' => 'nullable|integer|min:0',
        ], [
            'matricula.regex' => 'A matricula deve seguir o formato LD-00-00-AA.',
        ]);

        $this->exigirUnidadePermitida((int) $dados['unidade_id']);

        $v = Viatura::create([
            'matricula' => strtoupper($dados['matricula']),
            'marca' => $dados['marca'],
            'modelo' => $dados['modelo'],
            'ano' => $dados['ano'] ?? null,
            'cor' => $dados['cor'] ?? null,
            'unidade_id' => $dados['unidade_id'],
            'quilometragem' => $dados['quilometragem'] ?? 0,
            'estado' => 'operacional',
        ]);

        Log::registar('criar', 'viaturas', $v->id, "Viatura {$v->matricula} registada");

        return response()->json(['success' => true, 'viatura' => $v], 201);
    }

    public function show(Viatura $viatura)
    {
        $this->exigirUnidadePermitida($viatura->unidade_id);

        return response()->json($viatura->load(['unidade', 'atribuicoes.agente', 'manutencoes']));
    }

    public function atribuir(Request $request, Viatura $viatura)
    {
        $this->exigirUnidadePermitida($viatura->unidade_id);

        $dados = $request->validate([
            'agente_id' => 'required|exists:agentes,id',
            'quilometragem_saida' => 'required|integer|min:0',
        ]);

        abort_unless($viatura->estado === 'operacional', 422, 'Apenas viaturas operacionais podem ser atribuidas.');
        abort_if($viatura->atribuicoes()->whereNull('data_retorno')->exists(), 422, 'Esta viatura ja possui uma atribuicao aberta.');
        abort_if((int) $dados['quilometragem_saida'] < (int) $viatura->quilometragem, 422, 'A quilometragem de saida nao pode ser inferior a quilometragem actual.');

        $agente = Agente::activos()->find($dados['agente_id']);
        abort_unless($agente && (int) $agente->unidade_id === (int) $viatura->unidade_id, 422, 'O agente deve estar activo e pertencer a unidade da viatura.');

        $at = ViaturaAtribuicao::create([
            'viatura_id' => $viatura->id,
            'agente_id' => $dados['agente_id'],
            'data_saida' => now(),
            'quilometragem_saida' => $dados['quilometragem_saida'],
        ]);

        return response()->json(['success' => true, 'atribuicao' => $at], 201);
    }

    public function devolver(Request $request, ViaturaAtribuicao $atribuicao)
    {
        $atribuicao->loadMissing('viatura');
        $this->exigirUnidadePermitida($atribuicao->viatura->unidade_id);

        abort_if($atribuicao->data_retorno, 422, 'Esta viatura ja foi devolvida.');

        $dados = $request->validate([
            'quilometragem_retorno' => 'nullable|integer|min:' . (int) $atribuicao->quilometragem_saida,
            'observacoes' => 'nullable|string|max:1000',
        ]);

        $atribuicao->update([
            'data_retorno' => now(),
            'quilometragem_retorno' => $dados['quilometragem_retorno'] ?? null,
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        if (!empty($dados['quilometragem_retorno'])) {
            $atribuicao->viatura->update(['quilometragem' => $dados['quilometragem_retorno']]);
        }

        return response()->json(['success' => true, 'message' => 'Viatura devolvida.']);
    }
}
