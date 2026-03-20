<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detencao {{ $dt->numero_detencao }}</title>
    @include('pdf.partials.styles')
</head>
<body>
<div class="page">
    <div class="pdf-header">
        <div class="pdf-right"><div style="font-size:14px;font-weight:bold;">{{ $dt->numero_detencao }}</div></div>
        <div class="pdf-entity">REPUBLICA DE ANGOLA - POLICIA NACIONAL</div>
        <div class="pdf-title">Auto de Detencao</div>
        <div class="pdf-subtitle">{{ $entidade }}</div>
        <div class="clear"></div>
    </div>

    <div class="section-title">Dados da Detencao</div>
    <div class="info-row"><span class="info-label">Numero:</span><span class="info-value font-bold">{{ $dt->numero_detencao }}</span></div>
    <div class="info-row"><span class="info-label">Data/Hora:</span><span class="info-value">{{ $dt->data_detencao?->format('d/m/Y H:i') }}</span></div>
    <div class="info-row"><span class="info-label">Local:</span><span class="info-value">{{ $dt->local_detencao }}</span></div>
    <div class="info-row"><span class="info-label">Agente:</span><span class="info-value">{{ $dt->agenteResponsavel?->nome }} ({{ $dt->agenteResponsavel?->nip }})</span></div>
    <div class="info-row"><span class="info-label">Unidade:</span><span class="info-value">{{ $dt->unidade?->nome }}</span></div>
    <div class="info-row"><span class="info-label">Ocorrencia:</span><span class="info-value">{{ $dt->ocorrencia?->numero_ocorrencia }}</span></div>

    <div class="section-title">Dados do Detido</div>
    <div class="info-row"><span class="info-label">Nome:</span><span class="info-value font-bold">{{ $dt->pessoa?->nome }}</span></div>
    <div class="info-row"><span class="info-label">BI:</span><span class="info-value">{{ $dt->pessoa?->bi ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Nascimento:</span><span class="info-value">{{ $dt->pessoa?->data_nascimento?->format('d/m/Y') ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Sexo:</span><span class="info-value">{{ $dt->pessoa?->sexo === 'M' ? 'Masculino' : ($dt->pessoa?->sexo === 'F' ? 'Feminino' : '-') }}</span></div>
    <div class="info-row"><span class="info-label">Morada:</span><span class="info-value">{{ $dt->pessoa?->morada ?? '-' }}</span></div>

    <div class="section-title">Motivo da Detencao</div>
    <p style="font-size:10px;line-height:1.7;">{{ $dt->motivo }}</p>

    @if($dt->observacoes)
    <div class="section-title">Observacoes</div>
    <p style="font-size:10px;">{{ $dt->observacoes }}</p>
    @endif

    <div style="margin-top:60px;">
        <table style="width:100%;"><tr>
            <td style="width:45%;text-align:center;padding-top:40px;border-top:1px solid #333;font-size:10px;">Agente Responsavel<br><span style="font-size:9px;color:#666;">{{ $dt->agenteResponsavel?->nome }}</span></td>
            <td style="width:10%;"></td>
            <td style="width:45%;text-align:center;padding-top:40px;border-top:1px solid #333;font-size:10px;">Chefe de Esquadra<br><span style="font-size:9px;color:#666;">Assinatura e carimbo</span></td>
        </tr></table>
    </div>

    <div class="pdf-footer">{{ $entidade }} - Auto de Detencao | {{ $data_geracao }}</div>
</div>
</body>
</html>