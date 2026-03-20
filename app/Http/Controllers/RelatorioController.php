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
        return response()->json(
            Relatorio::with(['tipoRelatorio', 'geradoPor', 'unidade'])
                ->orderByDesc('created_at')
                ->paginate(20)
        );
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

        // Query ocorrencias no periodo
        $qOc = Ocorrencia::whereDate('data_ocorrencia', '>=', $inicio)
                          ->whereDate('data_ocorrencia', '<=', $fim);
        if ($uid) $qOc->where('unidade_id', $uid);

        // Query detencoes no periodo
        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);
        if ($uid) $qDt->where('unidade_id', $uid);

        $totalOc = (clone $qOc)->count();
        $resolvidas = (clone $qOc)->where('estado_id', 5)->count();
        $abertas = (clone $qOc)->whereNotIn('estado_id', [5, 6, 7])->count();
        $tribunal = (clone $qOc)->where('estado_id', 6)->count();
        $arquivadas = (clone $qOc)->where('estado_id', 7)->count();
        $totalDet = (clone $qDt)->count();
        $taxa = $totalOc > 0 ? round(($resolvidas / $totalOc) * 100, 1) : 0;

        // Crimes por tipo
        $crimesTipo = (clone $qOc)
            ->selectRaw('tipo_crime_id, COUNT(*) as total')
            ->groupBy('tipo_crime_id')
            ->orderByDesc('total')
            ->limit(15)
            ->get()
            ->map(function ($i) {
                return [
                    'tipo' => $i->tipoCrime?->nome ?? 'Desconhecido',
                    'total' => $i->total,
                ];
            });

        // Crimes por bairro
        $crimesBairro = (clone $qOc)
            ->selectRaw('bairro, COUNT(*) as total')
            ->whereNotNull('bairro')
            ->where('bairro', '!=', '')
            ->groupBy('bairro')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        // Crimes por prioridade
        $crimesPrio = (clone $qOc)
            ->selectRaw('prioridade, COUNT(*) as total')
            ->groupBy('prioridade')
            ->get();

        // Crimes por mes
        $crimesMes = (clone $qOc)
            ->selectRaw('MONTH(data_ocorrencia) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $dados = [
            'total_ocorrencias' => $totalOc,
            'ocorrencias_resolvidas' => $resolvidas,
            'ocorrencias_abertas' => $abertas,
            'ocorrencias_tribunal' => $tribunal,
            'ocorrencias_arquivadas' => $arquivadas,
            'total_detencoes' => $totalDet,
            'taxa_resolucao' => $taxa,
            'crimes_por_tipo' => $crimesTipo,
            'crimes_por_bairro' => $crimesBairro,
            'crimes_por_prioridade' => $crimesPrio,
            'crimes_por_mes' => $crimesMes,
        ];

        // Guardar relatorio
        $agente = auth()->user()->agente;
        $rel = Relatorio::create([
            'tipo_relatorio_id' => $request->tipo_relatorio_id,
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'unidade_id' => $uid,
            'gerado_por' => $agente ? $agente->id : 1,
            'dados' => $dados,
        ]);

        Log::registar('criar', 'relatorios', $rel->id, 'Relatorio gerado');

        return response()->json([
            'success' => true,
            'message' => 'Relatorio gerado com sucesso.',
            'relatorio' => $rel,
            'dados' => $dados,
        ], 201);
    }
}