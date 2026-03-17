/* ═══════════════════════════════════════════════════
   SCGD VIANA — JAVASCRIPT PRINCIPAL
   ═══════════════════════════════════════════════════ */

let aux = {};
let confirmCb = null;

// ══════════════════
// INIT
// ══════════════════
document.addEventListener('DOMContentLoaded', () => {
    loadAux();
    loadDashboard();
    initUserMenu();
    initSearch();
    initKeys();
    checkNotifs();
});

// ══════════════════
// API
// ══════════════════
async function api(url, opt = {}) {
    const h = { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': APP.csrf, 'X-Requested-With':'XMLHttpRequest' };
    if (opt.body instanceof FormData) delete h['Content-Type'];
    const cfg = { credentials:'same-origin', headers: h, ...opt };
    if (opt.headers) cfg.headers = { ...h, ...opt.headers };

    try {
        const r = await fetch('/api' + url, cfg);
        if (r.status === 401) { window.location.href = '/login'; return null; }
        if (r.status === 403) { toast('Sem permissão.', 'err'); return null; }
        if (r.status === 422) {
            const e = await r.json();
            (e.errors ? Object.values(e.errors).flat() : [e.message || 'Dados inválidos']).forEach(m => toast(m, 'err'));
            return null;
        }
        const d = await r.json();
        if (!r.ok) { toast(d.message || 'Erro.', 'err'); return null; }
        return d;
    } catch (e) { toast('Erro de conexão.', 'err'); console.error(e); return null; }
}

async function apiForm(url, fd) {
    try {
        const r = await fetch('/api' + url, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': APP.csrf, 'X-Requested-With':'XMLHttpRequest' },
            body: fd,
        });
        if (r.status === 422) { const e = await r.json(); (e.errors ? Object.values(e.errors).flat() : ['Erro']).forEach(m => toast(m,'err')); return null; }
        const d = await r.json();
        if (!r.ok) { toast(d.message || 'Erro.', 'err'); return null; }
        return d;
    } catch(e) { toast('Erro de conexão.', 'err'); return null; }
}

// ══════════════════
// NAVEGAÇÃO
// ══════════════════
function showSection(id) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    const el = document.getElementById('section-' + id);
    if (el) el.classList.add('active');

    document.querySelectorAll('.nav-item').forEach(n => {
        n.classList.toggle('active', n.dataset.section === id);
    });

    const loaders = {
        inicio: loadDashboard, ocorrencias: loadOcorrencias, pessoas: loadPessoas,
        detencoes: loadDetencoes, evidencias: () => loadEvidencias(1,'todos'),
        investigacoes: loadInvestigacoes, despachos: loadDespachos, patrulhas: loadPatrulhas,
        alertas: () => loadAlertas('activo'), viaturas: loadViaturas, armamento: loadArmamento,
        mensagens: () => loadMensagens('inbox'), relatorios: loadRelatorios,
        identidade: loadIdentidade, logs: loadLogs, configuracoes: loadConfig,
    };
    if (loaders[id]) loaders[id]();

    // fechar menus
    document.getElementById('user-menu')?.classList.remove('open');
}

// ══════════════════
// DADOS AUXILIARES
// ══════════════════
async function loadAux() {
    const d = await api('/dados-auxiliares');
    if (!d) return;
    aux = d;
    fillSel('f-oc-estado', aux.estados_ocorrencia, 'id', 'nome', 'Estado');
    fillSel('f-oc-tipo', aux.tipos_crime, 'id', 'nome', 'Tipo');
    fillSel('f-det-estado', aux.estados_detencao, 'id', 'nome', 'Estado');
    fillSel('f-inv-estado', aux.estados_investigacao, 'id', 'nome', 'Estado');
    fillSel('ag-unidade', aux.unidades, 'id', 'nome', 'Selecionar');
    fillSel('ag-patente', aux.patentes, 'id', 'nome', 'Selecionar');
    fillSel('ag-perfil', aux.perfis, 'id', 'descricao', 'Selecionar');
    fillSel('rel-tipo', aux.tipos_relatorio, 'id', 'nome', 'Selecionar');
    fillSel('rel-unidade', aux.unidades, 'id', 'nome', 'Todas');
}

function fillSel(id, items, vk, tk, ph) {
    const el = document.getElementById(id);
    if (!el || !items) return;
    el.innerHTML = `<option value="">${ph}</option>` + items.map(i => `<option value="${i[vk]}">${i[tk]}</option>`).join('');
}

function fillSelEl(id, items, vk, tk, ph) { fillSel(id, items, vk, tk, ph); }

function mkSel(id, items, vk, tk, ph = 'Selecionar', req = true) {
    if (!items) return `<select id="${id}"><option value="">—</option></select>`;
    return `<select id="${id}" ${req?'required':''}><option value="">${ph}</option>${items.map(i=>`<option value="${i[vk]}">${i[tk]}</option>`).join('')}</select>`;
}

function mkOpts(id, opts, req = true) {
    return `<select id="${id}" ${req?'required':''}><option value="">Selecionar</option>${opts.map(o=>`<option value="${o.v}">${o.t}</option>`).join('')}</select>`;
}

// ══════════════════
// DASHBOARD
// ══════════════════
async function loadDashboard() {
    const d = await api('/dashboard/metricas');
    if (!d) return;

    txt('m-total-oc', d.total_ocorrencias);
    txt('m-abertas', d.ocorrencias_abertas);
    txt('m-resolvidas', d.ocorrencias_resolvidas);
    txt('m-detencoes', d.detencoes_mes);
    txt('m-inv', d.investigacoes_activas);
    txt('m-alertas', d.alertas_activos);

    renderBar('chart-tipo', d.crimes_por_tipo, 'tipo_nome', 'total');
    renderBar('chart-mes', d.crimes_por_mes, i => {
        const ms = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        return ms[i.mes] || i.mes;
    }, 'total');

    renderDashUltimas(d.ultimas_ocorrencias || []);

    const dot = document.getElementById('notif-dot');
    if (dot) dot.style.display = d.alertas_activos > 0 ? 'block' : 'none';
}

function renderBar(cid, items, labelFn, vk) {
    const c = document.getElementById(cid);
    if (!c) return;
    if (!items || !items.length) { c.innerHTML = '<p class="text-muted">Sem dados</p>'; return; }
    const max = Math.max(...items.map(i => i[vk]));
    c.innerHTML = '<div class="bar-chart">' + items.map(i => {
        const lbl = typeof labelFn === 'function' ? labelFn(i) : (i[labelFn] || '—');
        const pct = max > 0 ? (i[vk] / max * 100) : 0;
        return `<div class="bar-row"><div class="bar-label" title="${lbl}">${lbl}</div><div class="bar-bg"><div class="bar-fg" style="width:${pct}%"><span class="bar-val">${i[vk]}</span></div></div></div>`;
    }).join('') + '</div>';
}

