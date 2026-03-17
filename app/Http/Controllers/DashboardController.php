<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use App\Models\Ocorrencia;
use App\Models\Detencao;
use App\Models\Investigacao;
use App\Models\Unidade;
use App\Models\Alerta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('painel.index');
    }

    public function metricas()
    {
        $user = auth()->user();
        $uid = $user->unidade_id;
        $global = $user->isAdmin() || $user->isComandante();

        $qOc = Ocorrencia::query();
        $qDt = Detencao::query();
        $qAg = Agente::activos();

        if (!$global && $uid) {
            $qOc->where('unidade_id', $uid);
            $qDt->where('unidade_id', $uid);
            $qAg->where('unidade_id', $uid);
        }

        $mes = now()->month;
        $ano = now()->year;

        return response()->json([
            'total_ocorrencias' => (clone $qOc)->count(),
            'ocorrencias_mes' => (clone $qOc)->whereMonth('data_ocorrencia', $mes)->whereYear('data_ocorrencia', $ano)->count(),
            'ocorrencias_abertas' => (clone $qOc)->whereNotIn('estado_id', [5, 6, 7])->count(),
            'ocorrencias_resolvidas' => (clone $qOc)->where('estado_id', 5)->count(),
            'total_detencoes' => (clone $qDt)->count(),
            'detencoes_mes' => (clone $qDt)->whereMonth('data_detencao', $mes)->whereYear('data_detencao', $ano)->count(),
            'total_investigacoes' => Investigacao::count(),
            'investigacoes_activas' => Investigacao::whereIn('estado_id', [1, 2])->count(),
            'total_agentes' => (clone $qAg)->count(),
            'total_unidades' => Unidade::activas()->count(),
            'total_esquadras' => Unidade::esquadras()->activas()->count(),
            'alertas_activos' => Alerta::activos()->count(),
            'crimes_por_tipo' => $this->crimesPorTipo($global, $uid),
            'crimes_por_mes' => $this->crimesPorMes($global, $uid),
            'ultimas_ocorrencias' => $this->ultimas($global, $uid),
        ]);
    }

    private function crimesPorTipo($global, $uid)
    {
        $q = Ocorrencia::selectRaw('tipo_crime_id, COUNT(*) as total')
            ->whereYear('data_ocorrencia', now()->year)
            ->groupBy('tipo_crime_id')->orderByDesc('total')->limit(10);
        if (!$global && $uid) $q->where('unidade_id', $uid);

        return $q->get()->map(function ($i) {
            $i->tipo_nome = $i->tipoCrime?->nome ?? 'N/A';
            return $i;
        });
    }

    private function crimesPorMes($global, $uid)
    {
        $q = Ocorrencia::selectRaw('MONTH(data_ocorrencia) as mes, COUNT(*) as total')
            ->whereYear('data_ocorrencia', now()->year)
            ->groupBy('mes')->orderBy('mes');
        if (!$global && $uid) $q->where('unidade_id', $uid);
        return $q->get();
    }

    private function ultimas($global, $uid)
    {
        $q = Ocorrencia::with(['tipoCrime', 'estado', 'unidade'])->orderByDesc('created_at')->limit(10);
        if (!$global && $uid) $q->where('unidade_id', $uid);
        return $q->get();
    }
}