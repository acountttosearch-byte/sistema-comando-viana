<?php

namespace App\Http\Controllers;

use App\Models\Relatorio;
use App\Models\Ocorrencia;
use App\Models\Detencao;
use App\Models\Log;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function index()
    {
        return response()->json(Relatorio::with(['tipoRelatorio', 'geradoPor', 'unidade'])->orderByDesc('created_at')->paginate(20));
    }

    public function gerar(Request $request)
    {
        $request->validate([
            'tipo_relatorio_id' => 'required|exists:tipos_relatorio,id',
            'periodo_inicio' => 'required|date',
            'periodo_fim' => 'required|date|after_or_equal:periodo_inicio',
        ]);

        $inicio = $request->periodo_inicio;
        $fim = $request->periodo_fim;
        $uid = $request->unidade_id;

        $qOc = Ocorrencia::whereBetween('data_ocorrencia', [$inicio, $fim]);
        $qDt = Detencao::whereBetween('data_detencao', [$inicio, $fim]);
        if ($uid) { $qOc->where('unidade_id', $uid); $qDt->where('unidade_id', $uid); }

        $totalOc = (clone $qOc)->count();
        $resolvidas = (clone $qOc)->where('estado_id', 5)->count();

        $dados = [
            'total_ocorrencias' => $totalOc,
            'ocorrencias_resolvidas' => $resolvidas,
            'total_detencoes' => (clone $qDt)->count(),
            'taxa_resolucao' => $totalOc > 0 ? round(($resolvidas / $totalOc) * 100, 1) : 0,
            'crimes_por_tipo' => (clone $qOc)->selectRaw('tipo_crime_id, COUNT(*) as total')
                ->groupBy('tipo_crime_id')->get()->map(fn($i) => ['tipo' => $i->tipoCrime?->nome, 'total' => $i->total]),
            'crimes_por_bairro' => (clone $qOc)->selectRaw('bairro, COUNT(*) as total')
                ->whereNotNull('bairro')->groupBy('bairro')->orderByDesc('total')->get(),
        ];

        $rel = Relatorio::create([
            'tipo_relatorio_id' => $request->tipo_relatorio_id,
            'periodo_inicio' => $inicio, 'periodo_fim' => $fim,
            'unidade_id' => $uid, 'gerado_por' => auth()->user()->agente->id,
            'dados' => $dados,
        ]);

        Log::registar('criar', 'relatorios', $rel->id, "Relatório gerado");
        return response()->json(['success' => true, 'relatorio' => $rel, 'dados' => $dados], 201);
    }
}