function renderDashUltimas(items) {
    const c = document.getElementById('dash-ultimas');
    if (!c) return;
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem ocorrências.</div>'; return; }
    c.innerHTML = items.map(o => `
        <div class="tbl-row" onclick="viewOcorrencia(${o.id})">
            <div class="col c2"><strong>${o.numero_ocorrencia}</strong></div>
            <div class="col c2">${o.tipo_crime?.nome || '—'}</div>
            <div class="col c3">${o.local || '—'}</div>
            <div class="col c1">${bPrio(o.prioridade)}</div>
            <div class="col c1">${bEstado(o.estado)}</div>
            <div class="col c1">${fDate(o.data_ocorrencia)}</div>
        </div>`).join('');
}

// ══════════════════
// OCORRÊNCIAS
// ══════════════════
async function loadOcorrencias(page = 1) {
    const p = new URLSearchParams({ page, estado_id: v('f-oc-estado'), prioridade: v('f-oc-prioridade'), tipo_crime_id: v('f-oc-tipo'), data_inicio: v('f-oc-di'), data_fim: v('f-oc-df'), busca: v('f-oc-busca') });
    const d = await api('/ocorrencias?' + p);
    if (!d) return;
    const items = d.data || [];
    const c = document.getElementById('list-oc');
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem ocorrências.</div>'; return; }
    c.innerHTML = items.map(o => `
        <div class="tbl-row prio-${o.prioridade}" onclick="viewOcorrencia(${o.id})">
            <div class="col c2"><strong>${o.numero_ocorrencia}</strong></div>
            <div class="col c2">${o.tipo_crime?.nome||'—'}</div>
            <div class="col c2">${o.local||'—'}</div>
            <div class="col c1">${fDate(o.data_ocorrencia)}</div>
            <div class="col c1">${bPrio(o.prioridade)}</div>
            <div class="col c1">${bEstado(o.estado)}</div>
            <div class="col c1"><button class="btn-icon" onclick="event.stopPropagation();viewOcorrencia(${o.id})"><i class='bx bx-show'></i></button></div>
        </div>`).join('');
    renderPag('pag-oc', d, loadOcorrencias);
}

async function viewOcorrencia(id) {
    showLoad();
    const o = await api('/ocorrencias/' + id);
    hideLoad();
    if (!o) return;

    let h = `<div class="detail-sect"><h4>Informações</h4>
        ${dl('Número', o.numero_ocorrencia)}${dl('Tipo', o.tipo_crime?.nome)}${dl('Categoria', o.tipo_crime?.categoria?.nome)}
        ${dl('Data/Hora', fDate(o.data_ocorrencia)+' '+(o.hora_ocorrencia||''))}${dl('Local', o.local)}${dl('Bairro', o.bairro)}
        ${dl('Prioridade', bPrio(o.prioridade))}${dl('Estado', bEstado(o.estado))}${dl('Unidade', o.unidade?.nome)}
        ${dl('Registado por', o.agente_registo?.nome)}${dl('Responsável', o.agente_responsavel?.nome||'<em style="color:var(--text-4)">Não atribuído</em>')}
    </div><div class="detail-sect"><h4>Descrição</h4><p style="font-size:13px;white-space:pre-wrap;line-height:1.6;">${o.descricao}</p></div>`;

    if (o.envolvimentos?.length) {
        h += '<div class="detail-sect"><h4>Pessoas Envolvidas ('+o.envolvimentos.length+')</h4>';
        o.envolvimentos.forEach(e => {
            const bc = e.tipo_envolvimento?.id===1?'red':e.tipo_envolvimento?.id===2?'orange':'blue';
            h += `<div class="detail-line"><span class="dl"><span class="badge badge-${bc}">${e.tipo_envolvimento?.nome}</span> ${e.pessoa?.nome||'—'}</span><span class="dv">${e.pessoa?.bi||'—'}</span></div>`;
        });
        h += `<button class="link-btn" style="margin-top:8px;" onclick="closeDetail();modalAddEnvolvido(${o.id})">+ Adicionar pessoa</button></div>`;
    } else {
        h += `<div class="detail-sect"><h4>Pessoas Envolvidas</h4><p class="text-muted">Nenhuma.</p><button class="link-btn" onclick="closeDetail();modalAddEnvolvido(${o.id})">+ Adicionar</button></div>`;
    }

    if (o.evidencias?.length) {
        h += '<div class="detail-sect"><h4>Evidências ('+o.evidencias.length+')</h4>';
        o.evidencias.forEach(e => h += dl(e.codigo+' — '+e.descricao, `<span class="badge badge-teal">${e.estado}</span>`));
        h += '</div>';
    }

    if (o.detencoes?.length) {
        h += '<div class="detail-sect"><h4>Detenções ('+o.detencoes.length+')</h4>';
        o.detencoes.forEach(d => h += dl(d.numero_detencao+' — '+(d.pessoa?.nome||'—'), d.estado?.nome));
        h += '</div>';
    }

    if (o.investigacoes?.length) {
        h += '<div class="detail-sect"><h4>Investigações</h4>';
        o.investigacoes.forEach(i => h += dl(i.numero_investigacao+' — '+(i.investigador?.nome||'—'), i.progresso+'% '+bEstadoObj(i.estado)));
        h += '</div>';
    }

    openDetail('Ocorrência ' + o.numero_ocorrencia, h);
}

function modalNovaOcorrencia() {
    const h = `<form onsubmit="return false;">
        <div class="form-row">${fg('Tipo de Crime *', mkSel('m-oc-tipo', aux.tipos_crime, 'id', 'nome'))}${fg('Prioridade *', mkOpts('m-oc-prio', [{v:'baixa',t:'Baixa'},{v:'media',t:'Média'},{v:'alta',t:'Alta'},{v:'critica',t:'Crítica'}]))}</div>
        <div class="form-row">${fg('Data *', `<input type="date" id="m-oc-data" value="${today()}" required>`)}${fg('Hora', '<input type="time" id="m-oc-hora">')}</div>
        <div class="form-row">${fg('Local *', '<input type="text" id="m-oc-local" required>')}${fg('Bairro', mkSel('m-oc-bairro', aux.bairros, 'id', 'nome', 'Selecionar', false))}</div>
        <div class="form-row">${fg('Unidade *', mkSel('m-oc-unidade', aux.unidades, 'id', 'nome'))}${fg('Agente Responsável', '<select id="m-oc-agente"><option value="">Definir depois</option></select>')}</div>
        ${fg('Descrição *', '<textarea id="m-oc-desc" rows="4" required placeholder="Descreva a ocorrência..."></textarea>')}
    </form>`;

    openModal('Nova Ocorrência', h, `<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitOcorrencia()"><i class='bx bx-save'></i> Registar</button>`);

    document.getElementById('m-oc-unidade')?.addEventListener('change', async function() {
        if (this.value) { const ag = await api('/agentes?estado=activo&unidade_id='+this.value); if(ag) fillSelEl('m-oc-agente', ag, 'id', 'nome', 'Definir depois'); }
    });
}

