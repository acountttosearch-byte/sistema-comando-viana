<div id="section-identidade" class="section">
    <div class="page-header"><div><h1 class="page-title">Gestão de Identidade</h1><p class="page-desc">Agentes e unidades policiais</p></div></div>
    <div class="tabs-bar">
        <button class="tab active" onclick="openIdTab('ag-act',event)">Agentes Activos</button>
        <button class="tab" onclick="openIdTab('ag-ina',event)">Inactivos</button>
        <button class="tab" onclick="openIdTab('ag-new',event)">Novo Agente</button>
        <button class="tab" onclick="openIdTab('unidades',event)">Unidades</button>
    </div>

    <div id="idtab-ag-act" class="idtab active">
        <div class="tbl"><div class="tbl-head"><div class="col c2">Nome</div><div class="col c1">NIP</div><div class="col c2">Perfil</div><div class="col c2">Unidade</div><div class="col c1">Patente</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div><div id="list-ag-act"></div></div>
        <div id="pag-ag-act" class="pagination"></div>
    </div>

    <div id="idtab-ag-ina" class="idtab">
        <div class="tbl"><div class="tbl-head"><div class="col c2">Nome</div><div class="col c1">NIP</div><div class="col c2">Perfil</div><div class="col c2">Unidade</div><div class="col c1">Patente</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div><div id="list-ag-ina"></div></div>
        <div id="pag-ag-ina" class="pagination"></div>
    </div>

    <div id="idtab-ag-new" class="idtab">
        <form class="form-card" id="form-agente" onsubmit="return criarAgente(event)">
            <h3>Registar Novo Agente</h3>
            <div class="form-section">Dados Pessoais</div>
            <div class="form-row"><div class="form-col"><label>Nome</label><input type="text" id="ag-nome" required></div><div class="form-col"><label>NIP</label><input type="text" id="ag-nip" placeholder="NIP-01025" required readonly></div></div>
            <div class="form-row"><div class="form-col"><label>BI</label><input type="text" id="ag-bi" placeholder="0012345678LA042" required></div><div class="form-col"><label>Email</label><input type="email" id="ag-email" placeholder="nome@policia-viana.ao" required></div></div>
            <div class="form-row"><div class="form-col"><label>Telefone</label><input type="text" id="ag-tel" placeholder="+244 923 000 000" required></div><div class="form-col"><label>Sexo</label><select id="ag-sexo"><option value="">—</option><option value="M">Masculino</option><option value="F">Feminino</option></select></div></div>
            <div class="form-section">Localização e Perfil</div>
            <div class="form-row"><div class="form-col"><label>Unidade</label><select id="ag-unidade" required></select></div><div class="form-col"><label>Perfil</label><select id="ag-perfil" required></select></div></div>
            <div class="form-row"><div class="form-col"><label>Patente</label><select id="ag-patente" required></select></div></div>
            <div class="form-section">Estado</div>
            <div class="form-row"><div class="form-col"><label>Estado</label><select id="ag-estado"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div></div>
            <button type="submit" class="btn-primary" style="margin-top:20px;"><i class='bx bx-save'></i> Registar Agente</button>
        </form>
    </div>

    <div id="idtab-unidades" class="idtab">
        <div class="tbl"><div class="tbl-head"><div class="col c2">Nome</div><div class="col c2">Tipo</div><div class="col c2">Endereço</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div><div id="list-unidades"></div></div>
    </div>
</div>
