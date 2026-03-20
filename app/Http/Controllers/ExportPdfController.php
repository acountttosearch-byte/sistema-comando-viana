<?php

namespace App\Http\Controllers;

use App\Models\Ocorrencia;
use App\Models\Detencao;
use App\Models\Agente;
use App\Models\Unidade;
use App\Models\Alerta;
use App\Models\Configuracao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportPdfController extends Controller
{
    public function relatorioCriminalidade(Request $request)
    {
        $request->validate([
            'periodo_inicio' => 'required|date',
            'periodo_fim' => 'required|date',
        ]);

        $inicio = $request->periodo_inicio;
        $fim = $request->periodo_fim;
        $uid = $request->unidade_id;

        $qOc = Ocorrencia::whereDate('data_ocorrencia', '>=', $inicio)
                          ->whereDate('data_ocorrencia', '<=', $fim);
        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);

        if ($uid) {
            $qOc->where('unidade_id', $uid);
            $qDt->where('unidade_id', $uid);
        }

        $totalOc = (clone $qOc)->count();
        $resolvidas = (clone $qOc)->where('estado_id', 5)->count();
        $abertas = (clone $qOc)->whereNotIn('estado_id', [5, 6, 7])->count();
        $tribunal = (clone $qOc)->where('estado_id', 6)->count();
        $arquivadas = (clone $qOc)->where('estado_id', 7)->count();
        $totalDet = (clone $qDt)->count();
        $taxa = $totalOc > 0 ? round(($resolvidas / $totalOc) * 100, 1) : 0;

        $crimesTipo = (clone $qOc)->selectRaw('tipo_crime_id, COUNT(*) as total')
            ->groupBy('tipo_crime_id')->orderByDesc('total')->limit(15)->get()
            ->map(fn($i) => ['tipo' => $i->tipoCrime?->nome ?? 'N/A', 'total' => $i->total]);

        $crimesBairro = (clone $qOc)->selectRaw('bairro, COUNT(*) as total')
            ->whereNotNull('bairro')->where('bairro', '!=', '')
            ->groupBy('bairro')->orderByDesc('total')->limit(15)->get();

        $crimesPrio = (clone $qOc)->selectRaw('prioridade, COUNT(*) as total')
            ->groupBy('prioridade')->get();

        $crimesMes = (clone $qOc)->selectRaw('MONTH(data_ocorrencia) as mes, COUNT(*) as total')
            ->groupBy('mes')->orderBy('mes')->get();

        $crimesPorUnidade = (clone $qOc)->selectRaw('unidade_id, COUNT(*) as total')
            ->groupBy('unidade_id')->orderByDesc('total')->get()
            ->map(fn($i) => ['unidade' => $i->unidade?->nome ?? 'N/A', 'total' => $i->total]);

        $unidadeNome = $uid ? (Unidade::find($uid)?->nome ?? 'N/A') : 'Todas as Unidades';
        $entidade = Configuracao::valor('entidade', 'Comando Municipal de Viana');

        $data = [
            'entidade' => $entidade,
            'unidade_nome' => $unidadeNome,
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'total_ocorrencias' => $totalOc,
            'resolvidas' => $resolvidas,
            'abertas' => $abertas,
            'tribunal' => $tribunal,
            'arquivadas' => $arquivadas,
            'total_detencoes' => $totalDet,
            'taxa_resolucao' => $taxa,
            'crimes_por_tipo' => $crimesTipo,
            'crimes_por_bairro' => $crimesBairro,
            'crimes_por_prioridade' => $crimesPrio,
            'crimes_por_mes' => $crimesMes,
            'crimes_por_unidade' => $crimesPorUnidade,
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.relatorio-criminalidade', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('relatorio-' . $inicio . '-a-' . $fim . '.pdf');
    }

    public function fichaOcorrencia(Ocorrencia $ocorrencia)
    {
        $ocorrencia->load([
            'tipoCrime.categoria', 'estado', 'agenteRegisto', 'agenteResponsavel',
            'unidade', 'envolvimentos.pessoa', 'envolvimentos.tipoEnvolvimento',
            'evidencias.tipoEvidencia', 'detencoes.pessoa', 'detencoes.estado',
            'investigacoes.investigador', 'investigacoes.estado',
        ]);

        $data = [
            'oc' => $ocorrencia,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-ocorrencia', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('ocorrencia-' . $ocorrencia->numero_ocorrencia . '.pdf');
    }

    public function fichaDetencao(Detencao $detencao)
    {
        $detencao->load(['pessoa', 'ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade', 'estado']);

        $data = [
            'dt' => $detencao,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-detencao', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('detencao-' . $detencao->numero_detencao . '.pdf');
    }

    public function listaAgentes(Request $request)
    {
        $q = Agente::with(['patente', 'unidade', 'user.perfil'])->orderBy('nome');
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);

        $data = [
            'agentes' => $q->get(),
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'filtro_estado' => $request->estado ?? 'Todos',
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.lista-agentes', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('agentes-' . date('Y-m-d') . '.pdf');
    }

    public function relatorioAlertas(Request $request)
    {
        $q = Alerta::with(['tipoAlerta', 'pessoa', 'criadoPor'])->orderByDesc('created_at');
        if ($request->filled('estado')) $q->where('estado', $request->estado);

        $data = [
            'alertas' => $q->get(),
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.relatorio-alertas', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('alertas-' . date('Y-m-d') . '.pdf');
    }
}