async function submitOcorrencia() {
    showLoad();
    const bairroSel = document.getElementById('m-oc-bairro');
    const bairroNome = bairroSel?.options[bairroSel.selectedIndex]?.text;
    const d = await api('/ocorrencias', { method:'POST', body: JSON.stringify({
        tipo_crime_id: v('m-oc-tipo'), prioridade: v('m-oc-prio'), data_ocorrencia: v('m-oc-data'),
        hora_ocorrencia: v('m-oc-hora')||null, local: v('m-oc-local'),
        bairro: bairroNome!=='Selecionar'?bairroNome:null, bairro_id: v('m-oc-bairro')||null,
        unidade_id: v('m-oc-unidade'), agente_responsavel_id: v('m-oc-agente')||null,
        descricao: v('m-oc-desc'),
    })});
    hideLoad();
    if (d?.success) { toast('Ocorrência registada: '+d.ocorrencia.numero_ocorrencia, 'ok'); closeModal(); loadOcorrencias(); }
}

// ══════════════════
// PESSOAS
// ══════════════════
async function loadPessoas(page = 1) {
    const d = await api('/pessoas?page='+page+'&busca='+v('f-pes-busca'));
    if (!d) return;
    const items = d.data || [];
    const c = document.getElementById('list-pes');
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; return; }
    c.innerHTML = items.map(p => `
        <div class="tbl-row" onclick="viewPessoa(${p.id})">
            <div class="col c2"><strong>${p.nome}</strong>${p.alcunha?` <small style="color:var(--text-4)">(${p.alcunha})</small>`:''}</div>
            <div class="col c1">${p.bi||'—'}</div><div class="col c1">${p.sexo||'—'}</div>
            <div class="col c1">${p.telefone||'—'}</div><div class="col c2">${p.morada||'—'}</div>
            <div class="col c1"><button class="btn-icon"><i class='bx bx-show'></i></button></div>
        </div>`).join('');
    renderPag('pag-pes', d, loadPessoas);
}

async function viewPessoa(id) {
    showLoad(); const p = await api('/pessoas/'+id); hideLoad(); if(!p) return;
    let h = `<div class="detail-sect"><h4>Dados Pessoais</h4>${dl('Nome',p.nome)}${p.alcunha?dl('Alcunha',p.alcunha):''}${dl('BI',p.bi)}${dl('Sexo',p.sexo==='M'?'Masculino':p.sexo==='F'?'Feminino':'—')}${dl('Nascimento',fDate(p.data_nascimento))}${dl('Telefone',p.telefone)}${dl('Morada',p.morada)}</div>`;
    if(p.envolvimentos?.length) { h+='<div class="detail-sect"><h4>Ocorrências ('+p.envolvimentos.length+')</h4>'; p.envolvimentos.forEach(e=>h+=dl((e.ocorrencia?.numero_ocorrencia||'—')+' — '+e.tipo_envolvimento?.nome, e.ocorrencia?.tipo_crime?.nome)); h+='</div>'; }
    if(p.detencoes?.length) { h+='<div class="detail-sect"><h4>Detenções ('+p.detencoes.length+')</h4>'; p.detencoes.forEach(d=>h+=dl(d.numero_detencao||'—', d.estado?.nome)); h+='</div>'; }
    openDetail('Perfil: '+p.nome, h);
}

function modalNovaPessoa(retOcId = null) {
    const h = `<form onsubmit="return false;">
        <div class="form-row">${fg('Nome *','<input type="text" id="m-pes-nome" required>')}${fg('Alcunha','<input type="text" id="m-pes-alcunha">')}</div>
        <div class="form-row">${fg('BI','<input type="text" id="m-pes-bi">')}${fg('Sexo',mkOpts('m-pes-sexo',[{v:'M',t:'Masculino'},{v:'F',t:'Feminino'}],false))}</div>
        <div class="form-row">${fg('Nascimento','<input type="date" id="m-pes-nasc">')}${fg('Nacionalidade','<input type="text" id="m-pes-nac" value="Angolana">')}</div>
        <div class="form-row">${fg('Telefone','<input type="text" id="m-pes-tel">')}${fg('Bairro','<input type="text" id="m-pes-bairro">')}</div>
        ${fg('Morada','<input type="text" id="m-pes-morada">')}
        ${fg('Características','<textarea id="m-pes-car" rows="2" placeholder="Altura, marcas..."></textarea>')}
    </form>`;
    openModal('Nova Pessoa', h, `<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitPessoa(${retOcId||'null'})"><i class='bx bx-save'></i> Registar</button>`);
}

async function submitPessoa(retOcId) {
    showLoad();
    const d = await api('/pessoas',{method:'POST',body:JSON.stringify({nome:v('m-pes-nome'),alcunha:v('m-pes-alcunha'),bi:v('m-pes-bi'),sexo:v('m-pes-sexo'),data_nascimento:v('m-pes-nasc'),nacionalidade:v('m-pes-nac'),telefone:v('m-pes-tel'),bairro:v('m-pes-bairro'),morada:v('m-pes-morada'),caracteristicas_fisicas:v('m-pes-car')})});
    hideLoad();
    if(d?.success){toast('Pessoa registada.','ok');closeModal();if(retOcId)modalAddEnvolvido(retOcId);else loadPessoas();}
}

function modalAddEnvolvido(ocId) {
    const h = `<form onsubmit="return false;">
        <p class="text-muted" style="margin-bottom:14px;">Associe uma pessoa à ocorrência.</p>
        <div class="form-row">${fg('Pessoa *',`<div style="display:flex;gap:8px;"><select id="m-env-pes" style="flex:1;" required></select><button type="button" class="btn-ghost btn-sm" onclick="modalNovaPessoa(${ocId})">+ Nova</button></div>`)}${fg('Tipo *',mkSel('m-env-tipo',[{id:1,nome:'Suspeito'},{id:2,nome:'Vítima'},{id:3,nome:'Testemunha'}],'id','nome'))}</div>
        ${fg('Observações','<textarea id="m-env-obs" rows="2"></textarea>')}
    </form>`;
    openModal('Adicionar Pessoa', h, `<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitEnvolvido(${ocId})"><i class='bx bx-plus'></i> Adicionar</button>`);
    loadSelPessoas('m-env-pes');
}

async function loadSelPessoas(id) { const d=await api('/pessoas?per_page=500');if(d)fillSelEl(id,d.data||d,'id','nome','Selecionar pessoa'); }
async function loadSelOcorrencias(id) { const d=await api('/ocorrencias?per_page=200');if(d)fillSelEl(id,d.data||[],'id','numero_ocorrencia','Selecionar'); }
async function loadSelAgentes(id,extra='') { const d=await api('/agentes?estado=activo'+extra);if(d)fillSelEl(id,d,'id','nome','Selecionar agente'); }

async function submitEnvolvido(ocId) {
    showLoad();const d=await api('/ocorrencias/'+ocId+'/envolvidos',{method:'POST',body:JSON.stringify({pessoa_id:v('m-env-pes'),tipo_envolvimento_id:v('m-env-tipo'),descricao:v('m-env-obs')})});hideLoad();
    if(d?.success){toast('Pessoa adicionada.','ok');closeModal();viewOcorrencia(ocId);}
}

