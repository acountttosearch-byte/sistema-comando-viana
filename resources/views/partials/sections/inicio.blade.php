<div id="section-inicio" class="section active">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-desc">Resumo operacional do Comando Municipal de Viana</p>
        </div>
    </div>

    <div class="stats-grid" id="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class='bx bx-file'></i></div>
            <div><span class="stat-value" id="m-total-oc">—</span><span class="stat-label">Ocorrências</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class='bx bx-error-circle'></i></div>
            <div><span class="stat-value" id="m-abertas">—</span><span class="stat-label">Casos Abertos</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class='bx bx-check-circle'></i></div>
            <div><span class="stat-value" id="m-resolvidas">—</span><span class="stat-label">Resolvidos</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class='bx bx-lock-alt'></i></div>
            <div><span class="stat-value" id="m-detencoes">—</span><span class="stat-label">Detenções (mês)</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class='bx bx-search-alt-2'></i></div>
            <div><span class="stat-value" id="m-inv">—</span><span class="stat-label">Investigações</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class='bx bx-bell-ring'></i></div>
            <div><span class="stat-value" id="m-alertas">—</span><span class="stat-label">Alertas</span></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card"><div class="card-head"><h3>Crimes por Tipo</h3></div><div id="chart-tipo" class="card-body"><p class="text-muted">A carregar...</p></div></div>
        <div class="card"><div class="card-head"><h3>Crimes por Mês</h3></div><div id="chart-mes" class="card-body"><p class="text-muted">A carregar...</p></div></div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-head"><h3>Últimas Ocorrências</h3><a class="link-btn" onclick="showSection('ocorrencias')">Ver todas →</a></div>
        <div class="tbl">
            <div class="tbl-head"><div class="col c2">Número</div><div class="col c2">Tipo</div><div class="col c3">Local</div><div class="col c1">Prioridade</div><div class="col c1">Estado</div><div class="col c1">Data</div></div>
            <div id="dash-ultimas"><div class="tbl-empty">A carregar...</div></div>
        </div>
    </div>
</div>