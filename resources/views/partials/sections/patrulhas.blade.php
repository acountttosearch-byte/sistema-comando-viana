<div id="section-patrulhas" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Patrulhas</h1><p class="page-desc">Planeamento e monitorização</p></div>
        <button class="btn-primary" onclick="modalNovaPatrulha()"><i class='bx bx-plus'></i> Nova Patrulha</button>
    </div>
    <div class="filters">
        <input type="date" id="f-pat-data" value="{{ date('Y-m-d') }}" onchange="loadPatrulhas()">
        <select id="f-pat-estado" onchange="loadPatrulhas()"><option value="">Todos</option><option value="planeada">Planeadas</option><option value="em_curso">Em Curso</option><option value="concluida">Concluídas</option></select>
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c1">Data</div><div class="col c1">Turno</div><div class="col c2">Zona</div><div class="col c2">Líder</div><div class="col c1">Viatura</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div>
        <div id="list-pat"><div class="tbl-empty">Sem dados.</div></div>
    </div>
</div>