// ══════════════════
// DETENÇÕES
// ══════════════════
async function loadDetencoes(page=1) {
    const p=new URLSearchParams({page,estado_id:v('f-det-estado'),data_inicio:v('f-det-di'),data_fim:v('f-det-df')});
    const d=await api('/detencoes?'+p); if(!d)return;
    const items=d.data||[];const c=document.getElementById('list-det');
    if(!items.length){c.innerHTML='<div class="tbl-empty">Sem dados.</div>';return;}
    c.innerHTML=items.map(dt=>`<div class="tbl-row"><div class="col c2"><strong>${dt.numero_detencao}</strong></div><div class="col c2">${dt.pessoa?.nome||'—'}</div><div class="col c2">${dt.ocorrencia?.numero_ocorrencia||'—'}</div><div class="col c1">${fDT(dt.data_detencao)}</div><div class="col c1">${bGen(dt.estado?.nome)}</div><div class="col c1"><button class="btn-icon"><i class='bx bx-show'></i></button></div></div>`).join('');
    renderPag('pag-det',d,loadDetencoes);
}

function modalNovaDetencao() {
    const h=`<form onsubmit="return false;">
        <div class="form-row">${fg('Pessoa *','<div style="display:flex;gap:8px;"><select id="m-det-pes" style="flex:1;" required></select><button type="button" class="btn-ghost btn-sm" onclick="modalNovaPessoa()">+ Nova</button></div>')}${fg('Ocorrência *','<select id="m-det-oc" required></select>')}</div>
        <div class="form-row">${fg('Data/Hora *',`<input type="datetime-local" id="m-det-data" value="${nowLocal()}" required>`)}${fg('Local *','<input type="text" id="m-det-local" required>')}</div>
        ${fg('Motivo *','<textarea id="m-det-motivo" rows="3" required></textarea>')}
        ${fg('Observações','<textarea id="m-det-obs" rows="2"></textarea>')}
    </form>`;
    openModal('Nova Detenção',h,`<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-danger" onclick="submitDetencao()"><i class='bx bx-lock-alt'></i> Registar</button>`);
    loadSelPessoas('m-det-pes');loadSelOcorrencias('m-det-oc');
}

async function submitDetencao() {
    showLoad();const d=await api('/detencoes',{method:'POST',body:JSON.stringify({pessoa_id:v('m-det-pes'),ocorrencia_id:v('m-det-oc'),data_detencao:v('m-det-data'),local_detencao:v('m-det-local'),motivo:v('m-det-motivo'),observacoes:v('m-det-obs')})});hideLoad();
    if(d?.success){toast('Detenção registada: '+d.detencao.numero_detencao,'ok');closeModal();loadDetencoes();}
}

// ══════════════════
// EVIDÊNCIAS
// ══════════════════
async function loadEvidencias(page=1,tipo='todos') {
    const p=new URLSearchParams({page});if(tipo&&tipo!=='todos')p.append('tipo_evidencia_id',tipo);
    const d=await api('/evidencias?'+p);if(!d)return;
    const items=d.data||[];const c=document.getElementById('list-ev');
    const icos={1:'bx-image',2:'bx-video',3:'bx-file',4:'bx-microphone',5:'bx-box'};
    const cols={1:'blue',2:'purple',3:'orange',4:'green',5:'red'};
    if(!items.length){c.innerHTML='<div class="tbl-empty" style="grid-column:1/-1;">Sem evidências.</div>';return;}
    c.innerHTML=items.map(e=>`<div class="ev-card"><div class="ev-icon" style="color:var(--${cols[e.tipo_evidencia_id]||'text-4'})"><i class='bx ${icos[e.tipo_evidencia_id]||'bx-file'}'></i></div><div class="ev-name">${e.descricao}</div><div class="ev-meta">${e.codigo} • ${e.tipo_evidencia?.nome||''}</div><div class="ev-meta" style="margin-top:4px;">${bGen(e.estado)}</div></div>`).join('');
}
function filtEv(tipo,ev){if(ev){ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));ev.target.classList.add('active');}loadEvidencias(1,tipo);}

function modalNovaEvidencia(ocId=null) {
    const h=`<form onsubmit="return false;" enctype="multipart/form-data">
        <div class="form-row">${fg('Ocorrência *',ocId?`<input type="text" id="m-ev-oc" value="${ocId}" readonly>`:'<select id="m-ev-oc" required></select>')}${fg('Tipo *',mkSel('m-ev-tipo',aux.tipos_evidencia,'id','nome'))}</div>
        ${fg('Descrição *','<input type="text" id="m-ev-desc" required>')}
        ${fg('Ficheiro','<input type="file" id="m-ev-file">')}
        ${fg('Localização Física','<input type="text" id="m-ev-loc" placeholder="Cofre A, Prateleira 1">')}
    </form>`;
    openModal('Nova Evidência',h,`<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitEvidencia(${ocId||'null'})"><i class='bx bx-save'></i> Registar</button>`);
    if(!ocId) loadSelOcorrencias('m-ev-oc');
}

async function submitEvidencia(retOc) {
    showLoad();const fd=new FormData();fd.append('ocorrencia_id',v('m-ev-oc'));fd.append('tipo_evidencia_id',v('m-ev-tipo'));fd.append('descricao',v('m-ev-desc'));fd.append('localizacao_fisica',v('m-ev-loc'));
    const f=document.getElementById('m-ev-file')?.files[0];if(f)fd.append('ficheiro',f);
    const d=await apiForm('/evidencias',fd);hideLoad();
    if(d?.success){toast('Evidência registada: '+d.evidencia.codigo,'ok');closeModal();if(retOc)viewOcorrencia(retOc);else loadEvidencias(1,'todos');}
}

// ══════════════════
// INVESTIGAÇÕES
// ══════════════════
async function loadInvestigacoes() {
    const d=await api('/investigacoes?estado_id='+v('f-inv-estado'));if(!d)return;
    const items=d.data||[];const c=document.getElementById('list-inv');
    if(!items.length){c.innerHTML='<div class="tbl-empty">Sem dados.</div>';return;}
    c.innerHTML=items.map(i=>`<div class="tbl-row"><div class="col c2"><strong>${i.numero_investigacao}</strong></div><div class="col c1">${i.ocorrencia?.numero_ocorrencia||'—'}</div><div class="col c2">${i.investigador?.nome||'—'}</div><div class="col c2"><span style="font-size:11px;">${i.progresso}%</span><div class="progress-track"><div class="progress-fill" style="width:${i.progresso}%"></div></div></div><div class="col c1">${bEstadoObj(i.estado)}</div><div class="col c1"><button class="btn-icon"><i class='bx bx-show'></i></button></div></div>`).join('');
}

function modalNovaInvestigacao() {
    openModal('Nova Investigação',`<form onsubmit="return false;"><div class="form-row">${fg('Ocorrência *','<select id="m-inv-oc" required></select>')}${fg('Investigador *','<select id="m-inv-ag" required></select>')}</div>${fg('Prazo','<input type="date" id="m-inv-prazo">')}${fg('Resumo','<textarea id="m-inv-res" rows="3"></textarea>')}</form>`,
    `<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitInvestigacao()"><i class='bx bx-search-alt-2'></i> Abrir</button>`);
    loadSelOcorrencias('m-inv-oc');loadSelAgentes('m-inv-ag');
}
async function submitInvestigacao() {
    showLoad();const d=await api('/investigacoes',{method:'POST',body:JSON.stringify({ocorrencia_id:v('m-inv-oc'),investigador_id:v('m-inv-ag'),prazo:v('m-inv-prazo')||null,resumo:v('m-inv-res')})});hideLoad();
    if(d?.success){toast('Investigação aberta.','ok');closeModal();loadInvestigacoes();}
}

