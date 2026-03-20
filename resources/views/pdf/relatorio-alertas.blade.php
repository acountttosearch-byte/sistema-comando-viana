<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Alertas</title>
    @include('pdf.partials.styles')
</head>
<body>
<div class="page">
    <div class="pdf-header">
        <div class="pdf-right"><div>Total: {{ $alertas->count() }} alertas</div><div>{{ $data_geracao }}</div></div>
        <div class="pdf-entity">REPUBLICA DE ANGOLA - POLICIA NACIONAL</div>
        <div class="pdf-title">Relatorio de Alertas / BOLO</div>
        <div class="pdf-subtitle">{{ $entidade }}</div>
        <div class="clear"></div>
    </div>

    @foreach($alertas as $alerta)
    <div style="border:1px solid #e5e7eb;border-radius:4px;padding:12px;margin-bottom:10px;border-left:4px solid {{ $alerta->prioridade === 'urgente' ? '#dc2626' : ($alerta->prioridade === 'alta' ? '#f59e0b' : '#3b82f6') }};">
        <div style="margin-bottom:4px;">
            <strong style="font-size:12px;">{{ $alerta->titulo }}</strong>
            @if($alerta->prioridade === 'urgente') <span class="pdf-badge pdf-badge-red">URGENTE</span>
            @elseif($alerta->prioridade === 'alta') <span class="pdf-badge pdf-badge-orange">ALTA</span>
            @else <span class="pdf-badge pdf-badge-blue">NORMAL</span>
            @endif
            <span class="pdf-badge {{ $alerta->estado === 'activo' ? 'pdf-badge-red' : 'pdf-badge-green' }}">{{ $alerta->estado }}</span>
        </div>
        <p style="font-size:10px;margin-bottom:4px;">{{ $alerta->descricao }}</p>
        <div style="font-size:8px;color:#6b7280;">
            {{ $alerta->tipoAlerta?->nome }} |
            @if($alerta->pessoa) Pessoa: {{ $alerta->pessoa->nome }} | @endif
            Emitido por: {{ $alerta->criadoPor?->nome }} |
            {{ $alerta->created_at?->format('d/m/Y H:i') }}
        </div>
    </div>
    @endforeach

    <div class="pdf-footer">{{ $entidade }} - SCGD | {{ $data_geracao }}</div>
</div>
</body>
</html>