<div id="section-pessoas" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Pessoas</h1><p class="page-desc">Suspeitos, vítimas e testemunhas</p></div>
        <button class="btn-primary" onclick="formNovaPessoa()"><i class='bx bx-plus'></i> Nova Pessoa</button>
    </div>
    <div class="filters">
        <div class="search-filter"><i class='bx bx-search'></i><input type="text" id="f-pes-busca" placeholder="Buscar por nome, BI ou alcunha..."><button class="btn-ghost btn-sm" onclick="loadPessoas()" style="border:none;"><i class='bx bx-search'></i></button></div>
        <select id="f-pes-sexo" onchange="loadPessoas()"><option value="">Sexo</option><option value="M">Masculino</option><option value="F">Feminino</option></select>
        <select id="f-pes-nacionalidade" onchange="loadPessoas()"><option value="">Nacionalidade</option><option value="Angolana">Angolana</option><option value="Congolesa">Congolesa</option><option value="Portuguesa">Portuguesa</option><option value="Brasileira">Brasileira</option><option value="Outra">Outra</option></select>
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">Nome</div><div class="col c1">BI</div><div class="col c1">Sexo</div><div class="col c1">Telefone</div><div class="col c2">Morada</div><div class="col c1">Acções</div></div>
        <div id="list-pes"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-pes" class="pagination"></div>
</div>