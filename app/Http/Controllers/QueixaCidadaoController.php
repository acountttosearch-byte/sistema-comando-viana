<?php

namespace App\Http\Controllers;

use App\Models\QueixaCidadao;
use App\Models\Ocorrencia;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueixaCidadaoController extends Controller
{
    public function index(Request $request)
    {
        $q = QueixaCidadao::with('analisadoPor');
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        return response()->json($q->orderByDesc('created_at')->paginate(20));
    }

    public function submeter(Request $request)
    {
        $request->validate([
            'nome_cidadao' => 'required|string|max:200',
            'telefone' => 'required|string|max:20',
            'tipo_queixa' => 'required|string|max:100',
            'descricao' => 'required|string',
        ]);

        $qx = QueixaCidadao::create(array_merge($request->all(), [
            'protocolo' => QueixaCidadao::gerarProtocolo(),
            'estado' => 'recebida',
        ]));

        return response()->json(['success' => true, 'message' => 'Queixa submetida.', 'protocolo' => $qx->protocolo], 201);
    }

    public function consultar(string $protocolo)
    {
        $qx = QueixaCidadao::where('protocolo', $protocolo)->first();
        if (!$qx) return response()->json(['error' => 'Protocolo não encontrado.'], 404);
        return response()->json(['protocolo' => $qx->protocolo, 'estado' => $qx->estado, 'data_submissao' => $qx->created_at->format('d/m/Y H:i')]);
    }

    public function converter(Request $request, QueixaCidadao $queixa)
    {
        $request->validate([
            'tipo_crime_id' => 'required|exists:tipos_crime,id',
            'prioridade' => 'required|in:baixa,media,alta,critica',
            'unidade_id' => 'required|exists:unidades,id',
        ]);

        return DB::transaction(function () use ($request, $queixa) {
            $ag = auth()->user()->agente;
            $oc = Ocorrencia::create([
                'numero_ocorrencia' => Ocorrencia::gerarNumero(),
                'tipo_crime_id' => $request->tipo_crime_id,
                'descricao' => $queixa->descricao,
                'data_ocorrencia' => $queixa->created_at->toDateString(),
                'local' => $queixa->local ?? 'A definir',
                'prioridade' => $request->prioridade,
                'estado_id' => 1,
                'agente_registo_id' => $ag->id,
                'unidade_id' => $request->unidade_id,
            ]);
            $queixa->update(['estado' => 'convertida', 'ocorrencia_id' => $oc->id, 'analisado_por' => $ag->id]);
            Log::registar('criar', 'ocorrencias', $oc->id, "Ocorrência criada da queixa {$queixa->protocolo}");
            return response()->json(['success' => true, 'message' => 'Queixa convertida.', 'ocorrencia' => $oc]);
        });
    }

    public function rejeitar(Request $request, QueixaCidadao $queixa)
    {
        $request->validate(['justificacao_rejeicao' => 'required|string']);
        $queixa->update(['estado' => 'rejeitada', 'justificacao_rejeicao' => $request->justificacao_rejeicao, 'analisado_por' => auth()->user()->agente->id]);
        return response()->json(['success' => true, 'message' => 'Queixa rejeitada.']);
    }
}