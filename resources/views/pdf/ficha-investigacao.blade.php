<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Investigação {{ $inv->numero_investigacao }}</title>
    @include('pdf.partials.styles')
</head>
<body>
<div class="page">
    <div class="pdf-header">
        <div class="pdf-right">
            <div style="font-size:14px;font-weight:bold;">{{ $inv->numero_investigacao }}</div>
            <div style="margin-top:4px;"><span class="pdf-badge pdf-badge-blue">{{ $inv->progresso }}%</span></div>
        </div>
        <div class="pdf-entity">REPUBLICA DE ANGOLA - POLICIA NACIONAL</div>
        <div class="pdf-title">Ficha de Investigação</div>
        <div class="pdf-subtitle">{{ $entidade }}</div>
        <div class="clear"></div>
    </div>

    <div class="section-title">Dados da Investigação</div>
    <div class="info-row"><span class="info-label">Número:</span><span class="info-value font-bold">{{ $inv->numero_investigacao }}</span></div>
    <div class="info-row"><span class="info-label">Investigador:</span><span class="info-value">{{ $inv->investigador?->nome }}</span></div>
    <div class="info-row"><span class="info-label">Estado:</span><span class="info-value">{{ $inv->estado?->nome }}</span></div>
    <div class="info-row"><span class="info-label">Progresso:</span><span class="info-value">{{ $inv->progresso }}%</span></div>
    <div class="info-row"><span class="info-label">Data Início:</span><span class="info-value">{{ $inv->data_inicio?->format('d/m/Y') }}</span></div>
    <div class="info-row"><span class="info-label">Prazo:</span><span class="info-value">{{ $inv->prazo?->format('d/m/Y') ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Data Conclusão:</span><span class="info-value">{{ $inv->data_fim?->format('d/m/Y') ?? 'Em curso' }}</span></div>

    @if($inv->resumo)
    <div class="section-title">Resumo</div>
    <p style="font-size:10px;line-height:1.7;text-align:justify;">{{ $inv->resumo }}</p>
    @endif

    @php $oc = $inv->ocorrencia; @endphp
    <div class="section-title">Ocorrência Associada</div>
    <div class="info-row"><span class="info-label">Número:</span><span class="info-value font-bold">{{ $oc->numero_ocorrencia }}</span></div>
    <div class="info-row"><span class="info-label">Tipo de Crime:</span><span class="info-value">{{ $oc->tipoCrime?->nome }}</span></div>
    <div class="info-row"><span class="info-label">Data:</span><span class="info-value">{{ $oc->data_ocorrencia?->format('d/m/Y') }}</span></div>
    <div class="info-row"><span class="info-label">Local:</span><span class="info-value">{{ $oc->local }}</span></div>

    @if($inv->notas->count() > 0)
    <div class="section-title">Notas de Investigação ({{ $inv->notas->count() }})</div>
    @foreach($inv->notas as $nota)
    <div style="border:1px solid #ddd;padding:8px;margin-bottom:6px;border-radius:4px;">
        <div style="font-weight:bold;font-size:10px;">{{ $nota->titulo }}</div>
        <div style="font-size:9px;color:#666;margin:2px 0;">{{ $nota->agente?->nome }} — {{ $nota->created_at?->format('d/m/Y H:i') }}</div>
        <div style="font-size:10px;line-height:1.5;">{{ $nota->conteudo }}</div>
    </div>
    @endforeach
    @endif
</div>
</body>
</html>
