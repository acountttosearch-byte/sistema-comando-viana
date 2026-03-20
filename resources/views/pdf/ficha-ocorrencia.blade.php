<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ocorrencia {{ $oc->numero_ocorrencia }}</title>
    @include('pdf.partials.styles')
</head>
<body>
<div class="page">
    <div class="pdf-header">
        <div class="pdf-right">
            <div style="font-size:14px;font-weight:bold;">{{ $oc->numero_ocorrencia }}</div>
            <div style="margin-top:4px;">
                @if($oc->prioridade === 'critica') <span class="pdf-badge pdf-badge-red">CRITICA</span>
                @elseif($oc->prioridade === 'alta') <span class="pdf-badge pdf-badge-orange">ALTA</span>
                @elseif($oc->prioridade === 'media') <span class="pdf-badge pdf-badge-blue">MEDIA</span>
                @else <span class="pdf-badge pdf-badge-green">BAIXA</span>
                @endif
            </div>
        </div>
        <div class="pdf-entity">REPUBLICA DE ANGOLA - POLICIA NACIONAL</div>
        <div class="pdf-title">Ficha de Ocorrencia</div>
        <div class="pdf-subtitle">{{ $entidade }}</div>
        <div class="clear"></div>
    </div>

    <div class="section-title">Dados da Ocorrencia</div>
    <div class="info-row"><span class="info-label">Numero:</span><span class="info-value font-bold">{{ $oc->numero_ocorrencia }}</span></div>
    <div class="info-row"><span class="info-label">Tipo de Crime:</span><span class="info-value">{{ $oc->tipoCrime?->nome }} ({{ $oc->tipoCrime?->categoria?->nome }})</span></div>
    <div class="info-row"><span class="info-label">Data / Hora:</span><span class="info-value">{{ $oc->data_ocorrencia?->format('d/m/Y') }} {{ $oc->hora_ocorrencia }}</span></div>
    <div class="info-row"><span class="info-label">Local:</span><span class="info-value">{{ $oc->local }}</span></div>
    <div class="info-row"><span class="info-label">Bairro:</span><span class="info-value">{{ $oc->bairro ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Unidade:</span><span class="info-value">{{ $oc->unidade?->nome }}</span></div>
    <div class="info-row"><span class="info-label">Registado por:</span><span class="info-value">{{ $oc->agenteRegisto?->nome }} ({{ $oc->agenteRegisto?->nip }})</span></div>
    <div class="info-row"><span class="info-label">Responsavel:</span><span class="info-value">{{ $oc->agenteResponsavel?->nome ?? 'Nao atribuido' }}</span></div>
    <div class="info-row"><span class="info-label">Estado:</span><span class="info-value">{{ $oc->estado?->nome }}</span></div>

    <div class="section-title">Descricao dos Factos</div>
    <p style="font-size:10px;line-height:1.7;text-align:justify;">{{ $oc->descricao }}</p>

    @if($oc->envolvimentos->count() > 0)
    <div class="section-title">Pessoas Envolvidas</div>
    <table class="pdf-table">
        <thead><tr><th>Nome</th><th>BI</th><th>Tipo</th></tr></thead>
        <tbody>
            @foreach($oc->envolvimentos as $env)
            <tr>
                <td class="font-bold">{{ $env->pessoa?->nome }}</td>
                <td>{{ $env->pessoa?->bi ?? '-' }}</td>
                <td>{{ $env->tipoEnvolvimento?->nome }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($oc->evidencias->count() > 0)
    <div class="section-title">Evidencias ({{ $oc->evidencias->count() }})</div>
    <table class="pdf-table">
        <thead><tr><th>Codigo</th><th>Tipo</th><th>Descricao</th><th>Estado</th></tr></thead>
        <tbody>
            @foreach($oc->evidencias as $ev)
            <tr><td class="font-bold">{{ $ev->codigo }}</td><td>{{ $ev->tipoEvidencia?->nome }}</td><td>{{ $ev->descricao }}</td><td>{{ $ev->estado }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($oc->detencoes->count() > 0)
    <div class="section-title">Detencoes</div>
    <table class="pdf-table">
        <thead><tr><th>Numero</th><th>Detido</th><th>Data</th><th>Estado</th></tr></thead>
        <tbody>
            @foreach($oc->detencoes as $dt)
            <tr><td>{{ $dt->numero_detencao }}</td><td>{{ $dt->pessoa?->nome }}</td><td>{{ $dt->data_detencao?->format('d/m/Y H:i') }}</td><td>{{ $dt->estado?->nome }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="pdf-footer">{{ $entidade }} - SCGD | {{ $data_geracao }} | {{ $gerado_por }}</div>
</div>
</body>
</html>