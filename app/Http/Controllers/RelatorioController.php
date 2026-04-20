<?php

namespace App\Http\Controllers;

use App\Models\Relatorio;
use App\Models\Ocorrencia;
use App\Models\Detencao;
use App\Models\Patrulha;
use App\Models\PatrulhaIncidente;
use App\Models\Investigacao;
use App\Models\ProcessoCriminal;
use App\Models\Log;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $q = Relatorio::with(['tipoRelatorio', 'geradoPor', 'unidade']);

        if ($perfil === 'chefe_esquadra') {
            $q->where('unidade_id', $user->unidade_id);
        }

        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->whereHas('tipoRelatorio', fn($q3) => $q3->where('nome', 'like', "%$b%")));
        }

        return response()->json($q->orderByDesc('created_at')->paginate(20));
    }

    public function show(Relatorio $relatorio)
    {
        return response()->json($relatorio->load(['tipoRelatorio', 'geradoPor', 'unidade']));
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
        $tipoId = (int) $request->tipo_relatorio_id;

        // Gerar dados conforme o tipo de relatório
        $dados = match ($tipoId) {
            1 => $this->dadosCriminalidade($inicio, $fim, $uid),
            2 => $this->dadosDetencoes($inicio, $fim, $uid),
            3 => $this->dadosPatrulhas($inicio, $fim, $uid),
            4 => $this->dadosDesempenho($inicio, $fim, $uid),
            5 => $this->dadosEstatisticos($inicio, $fim, $uid),
            default => $this->dadosCriminalidade($inicio, $fim, $uid),
        };

        // Guardar relatorio
        $agente = auth()->user()->agente;
        $rel = Relatorio::create([
            'tipo_relatorio_id' => $tipoId,
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
            'relatorio' => $rel->load(['tipoRelatorio', 'geradoPor', 'unidade']),
            'dados' => $dados,
        ], 201);
    }

    // ═══════════════════════════════════════
    // TIPO 1: Relatório Mensal de Criminalidade
    // ═══════════════════════════════════════
    private function dadosCriminalidade(string $inicio, string $fim, ?int $uid): array
    {
        $qOc = Ocorrencia::whereDate('data_ocorrencia', '>=', $inicio)
                          ->whereDate('data_ocorrencia', '<=', $fim);
        if ($uid) $qOc->where('unidade_id', $uid);

        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);
        if ($uid) $qDt->where('detencoes.unidade_id', $uid);

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
            ->map(fn($i) => ['tipo' => $i->tipoCrime?->nome ?? 'Desconhecido', 'total' => $i->total]);

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

        // Crimes por mês
        $crimesMes = (clone $qOc)
            ->selectRaw('MONTH(data_ocorrencia) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return [
            'tipo_relatorio' => 'Criminalidade',
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
    }

    // ═══════════════════════════════════════
    // TIPO 2: Relatório de Detenções
    // ═══════════════════════════════════════
    private function dadosDetencoes(string $inicio, string $fim, ?int $uid): array
    {
        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);
        if ($uid) $qDt->where('detencoes.unidade_id', $uid);

        $totalDet = (clone $qDt)->count();

        // Por estado
        $porEstado = (clone $qDt)
            ->selectRaw('estado_id, COUNT(*) as total')
            ->groupBy('estado_id')
            ->get()
            ->map(fn($i) => ['estado' => $i->estado?->nome ?? 'Desconhecido', 'total' => $i->total]);

        // Por unidade
        $porUnidade = (clone $qDt)
            ->selectRaw('unidade_id, COUNT(*) as total')
            ->groupBy('unidade_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['unidade' => $i->unidade?->nome ?? 'Desconhecido', 'total' => $i->total]);

        // Por mês
        $porMes = (clone $qDt)
            ->selectRaw('MONTH(data_detencao) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Top tipos de crime associados (via ocorrência)
        $topCrimes = (clone $qDt)
            ->join('ocorrencias', 'detencoes.ocorrencia_id', '=', 'ocorrencias.id')
            ->join('tipos_crime', 'ocorrencias.tipo_crime_id', '=', 'tipos_crime.id')
            ->selectRaw('tipos_crime.nome as tipo, COUNT(*) as total')
            ->groupBy('tipos_crime.nome')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Libertados vs detidos
        $libertados = (clone $qDt)->where('estado_id', 4)->count();
        $emCustodia = (clone $qDt)->where('estado_id', 2)->count();
        $apresentados = (clone $qDt)->where('estado_id', 3)->count();

        return [
            'tipo_relatorio' => 'Detenções',
            'total_detencoes' => $totalDet,
            'libertados' => $libertados,
            'em_custodia' => $emCustodia,
            'apresentados_tribunal' => $apresentados,
            'detencoes_por_estado' => $porEstado,
            'detencoes_por_unidade' => $porUnidade,
            'detencoes_por_mes' => $porMes,
            'top_crimes_associados' => $topCrimes,
        ];
    }

    // ═══════════════════════════════════════
    // TIPO 3: Relatório de Patrulhas
    // ═══════════════════════════════════════
    private function dadosPatrulhas(string $inicio, string $fim, ?int $uid): array
    {
        $qPt = Patrulha::whereDate('data', '>=', $inicio)
                        ->whereDate('data', '<=', $fim);
        if ($uid) $qPt->where('unidade_id', $uid);

        $totalPat = (clone $qPt)->count();
        $concluidas = (clone $qPt)->where('estado', 'concluida')->count();
        $emCurso = (clone $qPt)->where('estado', 'em_curso')->count();
        $planeadas = (clone $qPt)->where('estado', 'planeada')->count();

        // Por zona
        $porZona = (clone $qPt)
            ->selectRaw('zona_id, COUNT(*) as total')
            ->groupBy('zona_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['zona' => $i->zona?->nome ?? 'Desconhecido', 'total' => $i->total]);

        // Por turno
        $porTurno = (clone $qPt)
            ->selectRaw('turno_id, COUNT(*) as total')
            ->groupBy('turno_id')
            ->get()
            ->map(fn($i) => ['turno' => $i->turno?->nome ?? 'Desconhecido', 'total' => $i->total]);

        // Incidentes no período
        $patIds = (clone $qPt)->pluck('id');
        $totalIncidentes = PatrulhaIncidente::whereIn('patrulha_id', $patIds)->count();

        // Por unidade
        $porUnidade = (clone $qPt)
            ->selectRaw('unidade_id, COUNT(*) as total')
            ->groupBy('unidade_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['unidade' => $i->unidade?->nome ?? 'Desconhecido', 'total' => $i->total]);

        return [
            'tipo_relatorio' => 'Patrulhas',
            'total_patrulhas' => $totalPat,
            'concluidas' => $concluidas,
            'em_curso' => $emCurso,
            'planeadas' => $planeadas,
            'total_incidentes' => $totalIncidentes,
            'patrulhas_por_zona' => $porZona,
            'patrulhas_por_turno' => $porTurno,
            'patrulhas_por_unidade' => $porUnidade,
        ];
    }

    // ═══════════════════════════════════════
    // TIPO 4: Relatório de Desempenho
    // ═══════════════════════════════════════
    private function dadosDesempenho(string $inicio, string $fim, ?int $uid): array
    {
        $qOc = Ocorrencia::whereDate('data_ocorrencia', '>=', $inicio)
                          ->whereDate('data_ocorrencia', '<=', $fim);
        if ($uid) $qOc->where('unidade_id', $uid);

        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);
        if ($uid) $qDt->where('detencoes.unidade_id', $uid);

        $totalOc = (clone $qOc)->count();
        $resolvidas = (clone $qOc)->where('estado_id', 5)->count();
        $taxaGlobal = $totalOc > 0 ? round(($resolvidas / $totalOc) * 100, 1) : 0;
        $totalDet = (clone $qDt)->count();

        // Taxa resolução por unidade
        $taxaPorUnidade = (clone $qOc)
            ->selectRaw('unidade_id, COUNT(*) as total, SUM(CASE WHEN estado_id = 5 THEN 1 ELSE 0 END) as resolvidas')
            ->groupBy('unidade_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn($i) => [
                'unidade' => $i->unidade?->nome ?? 'Desconhecido',
                'total' => $i->total,
                'resolvidas' => (int) $i->resolvidas,
                'taxa' => $i->total > 0 ? round(($i->resolvidas / $i->total) * 100, 1) : 0,
            ]);

        // Agentes mais activos (por ocorrências como responsável)
        $agentesMaisActivos = (clone $qOc)
            ->selectRaw('agente_responsavel_id, COUNT(*) as total')
            ->whereNotNull('agente_responsavel_id')
            ->groupBy('agente_responsavel_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'agente' => $i->agenteResponsavel?->nome ?? 'Desconhecido',
                'total' => $i->total,
            ]);

        // Detenções por agente
        $detencoesPorAgente = (clone $qDt)
            ->selectRaw('agente_responsavel_id, COUNT(*) as total')
            ->groupBy('agente_responsavel_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'agente' => $i->agenteResponsavel?->nome ?? 'Desconhecido',
                'total' => $i->total,
            ]);

        return [
            'tipo_relatorio' => 'Desempenho',
            'total_ocorrencias' => $totalOc,
            'ocorrencias_resolvidas' => $resolvidas,
            'total_detencoes' => $totalDet,
            'taxa_resolucao_global' => $taxaGlobal,
            'taxa_por_unidade' => $taxaPorUnidade,
            'agentes_mais_activos' => $agentesMaisActivos,
            'detencoes_por_agente' => $detencoesPorAgente,
        ];
    }

    // ═══════════════════════════════════════
    // TIPO 5: Relatório Estatístico (resumo geral)
    // ═══════════════════════════════════════
    private function dadosEstatisticos(string $inicio, string $fim, ?int $uid): array
    {
        $qOc = Ocorrencia::whereDate('data_ocorrencia', '>=', $inicio)
                          ->whereDate('data_ocorrencia', '<=', $fim);
        if ($uid) $qOc->where('unidade_id', $uid);

        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);
        if ($uid) $qDt->where('detencoes.unidade_id', $uid);

        $qInv = Investigacao::whereDate('data_inicio', '>=', $inicio)
                            ->whereDate('data_inicio', '<=', $fim);

        $qProc = ProcessoCriminal::whereDate('data_abertura', '>=', $inicio)
                                 ->whereDate('data_abertura', '<=', $fim);
        if ($uid) $qProc->where('unidade_id', $uid);

        $qPat = Patrulha::whereDate('data', '>=', $inicio)
                         ->whereDate('data', '<=', $fim);
        if ($uid) $qPat->where('unidade_id', $uid);

        $totalOc = (clone $qOc)->count();
        $resolvidas = (clone $qOc)->where('estado_id', 5)->count();
        $totalDet = (clone $qDt)->count();
        $totalInv = (clone $qInv)->count();
        $invConcluidas = (clone $qInv)->where('estado_id', 4)->count();
        $totalProc = (clone $qProc)->count();
        $procRemetidos = (clone $qProc)->where('estado', 'remetido_mp')->count();
        $totalPat = (clone $qPat)->count();
        $patConcluidas = (clone $qPat)->where('estado', 'concluida')->count();
        $taxa = $totalOc > 0 ? round(($resolvidas / $totalOc) * 100, 1) : 0;

        // Crimes por tipo (top 10)
        $crimesTipo = (clone $qOc)
            ->selectRaw('tipo_crime_id, COUNT(*) as total')
            ->groupBy('tipo_crime_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['tipo' => $i->tipoCrime?->nome ?? 'Desconhecido', 'total' => $i->total]);

        // Crimes por mês
        $crimesMes = (clone $qOc)
            ->selectRaw('MONTH(data_ocorrencia) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Por prioridade
        $crimesPrio = (clone $qOc)
            ->selectRaw('prioridade, COUNT(*) as total')
            ->groupBy('prioridade')
            ->get();

        return [
            'tipo_relatorio' => 'Estatístico',
            'total_ocorrencias' => $totalOc,
            'ocorrencias_resolvidas' => $resolvidas,
            'taxa_resolucao' => $taxa,
            'total_detencoes' => $totalDet,
            'total_investigacoes' => $totalInv,
            'investigacoes_concluidas' => $invConcluidas,
            'total_processos' => $totalProc,
            'processos_remetidos_mp' => $procRemetidos,
            'total_patrulhas' => $totalPat,
            'patrulhas_concluidas' => $patConcluidas,
            'crimes_por_tipo' => $crimesTipo,
            'crimes_por_mes' => $crimesMes,
            'crimes_por_prioridade' => $crimesPrio,
        ];
    }
}