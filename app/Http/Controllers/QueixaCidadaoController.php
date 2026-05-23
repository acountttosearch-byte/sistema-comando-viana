<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Log;
use App\Models\Ocorrencia;
use App\Models\QueixaCidadao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QueixaCidadaoController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $q = QueixaCidadao::with('analisadoPor');
        if ($request->filled('estado')) $q->where('estado', $request->estado);

        return response()->json($q->orderByDesc('created_at')->paginate(20));
    }

    public function submeter(Request $request)
    {
        $dados = $request->validate([
            'nome_cidadao' => ['required', 'string', 'min:3', 'max:200', 'regex:/^[\pL\s\'-]+$/u'],
            'bi' => ['nullable', 'string', 'max:30', 'regex:/^\d{9,10}[A-Za-z]{2}\d{3}$/'],
            'telefone' => ['required', 'string', 'max:20', 'regex:/^(?:\+?244)?\s?9\d{2}\s?\d{3}\s?\d{3}$/'],
            'email' => 'nullable|email|max:150',
            'tipo_queixa' => 'required|string|max:100',
            'descricao' => 'required|string|min:20|max:5000',
            'local' => 'nullable|string|max:300',
        ], [
            'nome_cidadao.regex' => 'O nome deve conter apenas letras, espacos, apostrofos ou hifens.',
            'bi.regex' => 'Informe um BI angolano valido. Ex: 001234567LA042.',
            'telefone.regex' => 'Informe um telefone angolano valido. Ex: +244 923 000 000.',
        ]);

        $qx = QueixaCidadao::create([
            'protocolo' => QueixaCidadao::gerarProtocolo(),
            'nome_cidadao' => $dados['nome_cidadao'],
            'bi' => $dados['bi'] ?? null,
            'telefone' => $dados['telefone'],
            'email' => $dados['email'] ?? null,
            'tipo_queixa' => $dados['tipo_queixa'],
            'descricao' => $dados['descricao'],
            'local' => $dados['local'] ?? null,
            'estado' => 'recebida',
        ]);

        return response()->json(['success' => true, 'message' => 'Queixa submetida.', 'protocolo' => $qx->protocolo], 201);
    }

    public function consultar(string $protocolo)
    {
        $qx = QueixaCidadao::where('protocolo', $protocolo)->first();
        if (!$qx) return response()->json(['error' => 'Protocolo nao encontrado.'], 404);

        return response()->json(['protocolo' => $qx->protocolo, 'estado' => $qx->estado, 'data_submissao' => $qx->created_at->format('d/m/Y H:i')]);
    }

    public function converter(Request $request, QueixaCidadao $queixa)
    {
        $dados = $request->validate([
            'tipo_crime_id' => 'required|exists:tipos_crime,id',
            'prioridade' => ['required', Rule::in(['baixa', 'media', 'alta', 'critica'])],
            'unidade_id' => 'required|exists:unidades,id',
        ]);

        abort_unless($queixa->estado === 'recebida' || $queixa->estado === 'em_analise', 422, 'Apenas queixas recebidas ou em analise podem ser convertidas.');
        $this->exigirUnidadePermitida((int) $dados['unidade_id']);

        return DB::transaction(function () use ($dados, $queixa) {
            $ag = $this->agenteAtual();
            abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

            $oc = Ocorrencia::create([
                'numero_ocorrencia' => Ocorrencia::gerarNumero(),
                'tipo_crime_id' => $dados['tipo_crime_id'],
                'descricao' => $queixa->descricao,
                'data_ocorrencia' => $queixa->created_at->toDateString(),
                'local' => $queixa->local ?? 'A definir',
                'prioridade' => $dados['prioridade'],
                'estado_id' => 1,
                'agente_registo_id' => $ag->id,
                'unidade_id' => $dados['unidade_id'],
            ]);

            $queixa->update(['estado' => 'convertida', 'ocorrencia_id' => $oc->id, 'analisado_por' => $ag->id]);
            Log::registar('criar', 'ocorrencias', $oc->id, "Ocorrencia criada da queixa {$queixa->protocolo}");

            return response()->json(['success' => true, 'message' => 'Queixa convertida.', 'ocorrencia' => $oc]);
        });
    }

    public function rejeitar(Request $request, QueixaCidadao $queixa)
    {
        abort_unless($queixa->estado === 'recebida' || $queixa->estado === 'em_analise', 422, 'Esta queixa ja foi finalizada.');

        $dados = $request->validate(['justificacao_rejeicao' => 'required|string|min:10|max:2000']);

        $queixa->update([
            'estado' => 'rejeitada',
            'justificacao_rejeicao' => $dados['justificacao_rejeicao'],
            'analisado_por' => $this->agenteAtualId(),
        ]);

        return response()->json(['success' => true, 'message' => 'Queixa rejeitada.']);
    }
}
