<div id="section-viaturas" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Viaturas</h1><p class="page-desc">Frota policial</p></div>
        <button class="btn-primary" onclick="formNovaViatura()"><i class='bx bx-plus'></i> Nova Viatura</button>
    </div>
    <div class="filters">
        <div class="search-filter"><i class='bx bx-search'></i><input type="text" id="f-viat-busca" placeholder="Buscar por matrícula, marca ou modelo..."><button class="btn-ghost btn-sm" onclick="loadViaturas()" style="border:none;"><i class='bx bx-search'></i></button></div>
        <select id="f-viat-estado" onchange="loadViaturas()"><option value="">Estado</option><option value="disponivel">Disponível</option><option value="em_uso">Em Uso</option><option value="manutencao">Manutenção</option><option value="abatida">Abatida</option></select>
        @if(in_array(auth()->user()->perfil->nome, ['admin', 'comandante']))
        <select id="f-viat-unidade" onchange="loadViaturas()"><option value="">Unidade</option></select>
        @endif
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c1">Matrícula</div><div class="col c2">Marca / Modelo</div><div class="col c2">Unidade</div><div class="col c1">Km</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div>
        <div id="list-viat"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-viat" class="pagination"></div>
</div>