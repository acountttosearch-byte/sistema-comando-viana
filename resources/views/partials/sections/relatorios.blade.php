<div id="section-relatorios" class="section">
    <div class="page-header"><div><h1 class="page-title">Relatórios</h1><p class="page-desc">Geração de relatórios estatísticos</p></div></div>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <div class="form-row">
                <div class="form-col"><label>Tipo</label><select id="rel-tipo"></select></div>
                <div class="form-col"><label>De</label><input type="date" id="rel-di"></div>
                <div class="form-col"><label>Até</label><input type="date" id="rel-df"></div>
                <div class="form-col"><label>Unidade</label><select id="rel-unidade"><option value="">Todas</option></select></div>
                <div class="form-col" style="align-self:end;display:flex;gap:8px;">
                    <button class="btn-primary" onclick="gerarRelatorio()"><i class='bx bx-bar-chart-alt-2'></i> Gerar</button>
                    <button class="btn-ghost" onclick="exportPdfRelatorio()"><i class='bx bx-download'></i> PDF</button>
                </div>
            </div>
        </div>
    </div>
    <div id="rel-resultado" style="display:none;">
        <div class="stats-grid" id="rel-stats"></div>
    </div>
    <div class="card"><div class="card-head"><h3>Relatórios Anteriores</h3></div>
        <div class="filters" style="padding:12px;">
            <div class="search-filter full"><i class='bx bx-search'></i><input type="text" id="f-rel-busca" placeholder="Buscar nos relatórios anteriores..."></div>
            <button class="btn-ghost" onclick="loadRelatoriosAnteriores()"><i class='bx bx-search'></i></button>
        </div>
        <div class="tbl"><div class="tbl-head"><div class="col c2">Tipo</div><div class="col c2">Período</div><div class="col c2">Unidade</div><div class="col c1">Data</div></div><div id="list-rel"><div class="tbl-empty">Sem relatórios.</div></div></div>
    </div>
</div>