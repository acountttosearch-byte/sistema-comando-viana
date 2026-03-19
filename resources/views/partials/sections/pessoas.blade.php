<div id="section-pessoas" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Pessoas</h1><p class="page-desc">Suspeitos, vitimas e testemunhas</p></div>
        <button class="btn-primary" onclick="formNovaPessoa()"><i class='bx bx-plus'></i> Nova Pessoa</button>
    </div>
    <div class="filters"><div class="search-filter full"><i class='bx bx-search'></i><input type="text" id="f-pes-busca" placeholder="Buscar por nome, BI ou alcunha..."></div><button class="btn-ghost" onclick="loadPessoas()"><i class='bx bx-search'></i></button></div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">Nome</div><div class="col c1">BI</div><div class="col c1">Sexo</div><div class="col c1">Telefone</div><div class="col c2">Morada</div><div class="col c1">Accoes</div></div>
        <div id="list-pes"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-pes" class="pagination"></div>
</div>