// ══════════════════
// DESPACHOS
// ══════════════════
async function loadDespachos() {
    const d=await api('/despachos?estado='+v('f-desp-estado'));if(!d)return;
    const items=d.data||[];const c=document.getElementById('list-desp');
    if(!items.length){c.innerHTML='<div class="tbl-empty">Sem dados.</div>';return;}
    c.innerHTML=items.map(dp=>`<div class="tbl-row"><div class="col c2">${dp.ocorrencia?.numero_ocorrencia||'—'}</div><div class="col c1">${bPrio(dp.prioridade)}</div><div class="col c2">${dp.agente_destino?.nome||'—'}</div><div class="col c1">${dp.unidade?.nome||'—'}</div><div class="col c1">${bGen(dp.estado)}</div><div class="col c1">${fDT(dp.data_despacho)}</div><div class="col c1">${dp.estado==='pendente'?`<button class="btn-primary btn-sm" onclick="respDespacho(${dp.id})">Aceitar</button>`:''}</div></div>`).join('');
}
function modalNovoDespacho() {
    openModal('Novo Despacho',`<form onsubmit="return false;"><div class="form-row">${fg('Ocorrência *','<select id="m-desp-oc" required></select>')}${fg('Prioridade *',mkOpts('m-desp-prio',[{v:'baixa',t:'Baixa'},{v:'media',t:'Média'},{v:'alta',t:'Alta'},{v:'critica',t:'Crítica'}]))}</div><div class="form-row">${fg('Para (Agente) *','<select id="m-desp-ag" required></select>')}${fg('Unidade *',mkSel('m-desp-un',aux.unidades,'id','nome'))}</div>${fg('Instruções','<textarea id="m-desp-inst" rows="3"></textarea>')}</form>`,
    `<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitDespacho()"><i class='bx bx-send'></i> Despachar</button>`);
    loadSelOcorrencias('m-desp-oc');loadSelAgentes('m-desp-ag');
    document.getElementById('m-desp-un')?.addEventListener('change',function(){if(this.value)loadSelAgentes('m-desp-ag','&unidade_id='+this.value);});
}
async function submitDespacho() {
    showLoad();const d=await api('/despachos',{method:'POST',body:JSON.stringify({ocorrencia_id:v('m-desp-oc'),prioridade:v('m-desp-prio'),despachado_para:v('m-desp-ag'),unidade_destino:v('m-desp-un'),instrucoes:v('m-desp-inst')})});hideLoad();
    if(d?.success){toast('Despacho criado.','ok');closeModal();loadDespachos();}
}
async function respDespacho(id){const d=await api(`/despachos/${id}/responder`,{method:'PATCH',body:JSON.stringify({estado:'aceite'})});if(d?.success){toast('Despacho aceite.','ok');loadDespachos();}}

// ══════════════════
// PATRULHAS
// ══════════════════
async function loadPatrulhas() {
    const d=await api('/patrulhas?data='+v('f-pat-data')+'&estado='+v('f-pat-estado'));if(!d)return;
    const items=d.data||[];const c=document.getElementById('list-pat');
    if(!items.length){c.innerHTML='<div class="tbl-empty">Sem dados.</div>';return;}
    c.innerHTML=items.map(p=>`<div class="tbl-row"><div class="col c1">${fDate(p.data)}</div><div class="col c1">${p.turno?.nome||'—'}</div><div class="col c2">${p.zona?.nome||'—'}</div><div class="col c2">${p.agente_lider?.nome||'—'}</div><div class="col c1">${p.viatura?.matricula||'—'}</div><div class="col c1">${bGen(p.estado)}</div><div class="col c1">${p.estado==='planeada'?`<button class="btn-primary btn-sm" onclick="patEstado(${p.id},'em_curso')">Iniciar</button>`:p.estado==='em_curso'?`<button class="btn-ghost btn-sm" onclick="patEstado(${p.id},'concluida')">Concluir</button>`:''}</div></div>`).join('');
}
async function patEstado(id,est){const d=await api(`/patrulhas/${id}/estado`,{method:'PATCH',body:JSON.stringify({estado:est})});if(d?.success){toast('Estado actualizado.','ok');loadPatrulhas();}}

function modalNovaPatrulha() {
    openModal('Nova Patrulha',`<form onsubmit="return false;"><div class="form-row">${fg('Data *',`<input type="date" id="m-pat-data" value="${today()}" required>`)}${fg('Turno *',mkSel('m-pat-turno',aux.turnos,'id','nome'))}</div><div class="form-row">${fg('Unidade *',mkSel('m-pat-un',aux.unidades,'id','nome'))}${fg('Zona *','<select id="m-pat-zona" required></select>')}</div><div class="form-row">${fg('Líder *','<select id="m-pat-lider" required></select>')}${fg('Viatura','<select id="m-pat-viat"><option value="">Sem viatura</option></select>')}</div>${fg('Agentes *','<select id="m-pat-ags" multiple style="height:100px;" required></select>')}<p class="text-muted" style="margin-top:4px;">Ctrl+click para selecionar múltiplos</p></form>`,
    `<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitPatrulha()"><i class='bx bx-car'></i> Criar</button>`);
    loadSelAgentes('m-pat-lider');loadSelAgentes('m-pat-ags');
    document.getElementById('m-pat-un')?.addEventListener('change',async function(){
        if(!this.value)return;
        loadSelAgentes('m-pat-lider','&unidade_id='+this.value);loadSelAgentes('m-pat-ags','&unidade_id='+this.value);
        const v2=await api('/viaturas?unidade_id='+this.value+'&estado=operacional');if(v2)fillSelEl('m-pat-viat',v2,'id','matricula','Sem viatura');
    });
}
async function submitPatrulha() {
    const ags=Array.from(document.getElementById('m-pat-ags').selectedOptions).map(o=>parseInt(o.value));
    if(!ags.length){toast('Selecione agentes.','err');return;}
    showLoad();const d=await api('/patrulhas',{method:'POST',body:JSON.stringify({data:v('m-pat-data'),turno_id:v('m-pat-turno'),zona_id:v('m-pat-zona'),unidade_id:v('m-pat-un'),agente_lider_id:v('m-pat-lider'),viatura_id:v('m-pat-viat')||null,agentes:ags})});hideLoad();
    if(d?.success){toast('Patrulha criada.','ok');closeModal();loadPatrulhas();}
}

