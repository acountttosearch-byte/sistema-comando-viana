<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Processo {{ $proc->numero_processo }}</title>
    @include('pdf.partials.styles')
</head>
<body>
<div class="page">
    <div class="pdf-header">
        <div class="pdf-right">
            <div style="font-size:14px;font-weight:bold;">{{ $proc->numero_processo }}</div>
            <div style="margin-top:4px;">
                @if($proc->estado === 'em_instrucao') <span class="pdf-badge pdf-badge-blue">EM INSTRUÇÃO</span>
                @elseif($proc->estado === 'concluido') <span class="pdf-badge pdf-badge-green">CONCLUÍDO</span>
                @elseif($proc->estado === 'remetido_mp') <span class="pdf-badge pdf-badge-orange">REMETIDO AO MP</span>
                @else <span class="pdf-badge pdf-badge-gray">ARQUIVADO</span>
                @endif
            </div>
        </div>
        <div class="pdf-entity">REPUBLICA DE ANGOLA - POLICIA NACIONAL</div>
        <div class="pdf-title">Ficha de Processo Criminal</div>
        <div class="pdf-subtitle">{{ $entidade }}</div>
        <div class="clear"></div>
    </div>

    <div class="section-title">Dados do Processo</div>
    <div class="info-row"><span class="info-label">Número:</span><span class="info-value font-bold">{{ $proc->numero_processo }}</span></div>
    <div class="info-row"><span class="info-label">Data de Abertura:</span><span class="info-value">{{ $proc->data_abertura?->format('d/m/Y') }}</span></div>
    <div class="info-row"><span class="info-label">Data de Conclusão:</span><span class="info-value">{{ $proc->data_conclusao?->format('d/m/Y') ?? 'Pendente' }}</span></div>
    <div class="info-row"><span class="info-label">Data de Remessa:</span><span class="info-value">{{ $proc->data_remessa?->format('d/m/Y') ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Destino Remessa:</span><span class="info-value">{{ $proc->destino_remessa ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Agente Responsável:</span><span class="info-value">{{ $proc->agenteResponsavel?->nome }}</span></div>
    <div class="info-row"><span class="info-label">Unidade:</span><span class="info-value">{{ $proc->unidade?->nome }}</span></div>

    @if($proc->resumo)
    <div class="section-title">Resumo do Processo</div>
    <p style="font-size:10px;line-height:1.7;text-align:justify;">{{ $proc->resumo }}</p>
    @endif

    @php $oc = $proc->ocorrencia; @endphp
    <div class="section-title">Ocorrência Associada</div>
    <div class="info-row"><span class="info-label">Número:</span><span class="info-value font-bold">{{ $oc->numero_ocorrencia }}</span></div>
    <div class="info-row"><span class="info-label">Tipo de Crime:</span><span class="info-value">{{ $oc->tipoCrime?->nome }} ({{ $oc->tipoCrime?->categoria?->nome }})</span></div>
    <div class="info-row"><span class="info-label">Data / Hora:</span><span class="info-value">{{ $oc->data_ocorrencia?->format('d/m/Y') }} {{ $oc->hora_ocorrencia }}</span></div>
    <div class="info-row"><span class="info-label">Local:</span><span class="info-value">{{ $oc->local }}</span></div>
    <div class="info-row"><span class="info-label">Bairro:</span><span class="info-value">{{ $oc->bairro ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Estado:</span><span class="info-value">{{ $oc->estado?->nome }}</span></div>

    <div class="section-title">Descrição dos Factos</div>
    <p style="font-size:10px;line-height:1.7;text-align:justify;">{{ $oc->descricao }}</p>

    @if($oc->envolvimentos->count() > 0)
    <div class="section-title">Pessoas Envolvidas</div>
    <table class="pdf-table">
        <thead><tr><th>Nome</th><th>BI</th><th>Tipo</th><th>Observações</th></tr></thead>
        <tbody>
            @foreach($oc->envolvimentos as $env)
            <tr>
                <td class="font-bold">{{ $env->pessoa?->nome }}</td>
                <td>{{ $env->pessoa?->bi ?? '-' }}</td>
                <td>{{ $env->tipoEnvolvimento?->nome }}</td>
                <td>{{ $env->descricao ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($oc->detencoes->count() > 0)
    <div class="section-title">Detenções ({{ $oc->detencoes->count() }})</div>
    <table class="pdf-table">
        <thead><tr><th>Número</th><th>Detido</th><th>BI</th><th>Data</th><th>Motivo</th><th>Estado</th></tr></thead>
        <tbody>
            @foreach($oc->detencoes as $dt)
            <tr>
                <td>{{ $dt->numero_detencao }}</td>
                <td class="font-bold">{{ $dt->pessoa?->nome }}</td>
                <td>{{ $dt->pessoa?->bi ?? '-' }}</td>
                <td>{{ $dt->data_detencao?->format('d/m/Y H:i') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($dt->motivo, 60) }}</td>
                <td>{{ $dt->estado?->nome }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($oc->evidencias->count() > 0)
    <div class="section-title">Evidências ({{ $oc->evidencias->count() }})</div>
    <table class="pdf-table">
        <thead><tr><th>Código</th><th>Tipo</th><th>Descrição</th><th>Estado</th></tr></thead>
        <tbody>
            @foreach($oc->evidencias as $ev)
            <tr><td class="font-bold">{{ $ev->codigo }}</td><td>{{ $ev->tipoEvidencia?->nome }}</td><td>{{ $ev->descricao }}</td><td>{{ $ev->estado }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($oc->investigacoes->count() > 0)
    <div class="section-title">Investigações ({{ $oc->investigacoes->count() }})</div>
    <table class="pdf-table">
        <thead><tr><th>Número</th><th>Investigador</th><th>Progresso</th><th>Estado</th></tr></thead>
        <tbody>
            @foreach($oc->investigacoes as $inv)
            <tr>
                <td>{{ $inv->numero_investigacao }}</td>
                <td>{{ $inv->investigador?->nome }}</td>
                <td>{{ $inv->progresso }}%</td>
                <td>{{ $inv->estado?->nome }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($proc->parecer_final)
    <div class="section-title">Parecer Final</div>
    <p style="font-size:10px;line-height:1.7;text-align:justify;border:1px solid #ccc;padding:10px;">{{ $proc->parecer_final }}</p>
    @endif

    <div class="pdf-footer">{{ $entidade }} - SCGD | {{ $data_geracao }} | {{ $gerado_por }}</div>
</div>
</body>
</html>
