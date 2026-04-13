<div id="section-processos" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Processos Criminais</h1><p class="page-desc">Gestão de processos para remessa ao Ministério Público</p></div>
        <button class="btn-primary" onclick="formNovoProcesso()"><i class='bx bx-plus'></i> Novo Processo</button>
    </div>
    <div class="filters">
        <div class="search-filter"><i class='bx bx-search'></i><input type="text" id="f-proc-busca" placeholder="Buscar por nº processo ou ocorrência..."></div>
        <button class="btn-primary btn-sm" onclick="loadProcessos()"><i class='bx bx-search'></i> Buscar</button>
        <select id="f-proc-estado" onchange="loadProcessos()"><option value="">Estado</option><option value="em_instrucao">Em Instrução</option><option value="concluido">Concluído</option><option value="remetido_mp">Remetido ao MP</option><option value="arquivado">Arquivado</option></select>
        @if(in_array(auth()->user()->perfil->nome, ['admin', 'comandante']))
        <select id="f-proc-unidade" onchange="loadProcessos()"><option value="">Unidade</option></select>
        @endif
        <input type="date" id="f-proc-di" onchange="loadProcessos()">
        <input type="date" id="f-proc-df" onchange="loadProcessos()">
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">Nº Processo</div><div class="col c2">Ocorrência</div><div class="col c2">Tipo Crime</div><div class="col c1">Data</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div>
        <div id="list-proc"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-proc" class="pagination"></div>
</div>
