<div id="section-armamento" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Armamento</h1><p class="page-desc">Controlo de armas</p></div>
        <button class="btn-primary" onclick="formNovoArmamento()"><i class='bx bx-plus'></i> Novo</button>
    </div>
    <div class="filters">
        <div class="search-filter"><i class='bx bx-search'></i><input type="text" id="f-arm-busca" placeholder="Buscar por nº série, marca, modelo ou calibre..."><button class="btn-ghost btn-sm" onclick="loadArmamento()" style="border:none;"><i class='bx bx-search'></i></button></div>
        <select id="f-arm-tipo" onchange="loadArmamento()"><option value="">Tipo</option></select>
        <select id="f-arm-estado" onchange="loadArmamento()"><option value="">Estado</option><option value="disponivel">Disponível</option><option value="atribuido">Atribuído</option><option value="manutencao">Manutenção</option><option value="abatido">Abatido</option></select>
        @if(in_array(auth()->user()->perfil->nome, ['admin', 'comandante']))
        <select id="f-arm-unidade" onchange="loadArmamento()"><option value="">Unidade</option></select>
        @endif
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c1">Nº Série</div><div class="col c1">Tipo</div><div class="col c1">Marca</div><div class="col c1">Calibre</div><div class="col c2">Unidade</div><div class="col c2">Atribuído a</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div>
        <div id="list-arm"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-arm" class="pagination"></div>
</div>