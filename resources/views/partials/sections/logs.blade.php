<div id="section-logs" class="section">
    <div class="page-header"><div><h1 class="page-title">Logs do Sistema</h1><p class="page-desc">Registo de actividades</p></div></div>
    <div class="filters">
        <select id="f-log-acao" onchange="loadLogs()"><option value="">Acção</option><option value="login">Login</option><option value="logout">Logout</option><option value="criar">Criar</option><option value="editar">Editar</option><option value="apagar">Apagar</option></select>
        <select id="f-log-tabela" onchange="loadLogs()"><option value="">Tabela</option><option value="ocorrencias">Ocorrências</option><option value="detencoes">Detenções</option><option value="agentes">Agentes</option><option value="evidencias">Evidências</option></select>
        <input type="date" id="f-log-di" onchange="loadLogs()">
        <input type="date" id="f-log-df" onchange="loadLogs()">
    </div>
    <div class="timeline" id="list-logs"><div class="tbl-empty">A carregar...</div></div>
    <div id="pag-logs" class="pagination"></div>
</div>