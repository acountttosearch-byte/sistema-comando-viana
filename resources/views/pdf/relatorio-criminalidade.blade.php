<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Criminalidade</title>
    @include('pdf.partials.styles')
</head>
<body>
<div class="page">

    <div class="pdf-header">
        <div class="pdf-right">
            <div>Periodo: {{ \Carbon\Carbon::parse($periodo_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($periodo_fim)->format('d/m/Y') }}</div>
            <div>Gerado: {{ $data_geracao }}</div>
            <div>Por: {{ $gerado_por }}</div>
            <div style="margin-top:6px;"><span class="pdf-badge pdf-badge-blue">CONFIDENCIAL</span></div>
        </div>
        <div class="pdf-entity">REPUBLICA DE ANGOLA - POLICIA NACIONAL</div>
        <div class="pdf-title">Relatorio de Criminalidade</div>
        <div class="pdf-subtitle">{{ $entidade }} - {{ $unidade_nome }}</div>
        <div class="clear"></div>
    </div>

    <div class="stats-row">
        <div class="stat-box"><span class="stat-num">{{ $total_ocorrencias }}</span><span class="stat-txt">Ocorrencias</span></div>
        <div class="stat-box"><span class="stat-num">{{ $resolvidas }}</span><span class="stat-txt">Resolvidas</span></div>
        <div class="stat-box"><span class="stat-num">{{ $abertas }}</span><span class="stat-txt">Abertas</span></div>
        <div class="stat-box"><span class="stat-num">{{ $total_detencoes }}</span><span class="stat-txt">Detencoes</span></div>
        <div class="stat-box"><span class="stat-num">{{ $taxa_resolucao }}%</span><span class="stat-txt">Resolucao</span></div>
    </div>

    @if($crimes_por_tipo->count() > 0)
    <div class="section-title">Ocorrencias por Tipo de Crime</div>
    <table class="pdf-table">
        <thead><tr><th style="width:60%">Tipo de Crime</th><th>Quantidade</th><th>%</th></tr></thead>
        <tbody>
            @foreach($crimes_por_tipo as $crime)
            <tr>
                <td>{{ $crime['tipo'] }}</td>
                <td class="text-center font-bold">{{ $crime['total'] }}</td>
                <td class="text-center">{{ $total_ocorrencias > 0 ? round(($crime['total'] / $total_ocorrencias) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($crimes_por_bairro->count() > 0)
    <div class="section-title">Ocorrencias por Bairro</div>
    <table class="pdf-table">
        <thead><tr><th style="width:60%">Bairro</th><th>Quantidade</th><th>%</th></tr></thead>
        <tbody>
            @foreach($crimes_por_bairro as $bairro)
            <tr>
                <td>{{ $bairro->bairro }}</td>
                <td class="text-center font-bold">{{ $bairro->total }}</td>
                <td class="text-center">{{ $total_ocorrencias > 0 ? round(($bairro->total / $total_ocorrencias) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($crimes_por_prioridade->count() > 0)
    <div class="section-title">Ocorrencias por Prioridade</div>
    <table class="pdf-table">
        <thead><tr><th>Prioridade</th><th>Quantidade</th></tr></thead>
        <tbody>
            @foreach($crimes_por_prioridade as $prio)
            <tr>
                <td>
                    @if($prio->prioridade === 'critica') <span class="pdf-badge pdf-badge-red">{{ ucfirst($prio->prioridade) }}</span>
                    @elseif($prio->prioridade === 'alta') <span class="pdf-badge pdf-badge-orange">{{ ucfirst($prio->prioridade) }}</span>
                    @elseif($prio->prioridade === 'media') <span class="pdf-badge pdf-badge-blue">{{ ucfirst($prio->prioridade) }}</span>
                    @else <span class="pdf-badge pdf-badge-green">{{ ucfirst($prio->prioridade) }}</span>
                    @endif
                </td>
                <td class="text-center font-bold">{{ $prio->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($crimes_por_mes->count() > 0)
    <div class="section-title">Evolucao Mensal</div>
    <table class="pdf-table">
        <thead><tr><th>Mes</th><th>Ocorrencias</th></tr></thead>
        <tbody>
            @php $meses = ['','Janeiro','Fevereiro','Marco','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']; @endphp
            @foreach($crimes_por_mes as $mes)
            <tr><td>{{ $meses[$mes->mes] ?? $mes->mes }}</td><td class="text-center font-bold">{{ $mes->total }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($crimes_por_unidade->count() > 0)
    <div class="section-title">Ocorrencias por Unidade</div>
    <table class="pdf-table">
        <thead><tr><th>Unidade</th><th>Ocorrencias</th></tr></thead>
        <tbody>
            @foreach($crimes_por_unidade as $un)
            <tr><td>{{ $un['unidade'] }}</td><td class="text-center font-bold">{{ $un['total'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Resumo</div>
    <div class="info-row"><span class="info-label">Total de Ocorrencias:</span><span class="info-value">{{ $total_ocorrencias }}</span></div>
    <div class="info-row"><span class="info-label">Resolvidas:</span><span class="info-value">{{ $resolvidas }} ({{ $taxa_resolucao }}%)</span></div>
    <div class="info-row"><span class="info-label">Abertas:</span><span class="info-value">{{ $abertas }}</span></div>
    <div class="info-row"><span class="info-label">Tribunal:</span><span class="info-value">{{ $tribunal }}</span></div>
    <div class="info-row"><span class="info-label">Arquivadas:</span><span class="info-value">{{ $arquivadas }}</span></div>
    <div class="info-row"><span class="info-label">Detencoes:</span><span class="info-value">{{ $total_detencoes }}</span></div>

    <div class="pdf-footer">{{ $entidade }} - Documento gerado pelo SCGD | {{ $data_geracao }}</div>
</div>
</body>
</html>