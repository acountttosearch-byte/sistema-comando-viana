<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lista de Agentes</title>
    @include('pdf.partials.styles')
</head>
<body>
<div class="page">
    <div class="pdf-header">
        <div class="pdf-right"><div>Total: {{ $agentes->count() }} agentes</div><div>{{ $data_geracao }}</div></div>
        <div class="pdf-entity">REPUBLICA DE ANGOLA - POLICIA NACIONAL</div>
        <div class="pdf-title">Lista de Agentes</div>
        <div class="pdf-subtitle">{{ $entidade }} - Estado: {{ ucfirst($filtro_estado) }}</div>
        <div class="clear"></div>
    </div>

    <table class="pdf-table">
        <thead><tr><th>#</th><th>NIP</th><th>Nome</th><th>Patente</th><th>Cargo</th><th>Unidade</th><th>Telefone</th><th>Estado</th></tr></thead>
        <tbody>
            @foreach($agentes as $i => $ag)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="font-bold">{{ $ag->nip }}</td>
                <td>{{ $ag->nome }}</td>
                <td>{{ $ag->patente?->nome }}</td>
                <td>{{ $ag->cargo }}</td>
                <td>{{ $ag->unidade?->nome }}</td>
                <td>{{ $ag->telefone ?? '-' }}</td>
                <td><span class="pdf-badge {{ $ag->estado === 'activo' ? 'pdf-badge-green' : 'pdf-badge-gray' }}">{{ $ag->estado }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pdf-footer">{{ $entidade }} - SCGD | {{ $data_geracao }} | {{ $gerado_por }}</div>
</div>
</body>
</html>