// ══════════════════
// ALERTAS
// ══════════════════
async function loadAlertas(estado,ev) {
    if(ev){ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));ev.target.classList.add('active');}
    const d=await api('/alertas?estado='+estado);if(!d)return;
    const items=d.data||[];const c=document.getElementById('list-alertas');
    if(!items.length){c.innerHTML='<div class="tbl-empty">Sem alertas.</div>';return;}
    c.innerHTML=items.map(a=>`<div class="alert-card ${a.prioridade}"><div class="alert-ico"><i class='bx ${a.tipo_alerta?.icone||'bx-bell-ring'}'></i></div><div class="alert-info"><h4>${a.titulo}</h4><p>${a.descricao.substring(0,200)}${a.descricao.length>200?'...':''}</p><div class="alert-meta">${bPrio(a.prioridade)} • ${a.tipo_alerta?.nome||''} • ${fDT(a.created_at)}</div></div><div>${a.estado==='activo'?`<button class="btn-primary btn-sm" onclick="resolveAlerta(${a.id})">✓ Resolver</button>`:`<span class="badge badge-gray">${a.estado}</span>`}</div></div>`).join('');
}
async function resolveAlerta(id){const d=await api(`/alertas/${id}/resolver`,{method:'PATCH'});if(d?.success){toast('Alerta resolvido.','ok');loadAlertas('activo');loadDashboard();}}

function modalNovoAlerta() {
    openModal('Emitir Alerta',`<form onsubmit="return false;"><div class="form-row">${fg('Tipo *',mkSel('m-al-tipo',aux.tipos_alerta||[{id:1,nome:'Suspeito Procurado'},{id:2,nome:'Viatura Roubada'},{id:3,nome:'Desaparecido'},{id:4,nome:'Alerta Geral'}],'id','nome'))}${fg('Prioridade *',mkOpts('m-al-prio',[{v:'urgente',t:'🔴 Urgente'},{v:'alta',t:'🟠 Alta'},{v:'normal',t:'🔵 Normal'}]))}</div>${fg('Título *','<input type="text" id="m-al-tit" required>')}${fg('Descrição *','<textarea id="m-al-desc" rows="4" required></textarea>')}<div style="background:var(--red-bg);padding:10px;border-radius:var(--r-sm);margin-top:12px;font-size:12px;color:var(--red);"><i class='bx bx-info-circle'></i> Alerta enviado para todas as esquadras.</div></form>`,
    `<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-danger" onclick="submitAlerta()"><i class='bx bx-bell-plus'></i> Emitir</button>`);
}
async function submitAlerta(){showLoad();const d=await api('/alertas',{method:'POST',body:JSON.stringify({tipo_alerta_id:v('m-al-tipo'),prioridade:v('m-al-prio'),titulo:v('m-al-tit'),descricao:v('m-al-desc')})});hideLoad();if(d?.success){toast('🚨 Alerta emitido!','ok');closeModal();loadAlertas('activo');}}

// ══════════════════
// VIATURAS
// ══════════════════
async function loadViaturas(){const d=await api('/viaturas');if(!d)return;const c=document.getElementById('list-viat');if(!d.length){c.innerHTML='<div class="tbl-empty">Sem dados.</div>';return;}c.innerHTML=d.map(vi=>`<div class="tbl-row"><div class="col c1"><strong>${vi.matricula}</strong></div><div class="col c2">${vi.marca} ${vi.modelo}${vi.cor?' ('+vi.cor+')':''}</div><div class="col c2">${vi.unidade?.nome||'—'}</div><div class="col c1">${(vi.quilometragem||0).toLocaleString()} km</div><div class="col c1"><span class="badge badge-${vi.estado==='operacional'?'green':'orange'}">${vi.estado}</span></div></div>`).join('');}

function modalNovaViatura(){openModal('Nova Viatura',`<form onsubmit="return false;"><div class="form-row">${fg('Matrícula *','<input type="text" id="m-vi-mat" required placeholder="LD-00-00-AA">')}${fg('Marca *','<input type="text" id="m-vi-marca" required>')}</div><div class="form-row">${fg('Modelo *','<input type="text" id="m-vi-mod" required>')}${fg('Ano','<input type="number" id="m-vi-ano">')}</div><div class="form-row">${fg('Cor','<input type="text" id="m-vi-cor">')}${fg('Unidade *',mkSel('m-vi-un',aux.unidades,'id','nome'))}</div></form>`,`<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitViatura()"><i class='bx bx-save'></i> Registar</button>`);}
async function submitViatura(){showLoad();const d=await api('/viaturas',{method:'POST',body:JSON.stringify({matricula:v('m-vi-mat'),marca:v('m-vi-marca'),modelo:v('m-vi-mod'),ano:v('m-vi-ano')||null,cor:v('m-vi-cor'),unidade_id:v('m-vi-un')})});hideLoad();if(d?.success){toast('Viatura registada.','ok');closeModal();loadViaturas();}}

// ══════════════════
// ARMAMENTO
// ══════════════════
async function loadArmamento(){const d=await api('/armamento');if(!d)return;const c=document.getElementById('list-arm');if(!d.length){c.innerHTML='<div class="tbl-empty">Sem dados.</div>';return;}c.innerHTML=d.map(a=>`<div class="tbl-row"><div class="col c1"><strong>${a.numero_serie}</strong></div><div class="col c1">${a.tipo_armamento?.nome||'—'}</div><div class="col c1">${a.marca||''} ${a.modelo||''}</div><div class="col c1">${a.calibre||'—'}</div><div class="col c2">${a.unidade?.nome||'—'}</div><div class="col c2">${a.atribuicao_actual?.agente?.nome||'<em class="text-muted">Disponível</em>'}</div><div class="col c1"><span class="badge badge-${a.estado==='operacional'?'green':'orange'}">${a.estado}</span></div></div>`).join('');}

function modalNovoArmamento(){openModal('Novo Armamento',`<form onsubmit="return false;"><div class="form-row">${fg('Tipo *',mkSel('m-arm-tipo',aux.tipos_armamento,'id','nome'))}${fg('Nº Série *','<input type="text" id="m-arm-serie" required>')}</div><div class="form-row">${fg('Marca','<input type="text" id="m-arm-marca">')}${fg('Modelo','<input type="text" id="m-arm-mod">')}</div><div class="form-row">${fg('Calibre','<input type="text" id="m-arm-cal">')}${fg('Unidade *',mkSel('m-arm-un',aux.unidades,'id','nome'))}</div></form>`,`<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitArmamento()"><i class='bx bx-save'></i> Registar</button>`);}
async function submitArmamento(){showLoad();const d=await api('/armamento',{method:'POST',body:JSON.stringify({tipo_armamento_id:v('m-arm-tipo'),numero_serie:v('m-arm-serie'),marca:v('m-arm-marca'),modelo:v('m-arm-mod'),calibre:v('m-arm-cal'),unidade_id:v('m-arm-un')})});hideLoad();if(d?.success){toast('Armamento registado.','ok');closeModal();loadArmamento();}}

// ══════════════════
// MENSAGENS
// ══════════════════
async function loadMensagens(tipo,ev){
    if(ev){ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));ev.target.classList.add('active');}
    const d=await api('/mensagens/'+(tipo==='inbox'?'inbox':'enviadas'));if(!d)return;
    const items=d.data||[];const c=document.getElementById('list-msg');
    if(!items.length){c.innerHTML='<div class="tbl-empty">Sem mensagens.</div>';return;}
    c.innerHTML=items.map(m=>`<div class="tbl-row" style="${!m.lida?'font-weight:600;background:var(--accent-light);':''}"><div class="col c0">${!m.lida?'<i class="bx bxs-circle" style="color:var(--accent);font-size:8px;"></i>':''}</div><div class="col c2">${tipo==='inbox'?(m.remetente?.nome||'—'):(m.destinatario?.nome||'—')}</div><div class="col c3">${m.titulo}</div><div class="col c1">${m.prioridade==='urgente'?'<span class="badge badge-red">Urgente</span>':'<span class="badge badge-gray">Normal</span>'}</div><div class="col c1">${fDT(m.created_at)}</div></div>`).join('');
}

function modalNovaMensagem(){openModal('Nova Mensagem',`<form onsubmit="return false;"><div class="form-row">${fg('Enviar para',mkOpts('m-msg-tipo',[{v:'agente',t:'Agente'},{v:'unidade',t:'Unidade'}],false))}${fg('Destinatário *','<select id="m-msg-dest" required></select>')}</div>${fg('Assunto *','<input type="text" id="m-msg-tit" required>')}${fg('Mensagem *','<textarea id="m-msg-corpo" rows="5" required></textarea>')}<div class="form-row">${fg('Prioridade',mkOpts('m-msg-prio',[{v:'normal',t:'Normal'},{v:'urgente',t:'Urgente'}],false))}</div></form>`,`<button class="btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn-primary" onclick="submitMensagem()"><i class='bx bx-send'></i> Enviar</button>`);
    document.getElementById('m-msg-tipo')?.addEventListener('change',function(){if(this.value==='agente')loadSelAgentes('m-msg-dest');else fillSelEl('m-msg-dest',aux.unidades,'id','nome','Selecionar unidade');});
    loadSelAgentes('m-msg-dest');
}
async function submitMensagem(){const tipo=v('m-msg-tipo');const body={titulo:v('m-msg-tit'),mensagem:v('m-msg-corpo'),prioridade:v('m-msg-prio')||'normal'};if(tipo==='agente')body.destinatario_id=v('m-msg-dest');else body.unidade_destino_id=v('m-msg-dest');showLoad();const d=await api('/mensagens',{method:'POST',body:JSON.stringify(body)});hideLoad();if(d?.success){toast('Mensagem enviada.','ok');closeModal();loadMensagens('enviadas');}}

// ══════════════════
// RELATÓRIOS
// ══════════════════
async function loadRelatorios(){const d=await api('/relatorios');if(!d)return;const items=d.data||[];const c=document.getElementById('list-rel');if(!items.length){c.innerHTML='<div class="tbl-empty">Sem relatórios.</div>';return;}c.innerHTML=items.map(r=>`<div class="tbl-row"><div class="col c2">${r.tipo_relatorio?.nome||'—'}</div><div class="col c2">${fDate(r.periodo_inicio)} — ${fDate(r.periodo_fim)}</div><div class="col c2">${r.unidade?.nome||'Todas'}</div><div class="col c1">${fDate(r.created_at)}</div></div>`).join('');}
async function gerarRelatorio(){showLoad();const d=await api('/relatorios/gerar',{method:'POST',body:JSON.stringify({tipo_relatorio_id:v('rel-tipo'),periodo_inicio:v('rel-di'),periodo_fim:v('rel-df'),unidade_id:v('rel-unidade')||null})});hideLoad();if(d?.success){toast('Relatório gerado!','ok');const dt=d.dados;document.getElementById('rel-resultado').style.display='block';document.getElementById('rel-stats').innerHTML=`<div class="stat-card"><div><span class="stat-value">${dt.total_ocorrencias}</span><span class="stat-label">Ocorrências</span></div></div><div class="stat-card"><div><span class="stat-value">${dt.ocorrencias_resolvidas}</span><span class="stat-label">Resolvidas</span></div></div><div class="stat-card"><div><span class="stat-value">${dt.taxa_resolucao}%</span><span class="stat-label">Taxa Resolução</span></div></div><div class="stat-card"><div><span class="stat-value">${dt.total_detencoes}</span><span class="stat-label">Detenções</span></div></div>`;loadRelatorios();}}

// ══════════════════
// IDENTIDADE
// ══════════════════
async function loadIdentidade(){loadAgentes('activo','list-ag-act');loadAgentes('inactivo','list-ag-ina');loadUnidades();}
async function loadAgentes(estado,cid){const d=await api('/agentes?estado='+estado);if(!d)return;const c=document.getElementById(cid);if(!c)return;if(!d.length){c.innerHTML='<div class="tbl-empty">Sem agentes.</div>';return;}c.innerHTML=d.map(a=>`<div class="tbl-row"><div class="col c2"><strong>${a.nome}</strong></div><div class="col c1">${a.nip}</div><div class="col c2">${a.cargo||'—'}</div><div class="col c2">${a.unidade?.nome||'—'}</div><div class="col c1">${a.patente?.nome||'—'}</div><div class="col c1"><span class="badge badge-${a.estado==='activo'?'green':'gray'}">${a.estado}</span></div><div class="col c1"><button class="btn-icon" onclick="toggleAgente(${a.id})" title="${a.estado==='activo'?'Desactivar':'Activar'}"><i class='bx ${a.estado==='activo'?'bx-block':'bx-check-circle'}'></i></button></div></div>`).join('');}
async function loadUnidades(){const d=await api('/unidades');if(!d)return;const c=document.getElementById('list-unidades');if(!c)return;c.innerHTML=d.map(u=>`<div class="tbl-row"><div class="col c2"><strong>${u.nome}</strong></div><div class="col c2">${u.tipo_unidade?.nome||'—'}</div><div class="col c2">${u.endereco||'—'}</div><div class="col c1"><span class="badge badge-${u.estado==='activo'?'green':'gray'}">${u.estado}</span></div><div class="col c1"><button class="btn-icon" onclick="toggleUnidade(${u.id})"><i class='bx bx-power-off'></i></button></div></div>`).join('');}
function openIdTab(name,ev){document.querySelectorAll('.idtab').forEach(t=>t.classList.remove('active'));document.getElementById('idtab-'+name)?.classList.add('active');if(ev){ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));ev.target.classList.add('active');}}
async function criarAgente(ev){ev.preventDefault();showLoad();const d=await api('/agentes',{method:'POST',body:JSON.stringify({nome:v('ag-nome'),nip:v('ag-nip'),bi:v('ag-bi'),email:v('ag-email'),telefone:v('ag-tel'),sexo:v('ag-sexo'),unidade_id:v('ag-unidade'),cargo:v('ag-cargo'),patente_id:v('ag-patente'),perfil_id:v('ag-perfil'),estado:v('ag-estado')})});hideLoad();if(d?.success){toast('Agente registado!','ok');document.getElementById('form-agente')?.reset();loadIdentidade();openIdTab('ag-act');}return false;}
async function toggleAgente(id){const d=await api(`/agentes/${id}/toggle-estado`,{method:'PATCH'});if(d?.success){toast(d.message,'ok');loadIdentidade();}}
async function toggleUnidade(id){const d=await api(`/unidades/${id}/toggle-estado`,{method:'PATCH'});if(d?.success){toast(d.message,'ok');loadUnidades();}}

// ══════════════════
// LOGS
// ══════════════════
async function loadLogs(page=1){const p=new URLSearchParams({page,acao:v('f-log-acao'),tabela:v('f-log-tabela'),data_inicio:v('f-log-di'),data_fim:v('f-log-df')});const d=await api('/logs?'+p);if(!d)return;const items=d.data||[];const c=document.getElementById('list-logs');if(!items.length){c.innerHTML='<div class="tbl-empty">Sem logs.</div>';return;}c.innerHTML=items.map(l=>`<div class="tl-item"><div class="tl-dot"></div><div class="tl-content"><div class="tl-time">${fDT(l.created_at)} • IP: ${l.ip||'—'}</div><div class="tl-text"><span class="tl-user">${l.user?.email||'Sistema'}</span> — <span class="badge badge-${l.acao==='criar'?'green':l.acao==='apagar'?'red':'blue'}">${l.acao}</span>${l.tabela?` em <strong>${l.tabela}</strong>`:''}${l.descricao?' — '+l.descricao:''}</div></div></div>`).join('');renderPag('pag-logs',d,loadLogs);}

// ══════════════════
// CONFIGURAÇÕES
// ══════════════════
async function loadConfig(){const d=await api('/configuracoes');if(!d)return;const c=document.getElementById('config-content');if(!c)return;let h='';for(const[g,cfgs]of Object.entries(d)){h+=`<div class="card" style="margin-bottom:12px;"><div class="card-head"><h3 style="text-transform:capitalize;">${g}</h3></div><div class="card-body">`;cfgs.forEach(cfg=>h+=`<div class="detail-line"><span class="dl">${cfg.descricao||cfg.chave}</span><span class="dv">${cfg.valor}</span></div>`);h+='</div></div>';}c.innerHTML=h;}

// ══════════════════
// NOTIFICAÇÕES
// ══════════════════
async function checkNotifs(){try{const d=await api('/mensagens/nao-lidas');if(d?.total>0){const dot=document.getElementById('notif-dot');if(dot)dot.style.display='block';}}catch(e){}setTimeout(checkNotifs,60000);}

// ══════════════════
// UTILITÁRIOS
// ══════════════════
function v(id){const el=document.getElementById(id);return el?el.value.trim():'';}
function txt(id,t){const el=document.getElementById(id);if(el)el.textContent=t;}
function today(){return new Date().toISOString().split('T')[0];}
function nowLocal(){return new Date().toISOString().slice(0,16);}
function fDate(d){if(!d)return '—';try{return new Date(d).toLocaleDateString('pt-AO');}catch(e){return d;}}
function fDT(d){if(!d)return '—';try{const dt=new Date(d);return dt.toLocaleDateString('pt-AO')+' '+dt.toLocaleTimeString('pt-AO',{hour:'2-digit',minute:'2-digit'});}catch(e){return d;}}

function fg(label,input){return `<div class="form-col"><label>${label}</label>${input}</div>`;}
function dl(l,val){return `<div class="detail-line"><span class="dl">${l||'—'}</span><span class="dv">${val||'—'}</span></div>`;}

function bPrio(p){const m={baixa:'green',media:'orange',alta:'orange',critica:'red',urgente:'red',normal:'blue'};return `<span class="badge badge-${m[p]||'gray'}">${p||'—'}</span>`;}
function bEstado(e){if(!e)return '—';return `<span class="badge" style="background:${e.cor||'#eee'}20;color:${e.cor||'#666'}">${e.nome}</span>`;}
function bEstadoObj(e){if(!e)return '';return `<span class="badge" style="background:${e.cor||'#eee'}20;color:${e.cor||'#666'}">${e.nome}</span>`;}
function bGen(t){return `<span class="badge badge-teal">${t||'—'}</span>`;}

// ── Toast ──
function toast(msg,type='info'){const c=document.getElementById('toast-container');if(!c)return;const el=document.createElement('div');el.className='toast '+type;const icos={ok:'bx-check-circle',err:'bx-error-circle',warn:'bx-error',info:'bx-info-circle'};el.innerHTML=`<i class='bx ${icos[type]||icos.info}'></i> ${msg}`;c.appendChild(el);setTimeout(()=>{el.style.opacity='0';setTimeout(()=>el.remove(),300);},4000);}

// ── Loading ──
function showLoad(){document.getElementById('loading-overlay')?.classList.add('active');}
function hideLoad(){document.getElementById('loading-overlay')?.classList.remove('active');}

// ── Modal ──
function openModal(title,body,foot){document.getElementById('modal-title').textContent=title;document.getElementById('modal-body').innerHTML=body;document.getElementById('modal-foot').innerHTML=foot||'';document.getElementById('modal').style.display='flex';}
function closeModal(){document.getElementById('modal').style.display='none';}

// ── Confirmação ──
function showConfirm(title,msg,cb){document.getElementById('confirm-title').textContent=title;document.getElementById('confirm-msg').textContent=msg;confirmCb=cb;document.getElementById('modal-confirm').style.display='flex';}
function closeConfirm(){document.getElementById('modal-confirm').style.display='none';confirmCb=null;}
function execConfirm(){if(confirmCb)confirmCb();closeConfirm();}
function confirmarLogout(ev){ev.preventDefault();showConfirm('Sair','Tem a certeza que deseja sair?',()=>document.getElementById('logout-form').submit());}

// ── Detail Panel ──
function openDetail(title,html){document.getElementById('detail-title').textContent=title;document.getElementById('detail-body').innerHTML=html;document.getElementById('detail-panel').style.display='flex';document.getElementById('detail-overlay').style.display='block';}
function closeDetail(){document.getElementById('detail-panel').style.display='none';document.getElementById('detail-overlay').style.display='none';}

// ── Pagination ──
function renderPag(cid,data,cb){const c=document.getElementById(cid);if(!c||!data.last_page||data.last_page<=1){if(c)c.innerHTML='';return;}let h='';if(data.current_page>1)h+=`<button onclick="${cb.name}(${data.current_page-1})">←</button>`;for(let i=1;i<=data.last_page;i++){if(i===1||i===data.last_page||Math.abs(i-data.current_page)<=2)h+=`<button class="${i===data.current_page?'active':''}" onclick="${cb.name}(${i})">${i}</button>`;else if(Math.abs(i-data.current_page)===3)h+='<button disabled>...</button>';}if(data.current_page<data.last_page)h+=`<button onclick="${cb.name}(${data.current_page+1})">→</button>`;c.innerHTML=h;}

// ── User Menu ──
function initUserMenu(){const trig=document.getElementById('user-trigger');const menu=document.getElementById('user-menu');if(!trig||!menu)return;trig.addEventListener('click',e=>{e.stopPropagation();menu.classList.toggle('open');});document.addEventListener('click',e=>{if(!trig.contains(e.target)&&!menu.contains(e.target))menu.classList.remove('open');});}

// ── Search ──
function initSearch(){const input=document.getElementById('searchInput');if(!input)return;input.addEventListener('focus',()=>{});input.addEventListener('keydown',e=>{if(e.key==='Enter'){const b=input.value.trim();if(b){toast('Pesquisa global em desenvolvimento','info');}}});}

// ── Keyboard ──
function initKeys(){document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key==='k'){e.preventDefault();document.getElementById('searchInput')?.focus();}if(e.key==='Escape'){closeModal();closeConfirm();closeDetail();}});}