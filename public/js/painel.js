/* ═══════════════════════════════════════════════════
   SCGD VIANA — JS REFATORIZADO
   Formulários no main · Validação completa
   ═══════════════════════════════════════════════════ */

let aux = {};
let confirmCb = null;
let tempEvidencias = [];
let currentView = null; // guarda a view actual para voltar

// ══════════════════
// INIT
// ══════════════════
document.addEventListener('DOMContentLoaded', () => {
    loadAux();
    loadDashboard();
    initUserMenu();
    initKeys();
    checkNotifs();
    initSearchEnter();
});

// ══════════════════
// API
// ══════════════════
async function api(url, opt = {}) {
    const h = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': APP.csrf, 'X-Requested-With': 'XMLHttpRequest' };
    if (opt.body instanceof FormData) delete h['Content-Type'];
    const cfg = { credentials: 'same-origin', headers: h, ...opt };
    if (opt.headers) cfg.headers = { ...h, ...opt.headers };
    try {
        const r = await fetch('/api' + url, cfg);
        if (r.status === 401) { window.location.href = '/login'; return null; }
        if (r.status === 403) { toast('Sem permissao para esta accao.', 'err'); return null; }
        if (r.status === 422) {
            const e = await r.json();
            (e.errors ? Object.values(e.errors).flat() : [e.message || 'Dados invalidos.']).forEach(m => toast(m, 'err'));
            return null;
        }
        const d = await r.json();
        if (!r.ok) { toast(d.message || 'Erro no servidor.', 'err'); return null; }
        return d;
    } catch (e) { toast('Erro de conexao com o servidor.', 'err'); console.error(e); return null; }
}

async function apiForm(url, fd) {
    try {
        const r = await fetch('/api' + url, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': APP.csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        if (r.status === 422) { const e = await r.json(); (e.errors ? Object.values(e.errors).flat() : ['Dados invalidos.']).forEach(m => toast(m, 'err')); return null; }
        const d = await r.json(); if (!r.ok) { toast(d.message || 'Erro.', 'err'); return null; } return d;
    } catch (e) { toast('Erro de conexao.', 'err'); return null; }
}

// ══════════════════
// VALIDACAO
// ══════════════════
function validarNome(val) {
    if (!val || val.trim().length < 3) return 'Nome deve ter no minimo 3 caracteres.';
    if (/[0-9]/.test(val)) return 'Nome nao pode conter numeros.';
    if (/[^a-zA-ZÀ-ÿ\s\-']/.test(val)) return 'Nome contem caracteres invalidos.';
    return null;
}

function validarBI(val) {
    if (!val) return null; // BI pode ser opcional
    val = val.trim();
    if (val.length < 10) return 'BI deve ter no minimo 10 caracteres.';
    if (!/^[0-9]+[A-Za-z]{2}[0-9]{3}$/.test(val) && !/^[0-9]{10,14}[A-Za-z]{0,2}[0-9]{0,3}$/.test(val))
        return 'Formato de BI invalido.';
    return null;
}

function validarDataNaoFutura(val) {
    if (!val) return null;
    const d = new Date(val);
    const hoje = new Date();
    hoje.setHours(23, 59, 59, 999);
    if (d > hoje) return 'A data nao pode ser no futuro.';
    return null;
}

function validarTelefone(val) {
    if (!val) return null;
    val = val.trim();
    if (!/^[0-9+\-\s()]{7,20}$/.test(val)) return 'Telefone invalido.';
    return null;
}

function validarEmail(val) {
    if (!val) return null;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) return 'Email invalido.';
    return null;
}

function validarIdadeMenor(val) {
    if (!val) return null;
    const nasc = new Date(val);
    const hoje = new Date();
    let idade = hoje.getFullYear() - nasc.getFullYear();
    const m = hoje.getMonth() - nasc.getMonth();
    if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
    if (idade < 18) return 'Atenção: Pessoa menor de idade (' + idade + ' anos).';
    return null;
}

function validarCampo(id, validador) {
    const el = document.getElementById(id);
    if (!el) return null;
    const erro = validador(el.value);
    const errEl = el.parentNode.querySelector('.form-error');
    if (erro) {
        el.classList.add('error');
        if (errEl) errEl.textContent = erro; else { const span = document.createElement('span'); span.className = 'form-error'; span.textContent = erro; el.parentNode.appendChild(span); }
        return erro;
    } else {
        el.classList.remove('error');
        if (errEl) errEl.remove();
        return null;
    }
}

function validarObrigatorio(id, label) {
    const el = document.getElementById(id);
    if (!el) return label + ' e obrigatorio.';
    if (!el.value || !el.value.trim()) {
        el.classList.add('error');
        const errEl = el.parentNode.querySelector('.form-error');
        const msg = label + ' e obrigatorio.';
        if (errEl) errEl.textContent = msg; else { const span = document.createElement('span'); span.className = 'form-error'; span.textContent = msg; el.parentNode.appendChild(span); }
        return msg;
    }
    el.classList.remove('error');
    const errEl = el.parentNode.querySelector('.form-error');
    if (errEl) errEl.remove();
    return null;
}

function limparErros() {
    document.querySelectorAll('.form-error').forEach(e => e.remove());
    document.querySelectorAll('.error').forEach(e => e.classList.remove('error'));
}

// ══════════════════
// NAVEGACAO
// ══════════════════
function showSection(id) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    const el = document.getElementById('section-' + id);
    if (el) el.classList.add('active');
    document.querySelectorAll('.nav-item').forEach(n => n.classList.toggle('active', n.dataset.section === id));
    currentView = id;

    const loaders = {
        inicio: loadDashboard, ocorrencias: loadOcorrencias, processos: loadProcessos,
        pessoas: loadPessoas, detencoes: loadDetencoes, evidencias: () => loadEvidencias(1, 'todos'),
        investigacoes: loadInvestigacoes, despachos: loadDespachos, patrulhas: loadPatrulhas,
        alertas: () => loadAlertas('activo'), viaturas: loadViaturas, armamento: loadArmamento,
        mensagens: () => loadMensagens('inbox'), relatorios: loadRelatorios,
        identidade: loadIdentidade, logs: loadLogs, configuracoes: loadConfig,
    };
    if (loaders[id]) loaders[id]();
    document.getElementById('user-menu')?.classList.remove('open');
}

// Mostra conteudo dinamico no main
function renderMain(sectionId, html) {
    // Esconde todas as sections
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    // Cria ou reutiliza section dinamica
    let dynSection = document.getElementById('section-dynamic');
    if (!dynSection) {
        dynSection = document.createElement('div');
        dynSection.id = 'section-dynamic';
        dynSection.className = 'section';
        document.getElementById('main-content').appendChild(dynSection);
    }
    dynSection.innerHTML = html;
    dynSection.classList.add('active');
}

function voltarPara(sectionId) {
    const dynSection = document.getElementById('section-dynamic');
    if (dynSection) { dynSection.classList.remove('active'); dynSection.innerHTML = ''; }
    showSection(sectionId || currentView || 'inicio');
}

// ══════════════════
// DADOS AUXILIARES
// ══════════════════
async function loadAux() {
    const d = await api('/dados-auxiliares');
    if (!d) return;
    aux = d;
    fillSel('f-oc-estado', aux.estados_ocorrencia, 'id', 'nome', 'Todos os estados');
    fillSel('f-oc-tipo', aux.tipos_crime, 'id', 'nome', 'Todos os tipos');
    fillSel('f-det-estado', aux.estados_detencao, 'id', 'nome', 'Todos');
    fillSel('f-det-unidade', aux.unidades, 'id', 'nome', 'Unidade');
    fillSel('f-inv-estado', aux.estados_investigacao, 'id', 'nome', 'Todos');
    fillSel('f-inv-unidade', aux.unidades, 'id', 'nome', 'Unidade');
    fillSel('f-proc-unidade', aux.unidades, 'id', 'nome', 'Unidade');
    fillSel('f-arm-tipo', aux.tipos_armamento, 'id', 'nome', 'Tipo');
    fillSel('f-arm-unidade', aux.unidades, 'id', 'nome', 'Unidade');
    fillSel('f-viat-unidade', aux.unidades, 'id', 'nome', 'Unidade');
    fillSel('f-pat-unidade', aux.unidades, 'id', 'nome', 'Unidade');
    fillSel('ag-unidade', aux.unidades, 'id', 'nome', 'Selecionar');
    fillSel('ag-patente', aux.patentes, 'id', 'nome', 'Selecionar');
    fillSel('ag-perfil', aux.perfis, 'id', 'descricao', 'Selecionar');
    fillSel('rel-tipo', aux.tipos_relatorio, 'id', 'nome', 'Selecionar');
    fillSel('rel-unidade', aux.unidades, 'id', 'nome', 'Todas');
}

function fillSel(id, items, vk, tk, ph) { const el = document.getElementById(id); if (!el || !items) return; el.innerHTML = `<option value="">${ph}</option>` + items.map(i => `<option value="${i[vk]}">${i[tk]}</option>`).join(''); }

function mkSel(id, items, vk, tk, ph = 'Selecionar', req = true) {
    if (!items) return `<select id="${id}"><option value="">-</option></select>`;
    return `<select id="${id}" ${req ? 'required' : ''}><option value="">${ph}</option>${items.map(i => `<option value="${i[vk]}">${i[tk]}</option>`).join('')}</select>`;
}

function mkOpts(id, opts, req = true) {
    return `<select id="${id}" ${req ? 'required' : ''}><option value="">Selecionar</option>${opts.map(o => `<option value="${o.v}">${o.t}</option>`).join('')}</select>`;
}

// ══════════════════
// DASHBOARD
// ══════════════════
async function loadDashboard() {
    const d = await api('/dashboard/metricas'); if (!d) return;
    txt('m-total-oc', d.total_ocorrencias); txt('m-abertas', d.ocorrencias_abertas);
    txt('m-resolvidas', d.ocorrencias_resolvidas); txt('m-detencoes', d.detencoes_mes);
    txt('m-inv', d.investigacoes_activas); txt('m-alertas', d.alertas_activos);
    txt('m-proc', d.processos_activos || 0);
    renderBar('chart-tipo', d.crimes_por_tipo, 'tipo_nome', 'total');
    renderBar('chart-mes', d.crimes_por_mes, i => { const ms = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']; return ms[i.mes] || i.mes; }, 'total');
    renderDashUltimas(d.ultimas_ocorrencias || []);
    const dot = document.getElementById('notif-dot');
    if (dot) dot.style.display = d.alertas_activos > 0 ? 'block' : 'none';
}

function renderBar(cid, items, labelFn, vk) {
    const c = document.getElementById(cid); if (!c) return;
    if (!items || !items.length) { c.innerHTML = '<p class="text-muted">Sem dados disponiveis.</p>'; return; }
    const max = Math.max(...items.map(i => i[vk]));
    c.innerHTML = '<div class="bar-chart">' + items.map(i => {
        const lbl = typeof labelFn === 'function' ? labelFn(i) : (i[labelFn] || '-');
        const pct = max > 0 ? (i[vk] / max * 100) : 0;
        return `<div class="bar-row"><div class="bar-label" title="${lbl}">${lbl}</div><div class="bar-bg"><div class="bar-fg" style="width:${pct}%"><span class="bar-val">${i[vk]}</span></div></div></div>`;
    }).join('') + '</div>';
}

function renderDashUltimas(items) {
    const c = document.getElementById('dash-ultimas'); if (!c) return;
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem ocorrencias recentes.</div>'; return; }
    c.innerHTML = items.map(o => `<div class="tbl-row" onclick="viewOcorrencia(${o.id})"><div class="col c2"><strong>${o.numero_ocorrencia}</strong></div><div class="col c2">${o.tipo_crime?.nome || '-'}</div><div class="col c3">${o.local || '-'}</div><div class="col c1">${bPrio(o.prioridade)}</div><div class="col c1">${bEstado(o.estado)}</div><div class="col c1">${fDate(o.data_ocorrencia)}</div></div>`).join('');
}

// ══════════════════
// OCORRENCIAS
// ══════════════════
async function loadOcorrencias(page = 1) {
    const p = new URLSearchParams({ page, estado_id: v('f-oc-estado'), prioridade: v('f-oc-prioridade'), tipo_crime_id: v('f-oc-tipo'), data_inicio: v('f-oc-di'), data_fim: v('f-oc-df'), busca: v('f-oc-busca') });
    const d = await api('/ocorrencias?' + p); if (!d) return;
    const items = d.data || []; const c = document.getElementById('list-oc');
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Nenhuma ocorrencia encontrada.</div>'; return; }
    c.innerHTML = items.map(o => `<div class="tbl-row prio-${o.prioridade}" onclick="viewOcorrencia(${o.id})"><div class="col c2"><strong>${o.numero_ocorrencia}</strong></div><div class="col c2">${o.tipo_crime?.nome || '-'}</div><div class="col c2">${o.local || '-'}</div><div class="col c1">${fDate(o.data_ocorrencia)}</div><div class="col c1">${bPrio(o.prioridade)}</div><div class="col c1">${bEstado(o.estado)}</div><div class="col c1"><button class="btn-icon" onclick="event.stopPropagation();viewOcorrencia(${o.id})" title="Ver"><i class='bx bx-show'></i></button></div></div>`).join('');
    renderPag('pag-oc', d, loadOcorrencias);
}

// ── NOVA OCORRENCIA (no main) ──
function formNovaOcorrencia() {
    tempEvidencias = [];
    renderMain('ocorrencias', `
        <div class="page-header"><div><h1 class="page-title">Registar Nova Ocorrencia</h1><p class="page-desc">Preencha todos os campos obrigatorios (*)</p></div>
            <button class="btn-ghost" onclick="voltarPara('ocorrencias')"><i class='bx bx-arrow-back'></i> Voltar</button>
        </div>
        <div class="form-card">
            <div class="form-section">Dados da Ocorrencia</div>
            <div class="form-row">
                <div class="form-col"><label>Tipo de Crime *</label>${mkSel('noc-tipo', aux.tipos_crime, 'id', 'nome')}</div>
                <div class="form-col"><label>Prioridade *</label>${mkOpts('noc-prio', [{ v: 'baixa', t: 'Baixa' }, { v: 'media', t: 'Media' }, { v: 'alta', t: 'Alta' }, { v: 'critica', t: 'Critica' }])}</div>
            </div>
            <div class="form-row">
                <div class="form-col"><label>Data da Ocorrencia *</label><input type="date" id="noc-data" value="${today()}" max="${today()}"><span class="form-hint">Data válida</span></div>
                <div class="form-col"><label>Hora</label><input type="time" id="noc-hora"></div>
            </div>
            <div class="form-row">
                <div class="form-col"><label>Local *</label><input type="text" id="noc-local" placeholder="Descreva o local da ocorrencia"></div>
                <div class="form-col"><label>Bairro</label>${mkSel('noc-bairro', aux.bairros, 'id', 'nome', 'Selecionar bairro', false)}</div>
            </div>
            <div class="form-row">
                <div class="form-col"><label>Unidade Policial *</label>${mkSel('noc-unidade', aux.unidades, 'id', 'nome')}</div>
                <div class="form-col"><label>Agente Responsavel</label><select id="noc-agente"><option value="">Definir depois</option></select><span class="form-hint">Selecione a unidade primeiro</span></div>
            </div>
            <div class="form-col" style="margin-bottom:14px;"><label>Descricao Detalhada *</label><textarea id="noc-desc" rows="5" placeholder="Descreva os factos com o maximo de detalhes possiveis..."></textarea></div>

            <div class="form-section">Evidencias (opcional)</div>
            <p class="form-hint" style="margin-bottom:12px;">Adicione fotografias, documentos ou outros ficheiros relevantes</p>
            <div class="form-row">
                <div class="form-col"><label>Tipo</label>${mkSel('noc-ev-tipo', aux.tipos_evidencia, 'id', 'nome', 'Tipo', false)}</div>
                <div class="form-col"><label>Descricao</label><input type="text" id="noc-ev-desc" placeholder="Ex: Fotografia da cena do crime"></div>
            </div>
            <div class="form-row">
                <div class="form-col"><label>Ficheiro</label><input type="file" id="noc-ev-file"></div>
                <div class="form-col"><label>Localizacao Fisica</label><input type="text" id="noc-ev-loc" placeholder="Cofre A, Prateleira 1"></div>
            </div>
            <button type="button" class="btn-ghost btn-sm" onclick="addEvidenciaTemp()"><i class='bx bx-plus'></i> Adicionar Evidencia</button>
            <div id="noc-ev-list" class="ev-inline-list"></div>

            <div class="form-actions">
                <button class="btn-ghost" onclick="voltarPara('ocorrencias')">Cancelar</button>
                <button class="btn-primary" onclick="submitNovaOcorrencia()"><i class='bx bx-save'></i> Registar Ocorrencia</button>
            </div>
        </div>
    `);

    // Carregar agentes ao mudar unidade
    document.getElementById('noc-unidade')?.addEventListener('change', async function () {
        if (this.value) { const ag = await api('/agentes?estado=activo&unidade_id=' + this.value); if (ag) fillSel('noc-agente', ag, 'id', 'nome', 'Definir depois'); }
    });
}

function addEvidenciaTemp() {
    const tipo = v('noc-ev-tipo');
    const desc = v('noc-ev-desc');
    if (!tipo || !desc) { toast('Preencha o tipo e descricao da evidencia.', 'err'); return; }

    const fileEl = document.getElementById('noc-ev-file');
    const file = fileEl?.files[0] || null;
    const tipoNome = aux.tipos_evidencia?.find(t => t.id == tipo)?.nome || '';

    tempEvidencias.push({ tipo_evidencia_id: tipo, descricao: desc, localizacao_fisica: v('noc-ev-loc'), file: file, tipoNome: tipoNome, fileName: file?.name || 'Sem ficheiro' });

    // Limpar campos
    document.getElementById('noc-ev-desc').value = '';
    document.getElementById('noc-ev-loc').value = '';
    if (fileEl) fileEl.value = '';

    renderTempEvidencias();
}

function removeEvidenciaTemp(idx) { tempEvidencias.splice(idx, 1); renderTempEvidencias(); }

function renderTempEvidencias() {
    const c = document.getElementById('noc-ev-list'); if (!c) return;
    if (!tempEvidencias.length) { c.innerHTML = ''; return; }
    c.innerHTML = tempEvidencias.map((e, i) => `<div class="ev-inline-item"><i class='bx bx-file'></i><span class="ev-inline-name">${e.tipoNome}: ${e.descricao}</span><span class="text-muted">${e.fileName}</span><i class='bx bx-x ev-inline-remove' onclick="removeEvidenciaTemp(${i})"></i></div>`).join('');
}

async function submitNovaOcorrencia() {
    limparErros();
    let erros = [];
    erros.push(validarObrigatorio('noc-tipo', 'Tipo de crime'));
    erros.push(validarObrigatorio('noc-prio', 'Prioridade'));
    erros.push(validarObrigatorio('noc-data', 'Data'));
    erros.push(validarCampo('noc-data', validarDataNaoFutura));
    erros.push(validarObrigatorio('noc-local', 'Local'));
    erros.push(validarObrigatorio('noc-unidade', 'Unidade'));
    erros.push(validarObrigatorio('noc-desc', 'Descricao'));
    erros = erros.filter(e => e !== null);
    if (erros.length) { toast('Corrija os erros assinalados.', 'err'); return; }

    showLoad();
    const bairroSel = document.getElementById('noc-bairro');
    const bairroNome = bairroSel?.options[bairroSel.selectedIndex]?.text;

    const d = await api('/ocorrencias', { method: 'POST', body: JSON.stringify({ tipo_crime_id: v('noc-tipo'), prioridade: v('noc-prio'), data_ocorrencia: v('noc-data'), hora_ocorrencia: v('noc-hora') || null, local: v('noc-local'), bairro: bairroNome !== 'Selecionar bairro' ? bairroNome : null, bairro_id: v('noc-bairro') || null, unidade_id: v('noc-unidade'), agente_responsavel_id: v('noc-agente') || null, descricao: v('noc-desc') }) });

    if (!d?.success) { hideLoad(); return; }

    // Registar evidencias
    if (tempEvidencias.length) {
        for (const ev of tempEvidencias) {
            const fd = new FormData();
            fd.append('ocorrencia_id', d.ocorrencia.id);
            fd.append('tipo_evidencia_id', ev.tipo_evidencia_id);
            fd.append('descricao', ev.descricao);
            fd.append('localizacao_fisica', ev.localizacao_fisica || '');
            if (ev.file) fd.append('ficheiro', ev.file);
            await apiForm('/evidencias', fd);
        }
    }

    hideLoad();
    toast('Ocorrencia registada: ' + d.ocorrencia.numero_ocorrencia, 'ok');
    tempEvidencias = [];
    voltarPara('ocorrencias');
}

// ── VER OCORRENCIA (no main) ──
async function viewOcorrencia(id) {
    showLoad();
    const o = await api('/ocorrencias/' + id);
    hideLoad(); if (!o) return;

    let h = `<div class="page-header"><div><h1 class="page-title">Ocorrencia ${o.numero_ocorrencia}</h1><p class="page-desc">Registada em ${fDate(o.data_ocorrencia)} ${o.hora_ocorrencia || ''}</p></div>
        <div class="detail-view-actions">
            <button class="btn-ghost btn-sm" onclick="exportPdfOcorrencia(${o.id})"><i class='bx bx-download'></i> Exportar PDF</button>
            <button class="btn-ghost" onclick="voltarPara('ocorrencias')"><i class='bx bx-arrow-back'></i> Voltar</button>
        </div></div>
        <div class="detail-view">`;

    h += `<div class="detail-sect"><h4>Dados Gerais</h4>
        ${dl('Numero', o.numero_ocorrencia)}${dl('Tipo de Crime', o.tipo_crime?.nome)}${dl('Categoria', o.tipo_crime?.categoria?.nome)}
        ${dl('Data / Hora', fDate(o.data_ocorrencia) + ' ' + (o.hora_ocorrencia || ''))}${dl('Local', o.local)}${dl('Bairro', o.bairro)}
        ${dl('Prioridade', bPrio(o.prioridade))}${dl('Estado', bEstado(o.estado))}${dl('Unidade', o.unidade?.nome)}
        ${dl('Registado por', o.agente_registo?.nome)}${dl('Responsavel', o.agente_responsavel?.nome || 'Nao atribuido')}
        ${dl('Confidencial', o.confidencial ? 'Sim' : 'Nao')}
    </div>`;

    h += `<div class="detail-sect"><h4>Descricao dos Factos</h4><div class="detail-desc">${o.descricao}</div></div>`;

    // Envolvidos
    h += `<div class="detail-sect"><h4>Pessoas Envolvidas ${o.envolvimentos?.length ? '(' + o.envolvimentos.length + ')' : ''}</h4>`;
    if (o.envolvimentos?.length) {
        h += '<div class="tbl" style="margin-top:0;"><div class="tbl-head"><div class="col c2">Nome</div><div class="col c1">BI</div><div class="col c1">Tipo</div><div class="col c2">Obs</div></div>';
        o.envolvimentos.forEach(e => {
            const bc = e.tipo_envolvimento?.id === 1 ? 'red' : e.tipo_envolvimento?.id === 2 ? 'orange' : 'blue';
            h += `<div class="tbl-row" onclick="viewPessoa(${e.pessoa?.id})"><div class="col c2"><strong>${e.pessoa?.nome || '-'}</strong></div><div class="col c1">${e.pessoa?.bi || '-'}</div><div class="col c1"><span class="badge badge-${bc}">${e.tipo_envolvimento?.nome}</span></div><div class="col c2">${e.descricao || '-'}</div></div>`;
        });
        h += '</div>';
    } else { h += '<p class="text-muted">Nenhuma pessoa associada.</p>'; }
    h += `<button class="link-btn" style="margin-top:10px;" onclick="formAddEnvolvido(${o.id})">+ Adicionar pessoa</button></div>`;

    // Evidencias
    h += `<div class="detail-sect"><h4>Evidencias ${o.evidencias?.length ? '(' + o.evidencias.length + ')' : ''}</h4>`;
    if (o.evidencias?.length) {
        h += '<div class="tbl" style="margin-top:0;"><div class="tbl-head"><div class="col c1">Codigo</div><div class="col c1">Tipo</div><div class="col c2">Descricao</div><div class="col c1">Estado</div><div class="col c1">Ficheiro</div></div>';
        o.evidencias.forEach(ev => {
            const fileBtn = ev.ficheiro ? `<button class="btn-icon" onclick="event.stopPropagation();previewEvidencia(${ev.id},'${ev.tipo_evidencia?.nome||''}','${(ev.descricao||'').replace(/'/g,"\\'")}')" title="Ver"><i class='bx bx-show'></i></button>` : '-';
            h += `<div class="tbl-row" onclick="viewEvidencia(${ev.id})" style="cursor:pointer;"><div class="col c1"><strong>${ev.codigo}</strong></div><div class="col c1">${ev.tipo_evidencia?.nome || '-'}</div><div class="col c2">${ev.descricao}</div><div class="col c1">${bGen(ev.estado)}</div><div class="col c1">${fileBtn}</div></div>`;
        });
        h += '</div>';
    } else { h += '<p class="text-muted">Nenhuma evidencia.</p>'; }
    h += '</div>';

    // Detencoes
    if (o.detencoes?.length) {
        h += '<div class="detail-sect"><h4>Detencoes (' + o.detencoes.length + ')</h4><div class="tbl" style="margin-top:0;"><div class="tbl-head"><div class="col c1">Numero</div><div class="col c2">Detido</div><div class="col c1">Data</div><div class="col c1">Estado</div></div>';
        o.detencoes.forEach(d => h += `<div class="tbl-row"><div class="col c1">${d.numero_detencao}</div><div class="col c2">${d.pessoa?.nome || '-'}</div><div class="col c1">${fDT(d.data_detencao)}</div><div class="col c1">${bGen(d.estado?.nome)}</div></div>`);
        h += '</div></div>';
    }

    // Investigacoes
    if (o.investigacoes?.length) {
        h += '<div class="detail-sect"><h4>Investigacoes</h4><div class="tbl" style="margin-top:0;"><div class="tbl-head"><div class="col c1">Numero</div><div class="col c2">Investigador</div><div class="col c1">Progresso</div><div class="col c1">Estado</div></div>';
        o.investigacoes.forEach(i => h += `<div class="tbl-row" onclick="viewInvestigacao(${i.id})" style="cursor:pointer;"><div class="col c1">${i.numero_investigacao}</div><div class="col c2">${i.investigador?.nome || '-'}</div><div class="col c1">${i.progresso}%</div><div class="col c1">${bEstadoObj(i.estado)}</div></div>`);
        h += '</div></div>';
    }

    h += '</div>';
    renderMain('ocorrencias', h);
}

// ── ADICIONAR ENVOLVIDO (no main) ──
function formAddEnvolvido(ocId) {
    renderMain('ocorrencias', `
        <div class="page-header"><div><h1 class="page-title">Adicionar Pessoa a Ocorrencia</h1></div>
            <button class="btn-ghost" onclick="viewOcorrencia(${ocId})"><i class='bx bx-arrow-back'></i> Voltar</button>
        </div>
        <div class="form-card">
            <div class="form-row">
                <div class="form-col"><label>Pessoa *</label><div style="display:flex;gap:8px;"><select id="env-pes" style="flex:1;" required></select><button class="btn-ghost btn-sm" onclick="formNovaPessoa(${ocId})">+ Nova Pessoa</button></div></div>
                <div class="form-col"><label>Tipo de Envolvimento *</label>${mkSel('env-tipo', [{ id: 1, nome: 'Suspeito' }, { id: 2, nome: 'Vitima' }, { id: 3, nome: 'Testemunha' }], 'id', 'nome')}</div>
            </div>
            <div class="form-col" style="margin-bottom:14px;"><label>Observacoes</label><textarea id="env-obs" rows="2"></textarea></div>
            <div class="form-actions">
                <button class="btn-ghost" onclick="viewOcorrencia(${ocId})">Cancelar</button>
                <button class="btn-primary" onclick="submitEnvolvido(${ocId})"><i class='bx bx-plus'></i> Adicionar</button>
            </div>
        </div>
    `);
    loadSelPessoas('env-pes');
}

async function loadSelPessoas(id) { const d = await api('/pessoas?per_page=500'); if (d) fillSel(id, d.data || d, 'id', 'nome', 'Selecionar pessoa'); }
async function loadSelOcorrencias(id) { const d = await api('/ocorrencias?per_page=200'); if (d) fillSel(id, d.data || [], 'id', 'numero_ocorrencia', 'Selecionar'); }
async function loadSelAgentes(id, extra = '') { const d = await api('/agentes?estado=activo' + extra); if (d) fillSel(id, d, 'id', 'nome', 'Selecionar agente'); }

async function submitEnvolvido(ocId) {
    limparErros();
    if (validarObrigatorio('env-pes', 'Pessoa') || validarObrigatorio('env-tipo', 'Tipo')) { toast('Preencha os campos obrigatorios.', 'err'); return; }
    showLoad();
    const d = await api('/ocorrencias/' + ocId + '/envolvidos', { method: 'POST', body: JSON.stringify({ pessoa_id: v('env-pes'), tipo_envolvimento_id: v('env-tipo'), descricao: v('env-obs') }) });
    hideLoad();
    if (d?.success) { toast('Pessoa adicionada.', 'ok'); viewOcorrencia(ocId); }
}

// ══════════════════
// PESSOAS
// ══════════════════
async function loadPessoas(page = 1) {
    const p = new URLSearchParams({ page, busca: v('f-pes-busca'), sexo: v('f-pes-sexo'), nacionalidade: v('f-pes-nacionalidade') });
    const d = await api('/pessoas?' + p); if (!d) return;
    const items = d.data || []; const c = document.getElementById('list-pes');
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; return; }
    c.innerHTML = items.map(p => `<div class="tbl-row" onclick="viewPessoa(${p.id})"><div class="col c2"><strong>${p.nome}</strong>${p.alcunha ? ` <small class="text-muted">(${p.alcunha})</small>` : ''}</div><div class="col c1">${p.bi || '-'}</div><div class="col c1">${p.sexo || '-'}</div><div class="col c1">${p.telefone || '-'}</div><div class="col c2">${p.morada || '-'}</div><div class="col c1"><button class="btn-icon"><i class='bx bx-show'></i></button></div></div>`).join('');
    renderPag('pag-pes', d, loadPessoas);
}

async function viewPessoa(id) {
    showLoad(); const p = await api('/pessoas/' + id); hideLoad(); if (!p) return;
    let h = `<div class="page-header"><div><h1 class="page-title">${p.nome}</h1><p class="page-desc">${p.alcunha ? 'Alcunha: ' + p.alcunha : 'Perfil de pessoa'}</p></div>
        <button class="btn-ghost" onclick="voltarPara('pessoas')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="detail-view">
        <div class="detail-sect"><h4>Dados Pessoais</h4>${dl('Nome', p.nome)}${p.alcunha ? dl('Alcunha', p.alcunha) : ''}${dl('BI', p.bi)}${dl('Sexo', p.sexo === 'M' ? 'Masculino' : p.sexo === 'F' ? 'Feminino' : '-')}${dl('Nascimento', fDate(p.data_nascimento))}${dl('Nacionalidade', p.nacionalidade)}${dl('Telefone', p.telefone)}${dl('Morada', p.morada)}${dl('Bairro', p.bairro)}${p.caracteristicas_fisicas ? dl('Caracteristicas', p.caracteristicas_fisicas) : ''}</div>`;
    if (p.envolvimentos?.length) { h += '<div class="detail-sect"><h4>Ocorrencias Associadas (' + p.envolvimentos.length + ')</h4><div class="tbl" style="margin-top:0;"><div class="tbl-head"><div class="col c2">Ocorrencia</div><div class="col c1">Tipo</div><div class="col c2">Crime</div></div>'; p.envolvimentos.forEach(e => h += `<div class="tbl-row" onclick="viewOcorrencia(${e.ocorrencia?.id})"><div class="col c2">${e.ocorrencia?.numero_ocorrencia || '-'}</div><div class="col c1">${bGen(e.tipo_envolvimento?.nome)}</div><div class="col c2">${e.ocorrencia?.tipo_crime?.nome || '-'}</div></div>`); h += '</div></div>'; }
    if (p.detencoes?.length) { h += '<div class="detail-sect"><h4>Detencoes (' + p.detencoes.length + ')</h4>'; p.detencoes.forEach(d => h += dl(d.numero_detencao || '-', d.estado?.nome)); h += '</div>'; }
    h += '</div>';
    renderMain('pessoas', h);
}

function formNovaPessoa(retOcId = null) {
    const backFn = retOcId ? `formAddEnvolvido(${retOcId})` : `voltarPara('pessoas')`;
    renderMain('pessoas', `
        <div class="page-header"><div><h1 class="page-title">Registar Nova Pessoa</h1><p class="page-desc">Preencha os dados pessoais</p></div>
            <button class="btn-ghost" onclick="${backFn}"><i class='bx bx-arrow-back'></i> Voltar</button>
        </div>
        <div class="form-card">
            <div class="form-section">Identificacao</div>
            <div class="form-row">
                <div class="form-col"><label>Nome Completo *</label><input type="text" id="npes-nome"><span class="form-hint"></span></div>
                <div class="form-col"><label>Alcunha</label><input type="text" id="npes-alcunha"></div>
            </div>
            <div class="form-row">
                <div class="form-col"><label>Bilhete de Identidade</label><input type="text" id="npes-bi" placeholder="Ex: 0012345678LA042"><span class="form-hint">Digite o numero do BI válido</span></div>
                <div class="form-col"><label>Sexo</label>${mkOpts('npes-sexo', [{ v: 'M', t: 'Masculino' }, { v: 'F', t: 'Feminino' }], false)}</div>
            </div>
            <div class="form-row">
                <div class="form-col"><label>Data de Nascimento</label><input type="date" id="npes-nasc" max="${today()}"><span class="form-hint"></span></div>
                <div class="form-col"><label>Nacionalidade</label><input type="text" id="npes-nac" value="Angolana"></div>
            </div>
            <div class="form-section">Contacto</div>
            <div class="form-row">
                <div class="form-col"><label>Telefone</label><input type="text" id="npes-tel" placeholder="9XXXXXXXX"></div>
                <div class="form-col"><label>Bairro</label><input type="text" id="npes-bairro"></div>
            </div>
            <div class="form-col" style="margin-bottom:14px;"><label>Morada</label><input type="text" id="npes-morada"></div>
            <div class="form-section">Informacoes Adicionais</div>
            <div class="form-col" style="margin-bottom:14px;"><label>Caracteristicas Fisicas</label><textarea id="npes-car" rows="2" placeholder="Altura, peso, marcas, tatuagens..."></textarea></div>
            <div class="form-col" style="margin-bottom:14px;"><label>Observacoes</label><textarea id="npes-obs" rows="2"></textarea></div>
            <div class="form-actions">
                <button class="btn-ghost" onclick="${backFn}">Cancelar</button>
                <button class="btn-primary" onclick="submitNovaPessoa(${retOcId || 'null'})"><i class='bx bx-save'></i> Registar Pessoa</button>
            </div>
        </div>
    `);
}

async function submitNovaPessoa(retOcId) {
    limparErros();
    let erros = [];
    erros.push(validarObrigatorio('npes-nome', 'Nome'));
    erros.push(validarCampo('npes-nome', validarNome));
    erros.push(validarCampo('npes-bi', validarBI));
    erros.push(validarCampo('npes-nasc', validarDataNaoFutura));
    erros.push(validarCampo('npes-tel', validarTelefone));
    erros = erros.filter(e => e !== null);
    if (erros.length) { toast('Corrija os erros assinalados.', 'err'); return; }

    // Aviso de menor de idade (não bloqueante)
    const avisoIdade = validarIdadeMenor(v('npes-nasc'));
    if (avisoIdade) toast(avisoIdade, 'warn');

    showLoad();
    const d = await api('/pessoas', { method: 'POST', body: JSON.stringify({ nome: v('npes-nome'), alcunha: v('npes-alcunha'), bi: v('npes-bi') || null, sexo: v('npes-sexo') || null, data_nascimento: v('npes-nasc') || null, nacionalidade: v('npes-nac'), telefone: v('npes-tel') || null, bairro: v('npes-bairro') || null, morada: v('npes-morada') || null, caracteristicas_fisicas: v('npes-car') || null, observacoes: v('npes-obs') || null }) });
    hideLoad();
    if (d?.success) { toast('Pessoa registada.', 'ok'); if (retOcId) formAddEnvolvido(retOcId); else voltarPara('pessoas'); }
}

// ══════════════════
// DETENCOES
// ══════════════════
async function loadDetencoes(page = 1) {
    const p = new URLSearchParams({ page, estado_id: v('f-det-estado'), data_inicio: v('f-det-di'), data_fim: v('f-det-df'), busca: v('f-det-busca'), unidade_id: v('f-det-unidade') });
    const d = await api('/detencoes?' + p); if (!d) return;
    const items = d.data || []; const c = document.getElementById('list-det');
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem detenções.</div>'; return; }
    c.innerHTML = items.map(dt => `<div class="tbl-row" onclick="viewDetencao(${dt.id})"><div class="col c2"><strong>${dt.numero_detencao}</strong></div><div class="col c2">${dt.pessoa?.nome || '-'}</div><div class="col c2">${dt.ocorrencia?.numero_ocorrencia || '-'}</div><div class="col c1">${fDT(dt.data_detencao)}</div><div class="col c1">${bGen(dt.estado?.nome)}</div><div class="col c1"><button class="btn-icon" onclick="event.stopPropagation();viewDetencao(${dt.id})"><i class='bx bx-show'></i></button></div></div>`).join('');
    renderPag('pag-det', d, loadDetencoes);
}
async function viewDetencao(id) {
    showLoad(); const dt = await api('/detencoes/' + id); hideLoad(); if (!dt) return;
    let h = `<div class="page-header"><div><h1 class="page-title">Detenção ${dt.numero_detencao}</h1><p class="page-desc">Detido em ${fDT(dt.data_detencao)}</p></div>
        <button class="btn-ghost" onclick="voltarPara('detencoes')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
        <div class="detail-view">
        <div class="detail-sect"><h4>Dados da Detenção</h4>
            ${dl('Número', dt.numero_detencao)}${dl('Data', fDT(dt.data_detencao))}${dl('Local', dt.local_detencao)}
            ${dl('Motivo', dt.motivo)}${dl('Estado', bGen(dt.estado?.nome))}${dl('Observações', dt.observacoes)}
        </div>
        <div class="detail-sect"><h4>Detido</h4>
            ${dl('Nome', dt.pessoa?.nome)}${dl('BI', dt.pessoa?.bi)}${dl('Sexo', dt.pessoa?.sexo === 'M' ? 'Masculino' : dt.pessoa?.sexo === 'F' ? 'Feminino' : '-')}
            ${dl('Telefone', dt.pessoa?.telefone)}${dl('Morada', dt.pessoa?.morada)}
        </div>
        <div class="detail-sect"><h4>Ocorrência Associada</h4>
            ${dl('Número', dt.ocorrencia?.numero_ocorrencia)}${dl('Tipo', dt.ocorrencia?.tipo_crime?.nome)}
            <button class="link-btn" onclick="viewOcorrencia(${dt.ocorrencia?.id})">Ver Ocorrência Completa</button>
        </div>
        <div class="detail-sect"><h4>Agente Responsável</h4>
            ${dl('Nome', dt.agente_responsavel?.nome)}${dl('Unidade', dt.unidade?.nome)}
        </div>
    </div>`;
    renderMain('detencoes', h);
}

function formNovaDetencao() {
    renderMain('detencoes', `
        <div class="page-header"><div><h1 class="page-title">Registar Nova Detencao</h1></div><button class="btn-ghost" onclick="voltarPara('detencoes')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
        <div class="form-card">
            <div class="form-section">Dados da Detencao</div>
            <div class="form-row">
                <div class="form-col"><label>Pessoa (detido) *</label><div style="display:flex;gap:8px;"><select id="ndet-pes" style="flex:1;" required></select><button class="btn-ghost btn-sm" onclick="formNovaPessoa()">+ Nova</button></div></div>
                <div class="form-col"><label>Ocorrencia Associada *</label><select id="ndet-oc" required></select></div>
            </div>
            <div class="form-row">
                <div class="form-col"><label>Data e Hora *</label><input type="datetime-local" id="ndet-data" value="${nowLocal()}" max="${nowLocal()}"></div>
                <div class="form-col"><label>Local *</label><input type="text" id="ndet-local" required></div>
            </div>
            <div class="form-col" style="margin-bottom:14px;"><label>Motivo *</label><textarea id="ndet-motivo" rows="3" required></textarea></div>
            <div class="form-col" style="margin-bottom:14px;"><label>Observacoes</label><textarea id="ndet-obs" rows="2"></textarea></div>
            <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('detencoes')">Cancelar</button><button class="btn-danger" onclick="submitNovaDetencao()"><i class='bx bx-lock-alt'></i> Registar Detencao</button></div>
        </div>
    `);
    loadSelPessoas('ndet-pes'); loadSelOcorrencias('ndet-oc');
}

async function submitNovaDetencao() {
    limparErros();
    let e = [validarObrigatorio('ndet-pes', 'Pessoa'), validarObrigatorio('ndet-oc', 'Ocorrencia'), validarObrigatorio('ndet-data', 'Data'), validarObrigatorio('ndet-local', 'Local'), validarObrigatorio('ndet-motivo', 'Motivo')].filter(x => x);
    // Validar data não futura
    const dataDetVal = v('ndet-data');
    if (dataDetVal && new Date(dataDetVal) > new Date()) { e.push('Data da detenção não pode ser no futuro.'); toast('Data da detenção não pode ser no futuro.', 'err'); }
    if (e.length) { toast('Corrija os erros.', 'err'); return; }
    showLoad(); const d = await api('/detencoes', { method: 'POST', body: JSON.stringify({ pessoa_id: v('ndet-pes'), ocorrencia_id: v('ndet-oc'), data_detencao: v('ndet-data'), local_detencao: v('ndet-local'), motivo: v('ndet-motivo'), observacoes: v('ndet-obs') }) }); hideLoad();
    if (d?.success) { toast('Detencao registada: ' + d.detencao.numero_detencao, 'ok'); voltarPara('detencoes'); }
}

// ══════════════════
// EVIDENCIAS / INVESTIGACOES / DESPACHOS / PATRULHAS / ALERTAS / VIATURAS / ARMAMENTO / MENSAGENS
// (listagem funciona igual, formularios no main)
// ══════════════════
let evTipoActual = 'todos';
async function loadEvidencias(page = 1, tipo) { if (tipo !== undefined) evTipoActual = tipo; const p = new URLSearchParams({ page, busca: v('f-ev-busca'), estado: v('f-ev-estado') }); if (evTipoActual && evTipoActual !== 'todos') p.append('tipo_evidencia_id', evTipoActual); const d = await api('/evidencias?' + p); if (!d) return; const items = d.data || []; const c = document.getElementById('list-ev'); const icos = { 1: 'bx-image', 2: 'bx-video', 3: 'bx-file', 4: 'bx-microphone', 5: 'bx-box' }; if (!items.length) { c.innerHTML = '<div class="tbl-empty" style="grid-column:1/-1;">Sem evidências.</div>'; renderPag('pag-ev', d, loadEvidencias); return; } c.innerHTML = items.map(e => {
    const ico = icos[e.tipo_evidencia_id] || 'bx-file';
    const hasFile = !!e.ficheiro;
    return `<div class="ev-card" onclick="viewEvidencia(${e.id})"><div class="ev-card-thumb"><i class='bx ${ico}'></i></div><div class="ev-card-body"><strong>${e.codigo}</strong><div class="ev-card-desc">${e.descricao}</div><div class="ev-card-meta"><span>${e.tipo_evidencia?.nome || ''}</span><span>${bGen(e.estado)}</span></div>${hasFile ? `<div style="margin-top:6px;"><button class="btn-ghost btn-sm" onclick="event.stopPropagation();previewEvidencia(${e.id},'${e.tipo_evidencia?.nome||''}','${(e.descricao||'').replace(/'/g,"\\'")}')"><i class='bx bx-show'></i> Ver ficheiro</button></div>` : ''}</div></div>`;
}).join(''); renderPag('pag-ev', d, loadEvidencias); }
function filtEv(tipo, ev) { if (ev) { ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t => t.classList.remove('active')); ev.target.classList.add('active'); } loadEvidencias(1, tipo); }
async function viewEvidencia(id) {
    showLoad(); const e = await api('/evidencias/' + id); hideLoad(); if (!e) return;
    let h = `<div class="page-header"><div><h1 class="page-title">Evidência ${e.codigo}</h1><p class="page-desc">${e.tipo_evidencia?.nome || ''}</p></div>
        <div style="display:flex;gap:8px;">
            ${e.ficheiro ? `<button class="btn-primary btn-sm" onclick="previewEvidencia(${e.id},'${e.tipo_evidencia?.nome||''}','${(e.descricao||'').replace(/'/g,"\\'")}')"><i class='bx bx-show'></i> Visualizar</button>` : ''}
            ${e.ficheiro ? `<a href="/api/evidencias/${e.id}/ficheiro" download class="btn-ghost btn-sm"><i class='bx bx-download'></i> Descarregar</a>` : ''}
            <button class="btn-ghost" onclick="voltarPara('evidencias')"><i class='bx bx-arrow-back'></i> Voltar</button>
        </div></div>
        <div class="grid-2">
        <div class="card"><div class="card-head"><h3>Dados da Evidência</h3></div><div class="card-body">
            ${dl('Código', e.codigo)}${dl('Tipo', e.tipo_evidencia?.nome)}${dl('Descrição', e.descricao)}
            ${dl('Estado', bGen(e.estado))}${dl('Localização Física', e.localizacao_fisica)}
            ${dl('Registado por', e.agente_registo?.nome)}
            ${e.hash_ficheiro ? dl('Hash SHA-256', '<code style="font-size:10px;">' + e.hash_ficheiro.substring(0,16) + '...</code>') : ''}
            ${e.tamanho_ficheiro ? dl('Tamanho', (e.tamanho_ficheiro / 1024 / 1024).toFixed(2) + ' MB') : ''}
        </div></div>
        <div class="card"><div class="card-head"><h3>Ocorrência Associada</h3></div><div class="card-body">
            ${dl('Número', e.ocorrencia?.numero_ocorrencia)}${dl('Tipo', e.ocorrencia?.tipo_crime?.nome)}${dl('Unidade', e.ocorrencia?.unidade?.nome)}
            <button class="link-btn" onclick="viewOcorrencia(${e.ocorrencia?.id})">Ver Ocorrência</button>
        </div></div></div>`;
    if (e.cadeia_custodia?.length) {
        h += '<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Cadeia de Custódia</h3></div><div class="tbl"><div class="tbl-head"><div class="col c2">De</div><div class="col c2">Para</div><div class="col c2">Local</div><div class="col c1">Data</div></div>';
        e.cadeia_custodia.forEach(cc => h += `<div class="tbl-row"><div class="col c2">${cc.agente_origem?.nome || '-'}</div><div class="col c2">${cc.agente_destino?.nome || '-'}</div><div class="col c2">${cc.local_destino || '-'}</div><div class="col c1">${fDT(cc.data_transferencia)}</div></div>`);
        h += '</div></div>';
    }
    renderMain('evidencias', h);
}

async function loadInvestigacoes(page = 1) { const p = new URLSearchParams({ page, estado_id: v('f-inv-estado'), busca: v('f-inv-busca'), data_inicio: v('f-inv-di'), data_fim: v('f-inv-df') }); const un = v('f-inv-unidade'); if (un) p.append('unidade_id', un); const d = await api('/investigacoes?' + p); if (!d) return; const items = d.data || []; const c = document.getElementById('list-inv'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; renderPag('pag-inv', d, loadInvestigacoes); return; } c.innerHTML = items.map(i => `<div class="tbl-row" onclick="viewInvestigacao(${i.id})" style="cursor:pointer;"><div class="col c2"><strong>${i.numero_investigacao}</strong></div><div class="col c1">${i.ocorrencia?.numero_ocorrencia || '-'}</div><div class="col c2">${i.investigador?.nome || '-'}</div><div class="col c2"><span style="font-size:11px;">${i.progresso}%</span><div class="progress-track"><div class="progress-fill" style="width:${i.progresso}%"></div></div></div><div class="col c1">${bEstadoObj(i.estado)}</div><div class="col c1"><button class="btn-icon" onclick="event.stopPropagation();exportPdfInvestigacao(${i.id})" title="PDF"><i class='bx bx-download'></i></button></div></div>`).join(''); renderPag('pag-inv', d, loadInvestigacoes); }

async function loadDespachos() { const d = await api('/despachos?estado=' + v('f-desp-estado')); if (!d) return; const items = d.data || []; const c = document.getElementById('list-desp'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; return; } c.innerHTML = items.map(dp => `<div class="tbl-row"><div class="col c2">${dp.ocorrencia?.numero_ocorrencia || '-'}</div><div class="col c1">${bPrio(dp.prioridade)}</div><div class="col c2">${dp.agente_destino?.nome || '-'}</div><div class="col c1">${dp.unidade?.nome || '-'}</div><div class="col c1">${bGen(dp.estado)}</div><div class="col c1">${fDT(dp.data_despacho)}</div><div class="col c1">${dp.estado === 'pendente' ? `<button class="btn-primary btn-sm" onclick="respDesp(${dp.id})">Aceitar</button>` : ''}</div></div>`).join(''); }
async function respDesp(id) { const d = await api(`/despachos/${id}/responder`, { method: 'PATCH', body: JSON.stringify({ estado: 'aceite' }) }); if (d?.success) { toast('Despacho aceite.', 'ok'); loadDespachos(); } }

async function loadPatrulhas() { const pp = new URLSearchParams({ data: v('f-pat-data'), estado: v('f-pat-estado'), unidade_id: v('f-pat-unidade') }); const d = await api('/patrulhas?' + pp); if (!d) return; const items = d.data || []; const c = document.getElementById('list-pat'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; return; } c.innerHTML = items.map(p => `<div class="tbl-row"><div class="col c1">${fDate(p.data)}</div><div class="col c1">${p.turno?.nome || '-'}</div><div class="col c2">${p.zona?.nome || '-'}</div><div class="col c2">${p.agente_lider?.nome || '-'}</div><div class="col c1">${p.viatura?.matricula || '-'}</div><div class="col c1">${bGen(p.estado)}</div><div class="col c1">${p.estado === 'planeada' ? `<button class="btn-primary btn-sm" onclick="patEst(${p.id},'em_curso')">Iniciar</button>` : p.estado === 'em_curso' ? `<button class="btn-ghost btn-sm" onclick="patEst(${p.id},'concluida')">Concluir</button>` : ''}</div></div>`).join(''); }
async function patEst(id, est) { const d = await api(`/patrulhas/${id}/estado`, { method: 'PATCH', body: JSON.stringify({ estado: est }) }); if (d?.success) { toast('Estado actualizado.', 'ok'); loadPatrulhas(); } }

async function loadAlertas(estado, ev) { if (ev) { ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t => t.classList.remove('active')); ev.target.classList.add('active'); } const d = await api('/alertas?estado=' + estado); if (!d) return; const items = d.data || []; const c = document.getElementById('list-alertas'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem alertas.</div>'; return; } c.innerHTML = items.map(a => `<div class="alert-card ${a.prioridade}"><div class="alert-ico"><i class='bx ${a.tipo_alerta?.icone || 'bx-bell-ring'}'></i></div><div class="alert-info"><h4>${a.titulo}</h4><p>${a.descricao.substring(0, 200)}${a.descricao.length > 200 ? '...' : ''}</p><div class="alert-meta">${bPrio(a.prioridade)} - ${a.tipo_alerta?.nome || ''} - ${fDT(a.created_at)}</div></div><div>${a.estado === 'activo' ? `<button class="btn-success btn-sm" onclick="resolveAlerta(${a.id})">Resolver</button>` : `<span class="badge badge-gray">${a.estado}</span>`}</div></div>`).join(''); }
async function resolveAlerta(id) { const d = await api(`/alertas/${id}/resolver`, { method: 'PATCH' }); if (d?.success) { toast('Alerta resolvido.', 'ok'); loadAlertas('activo'); loadDashboard(); } }

async function loadViaturas(page = 1) { const p = new URLSearchParams({ page, busca: v('f-viat-busca'), estado: v('f-viat-estado'), unidade_id: v('f-viat-unidade') }); const d = await api('/viaturas?' + p); if (!d) return; const items = d.data || []; const c = document.getElementById('list-viat'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; return; } c.innerHTML = items.map(vi => `<div class="tbl-row" onclick="viewViatura(${vi.id})"><div class="col c1"><strong>${vi.matricula}</strong></div><div class="col c2">${vi.marca} ${vi.modelo}${vi.cor ? ' (' + vi.cor + ')' : ''}</div><div class="col c2">${vi.unidade?.nome || '-'}</div><div class="col c1">${(vi.quilometragem || 0).toLocaleString()} km</div><div class="col c1"><span class="badge badge-${vi.estado === 'disponivel' ? 'green' : vi.estado === 'em_uso' ? 'blue' : 'orange'}">${vi.estado || '-'}</span></div><div class="col c1"><button class="btn-icon" onclick="event.stopPropagation();viewViatura(${vi.id})"><i class='bx bx-show'></i></button></div></div>`).join(''); renderPag('pag-viat', d, loadViaturas); }
async function viewViatura(id) {
    showLoad(); const vi = await api('/viaturas/' + id); hideLoad(); if (!vi) return;
    let h = `<div class="page-header"><div><h1 class="page-title">${vi.matricula}</h1><p class="page-desc">${vi.marca} ${vi.modelo}</p></div>
        <button class="btn-ghost" onclick="voltarPara('viaturas')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
        <div class="detail-view"><div class="detail-sect"><h4>Dados da Viatura</h4>
            ${dl('Matrícula', vi.matricula)}${dl('Marca', vi.marca)}${dl('Modelo', vi.modelo)}${dl('Ano', vi.ano)}${dl('Cor', vi.cor)}
            ${dl('Quilometragem', (vi.quilometragem || 0).toLocaleString() + ' km')}${dl('Estado', vi.estado)}${dl('Unidade', vi.unidade?.nome)}
        </div>`;
    if (vi.atribuicoes?.length) {
        h += '<div class="detail-sect"><h4>Histórico de Atribuições</h4><div class="tbl" style="margin-top:0;"><div class="tbl-head"><div class="col c2">Agente</div><div class="col c1">Saída</div><div class="col c1">Retorno</div><div class="col c1">Km Saída</div><div class="col c1">Km Retorno</div></div>';
        vi.atribuicoes.forEach(at => h += `<div class="tbl-row"><div class="col c2">${at.agente?.nome || '-'}</div><div class="col c1">${fDT(at.data_saida)}</div><div class="col c1">${at.data_retorno ? fDT(at.data_retorno) : '<span class="badge badge-blue">Em uso</span>'}</div><div class="col c1">${at.quilometragem_saida || '-'}</div><div class="col c1">${at.quilometragem_retorno || '-'}</div></div>`);
        h += '</div></div>';
    }
    h += '</div>';
    renderMain('viaturas', h);
}

async function loadArmamento(page = 1) { const p = new URLSearchParams({ page, busca: v('f-arm-busca'), estado: v('f-arm-estado'), tipo_armamento_id: v('f-arm-tipo'), unidade_id: v('f-arm-unidade') }); const d = await api('/armamento?' + p); if (!d) return; const items = d.data || []; const c = document.getElementById('list-arm'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; return; } c.innerHTML = items.map(a => `<div class="tbl-row" onclick="viewArmamento(${a.id})"><div class="col c1"><strong>${a.numero_serie}</strong></div><div class="col c1">${a.tipo_armamento?.nome || '-'}</div><div class="col c1">${a.marca || '-'}</div><div class="col c1">${a.calibre || '-'}</div><div class="col c2">${a.unidade?.nome || '-'}</div><div class="col c2">${a.atribuicao_actual?.agente?.nome || '<span class="text-muted">Disponível</span>'}</div><div class="col c1"><span class="badge badge-${a.estado === 'disponivel' ? 'green' : a.estado === 'atribuido' ? 'blue' : 'orange'}">${a.estado || '-'}</span></div><div class="col c1"><button class="btn-icon" onclick="event.stopPropagation();viewArmamento(${a.id})"><i class='bx bx-show'></i></button></div></div>`).join(''); renderPag('pag-arm', d, loadArmamento); }
async function viewArmamento(id) {
    showLoad(); const a = await api('/armamento/' + id); hideLoad(); if (!a) return;
    let h = `<div class="page-header"><div><h1 class="page-title">${a.numero_serie}</h1><p class="page-desc">${a.tipo_armamento?.nome || ''} - ${a.marca || ''} ${a.modelo || ''}</p></div>
        <button class="btn-ghost" onclick="voltarPara('armamento')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
        <div class="detail-view"><div class="detail-sect"><h4>Dados do Armamento</h4>
            ${dl('Nº Série', a.numero_serie)}${dl('Tipo', a.tipo_armamento?.nome)}${dl('Marca', a.marca)}${dl('Modelo', a.modelo)}
            ${dl('Calibre', a.calibre)}${dl('Estado', a.estado)}${dl('Unidade', a.unidade?.nome)}
            ${a.atribuicao_actual?.agente ? dl('Atribuído a', a.atribuicao_actual.agente.nome) : ''}
        </div>`;
    if (a.atribuicoes?.length) {
        h += '<div class="detail-sect"><h4>Histórico de Atribuições</h4><div class="tbl" style="margin-top:0;"><div class="tbl-head"><div class="col c2">Agente</div><div class="col c1">Atribuição</div><div class="col c1">Devolução</div><div class="col c1">Estado</div></div>';
        a.atribuicoes.forEach(at => h += `<div class="tbl-row"><div class="col c2">${at.agente?.nome || '-'}</div><div class="col c1">${fDT(at.data_atribuicao)}</div><div class="col c1">${at.data_devolucao ? fDT(at.data_devolucao) : '-'}</div><div class="col c1">${bGen(at.estado)}</div></div>`);
        h += '</div></div>';
    }
    h += '</div>';
    renderMain('armamento', h);
}

async function loadMensagens(tipo, ev) { if (ev) { ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t => t.classList.remove('active')); ev.target.classList.add('active'); } const d = await api('/mensagens/' + (tipo === 'inbox' ? 'inbox' : 'enviadas')); if (!d) return; const items = d.data || []; const c = document.getElementById('list-msg'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem mensagens.</div>'; return; } c.innerHTML = items.map(m => `<div class="tbl-row" style="${!m.lida ? 'font-weight:600;background:var(--navy-light);' : ''}"><div class="col c0">${!m.lida ? '<i class="bx bxs-circle" style="color:var(--navy);font-size:7px;"></i>' : ''}</div><div class="col c2">${tipo === 'inbox' ? (m.remetente?.nome || '-') : (m.destinatario?.nome || '-')}</div><div class="col c3">${m.titulo}</div><div class="col c1">${m.prioridade === 'urgente' ? '<span class="badge badge-red">Urgente</span>' : '<span class="badge badge-gray">Normal</span>'}</div><div class="col c1">${fDT(m.created_at)}</div></div>`).join(''); }

async function loadRelatorios() { loadRelatoriosAnteriores(); }
async function loadRelatoriosAnteriores() {
    const p = new URLSearchParams({ busca: v('f-rel-busca') });
    const d = await api('/relatorios?' + p);
    if (!d) return;
    const items = d.data || [];
    const c = document.getElementById('list-rel');
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem relatórios.</div>'; return; }
    c.innerHTML = items.map(r => `<div class="tbl-row">
        <div class="col c2">${r.tipo_relatorio?.nome || '-'}</div>
        <div class="col c2">${fDate(r.periodo_inicio)} - ${fDate(r.periodo_fim)}</div>
        <div class="col c2">${r.unidade?.nome || 'Todas'}</div>
        <div class="col c1">${fDate(r.created_at)}</div>
        <div class="col c1">
            <button class="btn-icon" onclick="viewRelatorio(${r.id})" title="Ver detalhes"><i class='bx bx-show'></i></button>
            <button class="btn-icon" onclick="exportPdfRelatorioAnterior('${r.periodo_inicio}', '${r.periodo_fim}', '${r.unidade_id || ''}')" title="Exportar PDF"><i class='bx bx-download'></i></button>
        </div>
    </div>`).join('');
}

// ══════════════════
// RELATORIOS — GERAÇÃO E VISUALIZAÇÃO
// ══════════════════

// Renderizar gráfico de barras simples em HTML/CSS
function renderBarChart(containerId, items, labelKey, valueKey, color) {
    const el = document.getElementById(containerId);
    if (!el || !items || !items.length) { if (el) el.innerHTML = '<div class="tbl-empty">Sem dados.</div>'; return; }
    const max = Math.max(...items.map(i => i[valueKey] || 0));
    el.innerHTML = items.map(i => {
        const pct = max > 0 ? ((i[valueKey] || 0) / max * 100) : 0;
        return `<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <span style="min-width:140px;font-size:13px;text-align:right;color:var(--text-secondary)">${i[labelKey] || '-'}</span>
            <div style="flex:1;background:var(--bg-hover);border-radius:4px;height:22px;overflow:hidden;">
                <div style="width:${pct}%;height:100%;background:${color || 'var(--navy)'};border-radius:4px;transition:width .4s;"></div>
            </div>
            <span style="min-width:35px;font-size:13px;font-weight:600;">${i[valueKey] || 0}</span>
        </div>`;
    }).join('');
}

// Nomes dos meses
const nomesMes = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

// Renderizar stats cards genéricos
function renderRelStats(cards) {
    return cards.map(c => `<div class="stat-card">
        <div class="stat-icon ${c.cor}"><i class='bx ${c.icon}'></i></div>
        <div><span class="stat-value">${c.valor}</span><span class="stat-label">${c.label}</span></div>
    </div>`).join('');
}

// Renderizar conteúdo do relatório conforme tipo
function renderRelatorioConteudo(dt, tipoId) {
    let statsHtml = '';
    let chartsHtml = '';

    if (tipoId === 1 || tipoId === 5) {
        // Criminalidade ou Estatístico
        const cards = [
            { icon: 'bx-file', cor: 'blue', valor: dt.total_ocorrencias, label: 'Ocorrências' },
            { icon: 'bx-check-circle', cor: 'green', valor: dt.ocorrencias_resolvidas, label: 'Resolvidas' },
            { icon: 'bx-error-circle', cor: 'orange', valor: dt.ocorrencias_abertas || 0, label: 'Abertas' },
            { icon: 'bx-trending-up', cor: 'blue', valor: (dt.taxa_resolucao || 0) + '%', label: 'Taxa Resolução' },
            { icon: 'bx-lock-alt', cor: 'red', valor: dt.total_detencoes, label: 'Detenções' },
        ];
        if (tipoId === 5) {
            cards.push({ icon: 'bx-search-alt', cor: 'blue', valor: dt.total_investigacoes || 0, label: 'Investigações' });
            cards.push({ icon: 'bx-folder', cor: 'green', valor: dt.total_processos || 0, label: 'Processos' });
            cards.push({ icon: 'bx-car', cor: 'orange', valor: dt.total_patrulhas || 0, label: 'Patrulhas' });
        }
        statsHtml = renderRelStats(cards);
        chartsHtml = `<div class="form-grid" style="grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Crimes por Tipo</h4><div id="chart-crimes-tipo"></div></div>
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Crimes por Bairro</h4><div id="chart-crimes-bairro"></div></div>
        </div>
        <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Por Prioridade</h4><div id="chart-crimes-prio"></div></div>
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Por Mês</h4><div id="chart-crimes-mes"></div></div>
        </div>`;
    } else if (tipoId === 2) {
        // Detenções
        statsHtml = renderRelStats([
            { icon: 'bx-lock-alt', cor: 'red', valor: dt.total_detencoes, label: 'Total Detenções' },
            { icon: 'bx-log-out', cor: 'green', valor: dt.libertados || 0, label: 'Libertados' },
            { icon: 'bx-shield', cor: 'orange', valor: dt.em_custodia || 0, label: 'Em Custódia' },
            { icon: 'bx-building-house', cor: 'blue', valor: dt.apresentados_tribunal || 0, label: 'Tribunal' },
        ]);
        chartsHtml = `<div class="form-grid" style="grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Detenções por Unidade</h4><div id="chart-det-unidade"></div></div>
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Top Crimes Associados</h4><div id="chart-det-crimes"></div></div>
        </div>
        <div style="max-width:50%;margin-top:20px;">
            <h4 style="margin-bottom:10px;color:var(--text-primary)">Detenções por Mês</h4><div id="chart-det-mes"></div>
        </div>`;
    } else if (tipoId === 3) {
        // Patrulhas
        statsHtml = renderRelStats([
            { icon: 'bx-car', cor: 'blue', valor: dt.total_patrulhas, label: 'Total Patrulhas' },
            { icon: 'bx-check-circle', cor: 'green', valor: dt.concluidas || 0, label: 'Concluídas' },
            { icon: 'bx-run', cor: 'orange', valor: dt.em_curso || 0, label: 'Em Curso' },
            { icon: 'bx-error', cor: 'red', valor: dt.total_incidentes || 0, label: 'Incidentes' },
        ]);
        chartsHtml = `<div class="form-grid" style="grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Patrulhas por Zona</h4><div id="chart-pat-zona"></div></div>
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Patrulhas por Turno</h4><div id="chart-pat-turno"></div></div>
        </div>`;
    } else if (tipoId === 4) {
        // Desempenho
        statsHtml = renderRelStats([
            { icon: 'bx-file', cor: 'blue', valor: dt.total_ocorrencias, label: 'Total Ocorrências' },
            { icon: 'bx-check-circle', cor: 'green', valor: dt.ocorrencias_resolvidas, label: 'Resolvidas' },
            { icon: 'bx-trending-up', cor: 'blue', valor: (dt.taxa_resolucao_global || 0) + '%', label: 'Taxa Global' },
            { icon: 'bx-lock-alt', cor: 'red', valor: dt.total_detencoes, label: 'Detenções' },
        ]);
        chartsHtml = `<div class="form-grid" style="grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Taxa Resolução por Unidade</h4><div id="chart-desemp-unid"></div></div>
            <div><h4 style="margin-bottom:10px;color:var(--text-primary)">Agentes Mais Activos</h4><div id="chart-desemp-agentes"></div></div>
        </div>
        <div style="max-width:50%;margin-top:20px;">
            <h4 style="margin-bottom:10px;color:var(--text-primary)">Detenções por Agente</h4><div id="chart-desemp-det-ag"></div>
        </div>`;
    }

    return { statsHtml, chartsHtml };
}

// Popular gráficos após renderizar o HTML
function populateRelCharts(dt, tipoId) {
    if (tipoId === 1 || tipoId === 5) {
        renderBarChart('chart-crimes-tipo', dt.crimes_por_tipo || [], 'tipo', 'total', '#0078d4');
        renderBarChart('chart-crimes-bairro', dt.crimes_por_bairro || [], 'bairro', 'total', '#e8590c');
        renderBarChart('chart-crimes-prio', dt.crimes_por_prioridade || [], 'prioridade', 'total', '#6f42c1');
        const mesList = (dt.crimes_por_mes || []).map(m => ({ mes: nomesMes[m.mes] || m.mes, total: m.total }));
        renderBarChart('chart-crimes-mes', mesList, 'mes', 'total', '#28a745');
    } else if (tipoId === 2) {
        renderBarChart('chart-det-unidade', dt.detencoes_por_unidade || [], 'unidade', 'total', '#e8590c');
        renderBarChart('chart-det-crimes', dt.top_crimes_associados || [], 'tipo', 'total', '#0078d4');
        const mesList = (dt.detencoes_por_mes || []).map(m => ({ mes: nomesMes[m.mes] || m.mes, total: m.total }));
        renderBarChart('chart-det-mes', mesList, 'mes', 'total', '#28a745');
    } else if (tipoId === 3) {
        renderBarChart('chart-pat-zona', dt.patrulhas_por_zona || [], 'zona', 'total', '#0078d4');
        renderBarChart('chart-pat-turno', dt.patrulhas_por_turno || [], 'turno', 'total', '#6f42c1');
    } else if (tipoId === 4) {
        const unidData = (dt.taxa_por_unidade || []).map(u => ({ unidade: u.unidade, total: u.taxa }));
        renderBarChart('chart-desemp-unid', unidData, 'unidade', 'total', '#28a745');
        renderBarChart('chart-desemp-agentes', dt.agentes_mais_activos || [], 'agente', 'total', '#0078d4');
        renderBarChart('chart-desemp-det-ag', dt.detencoes_por_agente || [], 'agente', 'total', '#e8590c');
    }
}

async function gerarRelatorio() {
    const tipo = v('rel-tipo');
    const di = v('rel-di');
    const df = v('rel-df');

    if (!tipo) { toast('Selecione o tipo de relatório.', 'err'); return; }
    if (!di || !df) { toast('Selecione o período (data início e fim).', 'err'); return; }
    if (new Date(di) > new Date(df)) { toast('Data início não pode ser maior que data fim.', 'err'); return; }

    showLoad();
    const d = await api('/relatorios/gerar', {
        method: 'POST',
        body: JSON.stringify({
            tipo_relatorio_id: tipo,
            periodo_inicio: di,
            periodo_fim: df,
            unidade_id: v('rel-unidade') || null,
        })
    });
    hideLoad();

    if (!d?.success) return;

    toast('Relatório gerado com sucesso.', 'ok');

    // Abrir perfil do relatório gerado
    viewRelatorio(d.relatorio.id);
}

// Ver relatório individual (perfil)
async function viewRelatorio(id) {
    showLoad();
    const d = await api('/relatorios/' + id);
    hideLoad();
    if (!d) return;

    const r = d;
    const dt = typeof r.dados === 'string' ? JSON.parse(r.dados) : (r.dados || {});
    const tipoId = r.tipo_relatorio_id;
    const tipoNome = r.tipo_relatorio?.nome || 'Relatório';
    const geradoPorNome = r.gerado_por_rel?.nome || r.gerado_por?.nome || '-';

    const { statsHtml, chartsHtml } = renderRelatorioConteudo(dt, tipoId);

    let h = `<div class="page-header"><div><h1 class="page-title">${tipoNome}</h1>
        <p class="page-desc">Período: ${fDate(r.periodo_inicio)} — ${fDate(r.periodo_fim)} | Unidade: ${r.unidade?.nome || 'Todas'} | Gerado por: ${geradoPorNome} em ${fDT(r.created_at)}</p></div>
        <div style="display:flex;gap:8px;">
            <button class="btn-primary" onclick="exportPdfRelatorioAnterior('${r.periodo_inicio}', '${r.periodo_fim}', '${r.unidade_id || ''}')"><i class='bx bx-download'></i> PDF</button>
            <button class="btn-ghost" onclick="voltarPara('relatorios')"><i class='bx bx-arrow-back'></i> Voltar</button>
        </div>
    </div>
    <div class="stats-grid">${statsHtml}</div>
    <div>${chartsHtml}</div>`;

    renderMain('relatorios', h);

    // Popular gráficos após render
    setTimeout(() => populateRelCharts(dt, tipoId), 50);
}

function exportPdfRelatorioAnterior(di, df, un) {
    const params = new URLSearchParams({ periodo_inicio: di, periodo_fim: df });
    if (un && un !== 'null' && un !== 'undefined') params.append('unidade_id', un);
    window.open('/api/pdf/relatorio-criminalidade?' + params.toString(), '_blank');
}

function exportPdfRelatorio() {
    const di = v('rel-di');
    const df = v('rel-df');
    if (!di || !df) { toast('Primeiro preencha as datas e gere o relatório.', 'err'); return; }
    if (new Date(di) > new Date(df)) { toast('Data início não pode ser maior que data fim.', 'err'); return; }
    const params = new URLSearchParams({ periodo_inicio: di, periodo_fim: df });
    const un = v('rel-unidade');
    if (un) params.append('unidade_id', un);
    window.open('/api/pdf/relatorio-criminalidade?' + params.toString(), '_blank');
}

function exportPdfOcorrencia(id) {
    window.open('/api/pdf/ocorrencia/' + id, '_blank');
}

function exportPdfAgentes() {
    window.open('/api/pdf/agentes?estado=activo', '_blank');
}
// ══════════════════
// FORMULARIOS NO MAIN (Viaturas, Armamento, Alertas, etc.)
// ══════════════════
function formNovaViatura() {
    renderMain('viaturas', `<div class="page-header"><div><h1 class="page-title">Registar Nova Viatura</h1></div><button class="btn-ghost" onclick="voltarPara('viaturas')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados da Viatura</div>
        <div class="form-row"><div class="form-col"><label>Matricula *</label><input type="text" id="nvi-mat" placeholder="LD-00-00-AA" required></div><div class="form-col"><label>Marca *</label><input type="text" id="nvi-marca" required></div></div>
        <div class="form-row"><div class="form-col"><label>Modelo *</label><input type="text" id="nvi-mod" required></div><div class="form-col"><label>Ano</label><input type="number" id="nvi-ano" min="2000" max="${new Date().getFullYear()}"></div></div>
        <div class="form-row"><div class="form-col"><label>Cor</label><input type="text" id="nvi-cor"></div><div class="form-col"><label>Unidade *</label>${mkSel('nvi-un', aux.unidades, 'id', 'nome')}</div></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('viaturas')">Cancelar</button><button class="btn-primary" onclick="submitViatura()"><i class='bx bx-save'></i> Registar</button></div>
    </div>`);
}
async function submitViatura() { limparErros(); let e = [validarObrigatorio('nvi-mat', 'Matricula'), validarObrigatorio('nvi-marca', 'Marca'), validarObrigatorio('nvi-mod', 'Modelo'), validarObrigatorio('nvi-un', 'Unidade')].filter(x => x); if (e.length) { toast('Corrija os erros.', 'err'); return; } showLoad(); const d = await api('/viaturas', { method: 'POST', body: JSON.stringify({ matricula: v('nvi-mat'), marca: v('nvi-marca'), modelo: v('nvi-mod'), ano: v('nvi-ano') || null, cor: v('nvi-cor'), unidade_id: v('nvi-un') }) }); hideLoad(); if (d?.success) { toast('Viatura registada.', 'ok'); voltarPara('viaturas'); } }

function formNovoArmamento() {
    renderMain('armamento', `<div class="page-header"><div><h1 class="page-title">Registar Novo Armamento</h1></div><button class="btn-ghost" onclick="voltarPara('armamento')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados do Armamento</div>
        <div class="form-row"><div class="form-col"><label>Tipo *</label>${mkSel('narm-tipo', aux.tipos_armamento, 'id', 'nome')}</div><div class="form-col"><label>Numero de Serie *</label><input type="text" id="narm-serie" required></div></div>
        <div class="form-row"><div class="form-col"><label>Marca</label><input type="text" id="narm-marca"></div><div class="form-col"><label>Modelo</label><input type="text" id="narm-mod"></div></div>
        <div class="form-row"><div class="form-col"><label>Calibre</label><input type="text" id="narm-cal" placeholder="9mm"></div><div class="form-col"><label>Unidade *</label>${mkSel('narm-un', aux.unidades, 'id', 'nome')}</div></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('armamento')">Cancelar</button><button class="btn-primary" onclick="submitArmamento()"><i class='bx bx-save'></i> Registar</button></div>
    </div>`);
}
async function submitArmamento() { limparErros(); let e = [validarObrigatorio('narm-tipo', 'Tipo'), validarObrigatorio('narm-serie', 'Numero de serie'), validarObrigatorio('narm-un', 'Unidade')].filter(x => x); if (e.length) { toast('Corrija os erros.', 'err'); return; } showLoad(); const d = await api('/armamento', { method: 'POST', body: JSON.stringify({ tipo_armamento_id: v('narm-tipo'), numero_serie: v('narm-serie'), marca: v('narm-marca'), modelo: v('narm-mod'), calibre: v('narm-cal'), unidade_id: v('narm-un') }) }); hideLoad(); if (d?.success) { toast('Armamento registado.', 'ok'); voltarPara('armamento'); } }

function formNovoAlerta() {
    renderMain('alertas', `<div class="page-header"><div><h1 class="page-title">Emitir Alerta</h1><p class="page-desc">O alerta sera enviado para todas as esquadras do municipio</p></div><button class="btn-ghost" onclick="voltarPara('alertas')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados do Alerta</div>
        <div class="form-row"><div class="form-col"><label>Tipo de Alerta *</label>${mkSel('nal-tipo', aux.tipos_alerta, 'id', 'nome')}</div><div class="form-col"><label>Prioridade *</label>${mkOpts('nal-prio', [{ v: 'urgente', t: 'Urgente' }, { v: 'alta', t: 'Alta' }, { v: 'normal', t: 'Normal' }])}</div></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Titulo *</label><input type="text" id="nal-tit" required placeholder="Ex: Procura-se suspeito de homicidio"></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Descricao Detalhada *</label><textarea id="nal-desc" rows="5" required placeholder="Descreva todos os detalhes relevantes..."></textarea></div>
        <div style="background:var(--danger-bg);padding:12px;border-radius:var(--r-sm);margin-bottom:16px;font-size:12px;color:var(--danger);display:flex;align-items:center;gap:8px;"><i class='bx bx-info-circle' style="font-size:16px;"></i> Este alerta sera enviado automaticamente para todas as unidades policiais activas.</div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('alertas')">Cancelar</button><button class="btn-danger" onclick="submitAlerta()"><i class='bx bx-bell'></i> Emitir Alerta</button></div>
    </div>`);
}
async function submitAlerta() { limparErros(); let e = [validarObrigatorio('nal-tipo', 'Tipo'), validarObrigatorio('nal-prio', 'Prioridade'), validarObrigatorio('nal-tit', 'Titulo'), validarObrigatorio('nal-desc', 'Descricao')].filter(x => x); if (e.length) { toast('Corrija os erros.', 'err'); return; } showLoad(); const d = await api('/alertas', { method: 'POST', body: JSON.stringify({ tipo_alerta_id: v('nal-tipo'), prioridade: v('nal-prio'), titulo: v('nal-tit'), descricao: v('nal-desc') }) }); hideLoad(); if (d?.success) { toast('Alerta emitido para todas as unidades.', 'ok'); voltarPara('alertas'); } }

function formNovaInvestigacao() {
    renderMain('investigacoes', `<div class="page-header"><div><h1 class="page-title">Abrir Investigacao</h1></div><button class="btn-ghost" onclick="voltarPara('investigacoes')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados da Investigacao</div>
        <div class="form-row"><div class="form-col"><label>Ocorrencia *</label><select id="ninv-oc" required></select></div><div class="form-col"><label>Investigador *</label><select id="ninv-ag" required></select></div></div>
        <div class="form-row"><div class="form-col"><label>Prazo</label><input type="date" id="ninv-prazo" min="${today()}"></div></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Resumo</label><textarea id="ninv-res" rows="3"></textarea></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('investigacoes')">Cancelar</button><button class="btn-primary" onclick="submitInvestigacao()"><i class='bx bx-search-alt-2'></i> Abrir Investigacao</button></div>
    </div>`);
    loadSelOcorrencias('ninv-oc'); loadSelAgentes('ninv-ag');
}
async function submitInvestigacao() { limparErros(); let e = [validarObrigatorio('ninv-oc', 'Ocorrencia'), validarObrigatorio('ninv-ag', 'Investigador')].filter(x => x); if (e.length) { toast('Corrija os erros.', 'err'); return; } showLoad(); const d = await api('/investigacoes', { method: 'POST', body: JSON.stringify({ ocorrencia_id: v('ninv-oc'), investigador_id: v('ninv-ag'), prazo: v('ninv-prazo') || null, resumo: v('ninv-res') }) }); hideLoad(); if (d?.success) { toast('Investigacao aberta.', 'ok'); voltarPara('investigacoes'); } }

function formNovoDespacho() {
    renderMain('despachos', `<div class="page-header"><div><h1 class="page-title">Novo Despacho</h1></div><button class="btn-ghost" onclick="voltarPara('despachos')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados do Despacho</div>
        <div class="form-row"><div class="form-col"><label>Ocorrencia *</label><select id="ndesp-oc" required></select></div><div class="form-col"><label>Prioridade *</label>${mkOpts('ndesp-prio', [{ v: 'baixa', t: 'Baixa' }, { v: 'media', t: 'Media' }, { v: 'alta', t: 'Alta' }, { v: 'critica', t: 'Critica' }])}</div></div>
        <div class="form-row"><div class="form-col"><label>Agente *</label><select id="ndesp-ag" required></select></div><div class="form-col"><label>Unidade *</label>${mkSel('ndesp-un', aux.unidades, 'id', 'nome')}</div></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Instrucoes</label><textarea id="ndesp-inst" rows="3"></textarea></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('despachos')">Cancelar</button><button class="btn-primary" onclick="submitDespacho()"><i class='bx bx-send'></i> Despachar</button></div>
    </div>`);
    loadSelOcorrencias('ndesp-oc'); loadSelAgentes('ndesp-ag');
    document.getElementById('ndesp-un')?.addEventListener('change', function () { if (this.value) loadSelAgentes('ndesp-ag', '&unidade_id=' + this.value); });
}
async function submitDespacho() { limparErros(); let e = [validarObrigatorio('ndesp-oc', 'Ocorrencia'), validarObrigatorio('ndesp-prio', 'Prioridade'), validarObrigatorio('ndesp-ag', 'Agente'), validarObrigatorio('ndesp-un', 'Unidade')].filter(x => x); if (e.length) { toast('Corrija os erros.', 'err'); return; } showLoad(); const d = await api('/despachos', { method: 'POST', body: JSON.stringify({ ocorrencia_id: v('ndesp-oc'), prioridade: v('ndesp-prio'), despachado_para: v('ndesp-ag'), unidade_destino: v('ndesp-un'), instrucoes: v('ndesp-inst') }) }); hideLoad(); if (d?.success) { toast('Despacho criado.', 'ok'); voltarPara('despachos'); } }

function formNovaMensagem() {
    renderMain('mensagens', `<div class="page-header"><div><h1 class="page-title">Nova Mensagem</h1></div><button class="btn-ghost" onclick="voltarPara('mensagens')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados da Mensagem</div>
        <div class="form-row"><div class="form-col"><label>Destinatario *</label><select id="nmsg-dest" required></select></div><div class="form-col"><label>Prioridade</label>${mkOpts('nmsg-prio', [{ v: 'normal', t: 'Normal' }, { v: 'urgente', t: 'Urgente' }], false)}</div></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Assunto *</label><input type="text" id="nmsg-tit" required></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Mensagem *</label><textarea id="nmsg-corpo" rows="5" required></textarea></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('mensagens')">Cancelar</button><button class="btn-primary" onclick="submitMensagem()"><i class='bx bx-send'></i> Enviar</button></div>
    </div>`);
    loadSelAgentes('nmsg-dest');
}
async function submitMensagem() { limparErros(); let e = [validarObrigatorio('nmsg-dest', 'Destinatario'), validarObrigatorio('nmsg-tit', 'Assunto'), validarObrigatorio('nmsg-corpo', 'Mensagem')].filter(x => x); if (e.length) { toast('Corrija os erros.', 'err'); return; } showLoad(); const d = await api('/mensagens', { method: 'POST', body: JSON.stringify({ destinatario_id: v('nmsg-dest'), titulo: v('nmsg-tit'), mensagem: v('nmsg-corpo'), prioridade: v('nmsg-prio') || 'normal' }) }); hideLoad(); if (d?.success) { toast('Mensagem enviada.', 'ok'); voltarPara('mensagens'); } }

function formNovaPatrulha() {
    renderMain('patrulhas', `<div class="page-header"><div><h1 class="page-title">Planear Patrulha</h1></div><button class="btn-ghost" onclick="voltarPara('patrulhas')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados da Patrulha</div>
        <div class="form-row"><div class="form-col"><label>Data *</label><input type="date" id="npat-data" value="${today()}" required></div><div class="form-col"><label>Turno *</label>${mkSel('npat-turno', aux.turnos, 'id', 'nome')}</div></div>
        <div class="form-row"><div class="form-col"><label>Unidade *</label>${mkSel('npat-un', aux.unidades, 'id', 'nome')}</div><div class="form-col"><label>Zona *</label><select id="npat-zona" required></select></div></div>
        <div class="form-row"><div class="form-col"><label>Lider *</label><select id="npat-lider" required></select></div><div class="form-col"><label>Viatura</label><select id="npat-viat"><option value="">Sem viatura</option></select></div></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Agentes *</label><select id="npat-ags" multiple style="height:100px;" required></select><span class="form-hint">Ctrl+click para selecionar multiplos</span></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('patrulhas')">Cancelar</button><button class="btn-primary" onclick="submitPatrulha()"><i class='bx bx-save'></i> Criar Patrulha</button></div>
    </div>`);
    loadSelAgentes('npat-lider'); loadSelAgentes('npat-ags');
    document.getElementById('npat-un')?.addEventListener('change', async function () { if (!this.value) return; loadSelAgentes('npat-lider', '&unidade_id=' + this.value); loadSelAgentes('npat-ags', '&unidade_id=' + this.value); const vt = await api('/viaturas?unidade_id=' + this.value + '&estado=operacional'); if (vt) fillSel('npat-viat', vt, 'id', 'matricula', 'Sem viatura'); });
}
async function submitPatrulha() { const ags = Array.from(document.getElementById('npat-ags')?.selectedOptions || []).map(o => parseInt(o.value)); if (!ags.length) { toast('Selecione pelo menos um agente.', 'err'); return; } limparErros(); let e = [validarObrigatorio('npat-data', 'Data'), validarObrigatorio('npat-turno', 'Turno'), validarObrigatorio('npat-un', 'Unidade'), validarObrigatorio('npat-zona', 'Zona'), validarObrigatorio('npat-lider', 'Lider')].filter(x => x); if (e.length) { toast('Corrija os erros.', 'err'); return; } showLoad(); const d = await api('/patrulhas', { method: 'POST', body: JSON.stringify({ data: v('npat-data'), turno_id: v('npat-turno'), zona_id: v('npat-zona'), unidade_id: v('npat-un'), agente_lider_id: v('npat-lider'), viatura_id: v('npat-viat') || null, agentes: ags }) }); hideLoad(); if (d?.success) { toast('Patrulha criada.', 'ok'); voltarPara('patrulhas'); } }

// ══════════════════
// IDENTIDADE (Agentes / Unidades)
// ══════════════════
async function loadIdentidade() { loadAgentes('activo', 'list-ag-act'); loadAgentes('inactivo', 'list-ag-ina'); loadUnidades(); }
async function loadAgentes(estado, cid) { const d = await api('/agentes?estado=' + estado); if (!d) return; const c = document.getElementById(cid); if (!c) return; if (!d.length) { c.innerHTML = '<div class="tbl-empty">Sem agentes.</div>'; return; } c.innerHTML = d.map(a => `<div class="tbl-row"><div class="col c2"><strong>${a.nome}</strong></div><div class="col c1">${a.nip}</div><div class="col c2">${a.cargo || '-'}</div><div class="col c2">${a.unidade?.nome || '-'}</div><div class="col c1">${a.patente?.nome || '-'}</div><div class="col c1"><span class="badge badge-${a.estado === 'activo' ? 'green' : 'gray'}">${a.estado}</span></div><div class="col c1"><button class="btn-icon" onclick="toggleAgente(${a.id})" title="${a.estado === 'activo' ? 'Desactivar' : 'Activar'}"><i class='bx ${a.estado === 'activo' ? 'bx-block' : 'bx-check-circle'}'></i></button></div></div>`).join(''); }
async function loadUnidades() { const d = await api('/unidades'); if (!d) return; const c = document.getElementById('list-unidades'); if (!c) return; c.innerHTML = d.map(u => `<div class="tbl-row"><div class="col c2"><strong>${u.nome}</strong></div><div class="col c2">${u.tipo_unidade?.nome || '-'}</div><div class="col c2">${u.endereco || '-'}</div><div class="col c1"><span class="badge badge-${u.estado === 'activo' ? 'green' : 'gray'}">${u.estado}</span></div><div class="col c1"><button class="btn-icon" onclick="toggleUnidade(${u.id})"><i class='bx bx-power-off'></i></button></div></div>`).join(''); }
function openIdTab(name, ev) { document.querySelectorAll('.idtab').forEach(t => t.classList.remove('active')); document.getElementById('idtab-' + name)?.classList.add('active'); if (ev) { ev.target.closest('.tabs-bar').querySelectorAll('.tab').forEach(t => t.classList.remove('active')); ev.target.classList.add('active'); } }

async function criarAgente(ev) {
    ev.preventDefault(); limparErros();
    let erros = [];
    erros.push(validarObrigatorio('ag-nome', 'Nome'));
    erros.push(validarCampo('ag-nome', validarNome));
    erros.push(validarObrigatorio('ag-nip', 'NIP'));
    erros.push(validarObrigatorio('ag-email', 'Email'));
    erros.push(validarCampo('ag-email', validarEmail));
    erros.push(validarObrigatorio('ag-unidade', 'Unidade'));
    erros.push(validarObrigatorio('ag-cargo', 'Cargo'));
    erros.push(validarObrigatorio('ag-patente', 'Patente'));
    erros.push(validarObrigatorio('ag-perfil', 'Perfil'));
    erros.push(validarCampo('ag-tel', validarTelefone));
    erros = erros.filter(e => e !== null);
    if (erros.length) { toast('Corrija os erros assinalados.', 'err'); return false; }

    showLoad();
    const d = await api('/agentes', { method: 'POST', body: JSON.stringify({ nome: v('ag-nome'), nip: v('ag-nip'), bi: v('ag-bi') || null, email: v('ag-email'), telefone: v('ag-tel') || null, sexo: v('ag-sexo') || null, unidade_id: v('ag-unidade'), cargo: v('ag-cargo'), patente_id: v('ag-patente'), perfil_id: v('ag-perfil'), estado: v('ag-estado') }) });
    hideLoad();
    if (d?.success) { toast('Agente registado com sucesso.', 'ok'); document.getElementById('form-agente')?.reset(); loadIdentidade(); openIdTab('ag-act'); }
    return false;
}

async function toggleAgente(id) { const d = await api(`/agentes/${id}/toggle-estado`, { method: 'PATCH' }); if (d?.success) { toast(d.message, 'ok'); loadIdentidade(); } }
async function toggleUnidade(id) { const d = await api(`/unidades/${id}/toggle-estado`, { method: 'PATCH' }); if (d?.success) { toast(d.message, 'ok'); loadUnidades(); } }

// ══════════════════
// LOGS / CONFIG
// ══════════════════
async function loadLogs(page = 1) { const p = new URLSearchParams({ page, acao: v('f-log-acao'), tabela: v('f-log-tabela'), data_inicio: v('f-log-di'), data_fim: v('f-log-df') }); const d = await api('/logs?' + p); if (!d) return; const items = d.data || []; const c = document.getElementById('list-logs'); if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem logs.</div>'; return; } c.innerHTML = items.map(l => `<div class="tl-item"><div class="tl-dot"></div><div class="tl-content"><div class="tl-time">${fDT(l.created_at)} - IP: ${l.ip || '-'}</div><div class="tl-text"><span class="tl-user">${l.user?.email || 'Sistema'}</span> - <span class="badge badge-${l.acao === 'criar' ? 'green' : l.acao === 'apagar' ? 'red' : 'blue'}">${l.acao}</span>${l.tabela ? ` em <strong>${l.tabela}</strong>` : ''}${l.descricao ? ' - ' + l.descricao : ''}</div></div></div>`).join(''); renderPag('pag-logs', d, loadLogs); }

async function loadConfig() { const d = await api('/configuracoes'); if (!d) return; const c = document.getElementById('config-content'); if (!c) return; let h = ''; for (const [g, cfgs] of Object.entries(d)) { h += `<div class="card" style="margin-bottom:12px;"><div class="card-head"><h3 style="text-transform:capitalize;">${g}</h3></div><div class="card-body">`; cfgs.forEach(cfg => h += `<div class="detail-line"><span class="dl">${cfg.descricao || cfg.chave}</span><span class="dv">${cfg.valor}</span></div>`); h += '</div></div>'; } c.innerHTML = h; }

async function checkNotifs() { try { const d = await api('/mensagens/nao-lidas'); if (d?.total > 0) { const dot = document.getElementById('notif-dot'); if (dot) dot.style.display = 'block'; } } catch (e) { } setTimeout(checkNotifs, 60000); }

// (PDF functions consolidated above)

// ══════════════════
// PROCESSOS CRIMINAIS
// ══════════════════
async function loadProcessos(page = 1) {
    const p = new URLSearchParams({ page, busca: v('f-proc-busca'), estado: v('f-proc-estado'), data_inicio: v('f-proc-di'), data_fim: v('f-proc-df') });
    const un = v('f-proc-unidade'); if (un) p.append('unidade_id', un);
    const d = await api('/processos-criminais?' + p);
    if (!d) return;
    const items = d.data || [];
    const c = document.getElementById('list-proc');
    if (!items.length) { c.innerHTML = '<div class="tbl-empty">Sem processos criminais.</div>'; renderPag('pag-proc', d, loadProcessos); return; }
    c.innerHTML = items.map(p => {
        const est = { em_instrucao: ['Em Instrução','blue'], concluido: ['Concluído','green'], remetido_mp: ['Remetido MP','orange'], arquivado: ['Arquivado','gray'] };
        const [en, ec] = est[p.estado] || [p.estado, 'gray'];
        return `<div class="tbl-row" onclick="viewProcesso(${p.id})"><div class="col c2"><strong>${p.numero_processo}</strong></div><div class="col c2">${p.ocorrencia?.numero_ocorrencia || '-'}</div><div class="col c2">${p.ocorrencia?.tipo_crime?.nome || '-'}</div><div class="col c1">${fDate(p.data_abertura)}</div><div class="col c1"><span class="badge badge-${ec}">${en}</span></div><div class="col c1"><button class="btn-icon" onclick="event.stopPropagation();exportPdfProcesso(${p.id})" title="PDF"><i class='bx bx-download'></i></button></div></div>`;
    }).join('');
    renderPag('pag-proc', d, loadProcessos);
}

function formNovoProcesso() {
    renderMain('processos', `<div class="page-header"><div><h1 class="page-title">Abrir Processo Criminal</h1><p class="page-desc">Criar novo processo a partir de uma ocorrência</p></div><button class="btn-ghost" onclick="voltarPara('processos')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados do Processo</div>
        <div class="form-row"><div class="form-col"><label>Ocorrência *</label><select id="nproc-oc" required></select></div></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Resumo</label><textarea id="nproc-res" rows="4" placeholder="Resumo inicial do processo..."></textarea></div>
        <div class="form-row"><div class="form-col"><label><input type="checkbox" id="nproc-conf"> Processo Confidencial</label></div></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('processos')">Cancelar</button><button class="btn-primary" onclick="submitProcesso()"><i class='bx bx-folder-plus'></i> Abrir Processo</button></div>
    </div>`);
    loadSelOcorrencias('nproc-oc');
}

async function submitProcesso() {
    limparErros();
    let e = [validarObrigatorio('nproc-oc', 'Ocorrência')].filter(x => x);
    if (e.length) { toast('Corrija os erros.', 'err'); return; }
    showLoad();
    const d = await api('/processos-criminais', { method: 'POST', body: JSON.stringify({ ocorrencia_id: v('nproc-oc'), resumo: v('nproc-res'), confidencial: document.getElementById('nproc-conf')?.checked || false }) });
    hideLoad();
    if (d?.success) { toast('Processo criminal aberto.', 'ok'); voltarPara('processos'); }
}

async function viewProcesso(id) {
    showLoad();
    const p = await api('/processos-criminais/' + id);
    hideLoad();
    if (!p) return;
    const est = { em_instrucao: ['Em Instrução','blue'], concluido: ['Concluído','green'], remetido_mp: ['Remetido MP','orange'], arquivado: ['Arquivado','gray'] };
    const [en, ec] = est[p.estado] || [p.estado,'gray'];
    const oc = p.ocorrencia || {};
    let h = `<div class="page-header"><div><h1 class="page-title">${p.numero_processo}</h1><p class="page-desc">Processo Criminal — <span class="badge badge-${ec}">${en}</span></p></div><div style="display:flex;gap:8px;"><button class="btn-ghost" onclick="exportPdfProcesso(${p.id})"><i class='bx bx-download'></i> PDF</button><button class="btn-ghost" onclick="voltarPara('processos')"><i class='bx bx-arrow-back'></i> Voltar</button></div></div>`;
    h += `<div class="grid-2">`;
    h += `<div class="card"><div class="card-head"><h3>Dados do Processo</h3></div><div class="card-body">${dl('Número',p.numero_processo)}${dl('Estado',`<span class="badge badge-${ec}">${en}</span>`)}${dl('Data Abertura', fDate(p.data_abertura))}${dl('Data Conclusão', fDate(p.data_conclusao))}${dl('Data Remessa', fDate(p.data_remessa))}${dl('Destino Remessa', p.destino_remessa)}${dl('Confidencial', p.confidencial ? 'Sim' : 'Não')}${dl('Responsável', p.agente_responsavel?.nome)}${dl('Unidade', p.unidade?.nome)}</div></div>`;
    h += `<div class="card"><div class="card-head"><h3>Ocorrência Associada</h3></div><div class="card-body">${dl('Número', oc.numero_ocorrencia)}${dl('Tipo Crime', oc.tipo_crime?.nome)}${dl('Data', fDate(oc.data_ocorrencia) + ' ' + (oc.hora_ocorrencia||''))}${dl('Local', oc.local)}${dl('Bairro', oc.bairro)}${dl('Prioridade', bPrio(oc.prioridade))}${dl('Estado', bEstado(oc.estado))}</div></div>`;
    h += `</div>`;
    if (p.resumo) h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Resumo</h3></div><div class="card-body"><p style="line-height:1.7;">${p.resumo}</p></div></div>`;

    // Envolvidos
    const envs = oc.envolvimentos || [];
    if (envs.length) {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Pessoas Envolvidas (${envs.length})</h3></div><div class="tbl"><div class="tbl-head"><div class="col c2">Nome</div><div class="col c1">BI</div><div class="col c1">Tipo</div></div>`;
        envs.forEach(e => h += `<div class="tbl-row"><div class="col c2"><strong>${e.pessoa?.nome||'-'}</strong></div><div class="col c1">${e.pessoa?.bi||'-'}</div><div class="col c1">${bGen(e.tipo_envolvimento?.nome)}</div></div>`);
        h += `</div></div>`;
    }
    // Detenções
    const dets = oc.detencoes || [];
    if (dets.length) {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Detenções (${dets.length})</h3></div><div class="tbl"><div class="tbl-head"><div class="col c2">Nº Detenção</div><div class="col c2">Detido</div><div class="col c1">Data</div><div class="col c1">Estado</div></div>`;
        dets.forEach(d => h += `<div class="tbl-row"><div class="col c2">${d.numero_detencao||'-'}</div><div class="col c2"><strong>${d.pessoa?.nome||'-'}</strong></div><div class="col c1">${fDate(d.data_detencao)}</div><div class="col c1">${bEstado(d.estado)}</div></div>`);
        h += `</div></div>`;
    }
    // Evidências
    const evs = oc.evidencias || [];
    if (evs.length) {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Evidências (${evs.length})</h3></div><div class="tbl"><div class="tbl-head"><div class="col c1">Código</div><div class="col c1">Tipo</div><div class="col c2">Descrição</div><div class="col c1">Estado</div></div>`;
        evs.forEach(e => h += `<div class="tbl-row"><div class="col c1"><strong>${e.codigo}</strong></div><div class="col c1">${e.tipo_evidencia?.nome||'-'}</div><div class="col c2">${e.descricao||'-'}</div><div class="col c1">${e.estado||'-'}</div></div>`);
        h += `</div></div>`;
    }
    // Investigações
    const invs = oc.investigacoes || [];
    if (invs.length) {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Investigações (${invs.length})</h3></div><div class="tbl"><div class="tbl-head"><div class="col c2">Número</div><div class="col c2">Investigador</div><div class="col c1">Progresso</div><div class="col c1">Estado</div></div>`;
        invs.forEach(i => h += `<div class="tbl-row" onclick="viewInvestigacao(${i.id})"><div class="col c2">${i.numero_investigacao||'-'}</div><div class="col c2">${i.investigador?.nome||'-'}</div><div class="col c1">${i.progresso||0}%</div><div class="col c1">${bEstado(i.estado)}</div></div>`);
        h += `</div></div>`;
    }
    // Acções — actualizar estado
    if (p.estado !== 'arquivado') {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Acções</h3></div><div class="card-body"><div class="form-row">`;
        h += `<div class="form-col"><label>Alterar Estado</label><select id="upd-proc-est"><option value="">Selecionar</option>`;
        if (p.estado === 'em_instrucao') h += `<option value="concluido">Concluído</option><option value="arquivado">Arquivado</option>`;
        if (p.estado === 'concluido') h += `<option value="remetido_mp">Remeter ao MP</option><option value="arquivado">Arquivado</option>`;
        if (p.estado === 'remetido_mp') h += `<option value="arquivado">Arquivado</option>`;
        h += `</select></div>`;
        if (p.estado === 'concluido' || p.estado === 'em_instrucao') h += `<div class="form-col"><label>Destino Remessa</label><input type="text" id="upd-proc-dest" value="${p.destino_remessa||''}" placeholder="Ex: Procuradoria Municipal de Viana"></div>`;
        h += `</div>`;
        h += `<div class="form-col" style="margin:14px 0;"><label>Parecer Final</label><textarea id="upd-proc-parecer" rows="3">${p.parecer_final||''}</textarea></div>`;
        h += `<button class="btn-primary" onclick="updateProcesso(${p.id})"><i class='bx bx-save'></i> Actualizar</button>`;
        h += `</div></div>`;
    }
    if (p.parecer_final && p.estado === 'arquivado') {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Parecer Final</h3></div><div class="card-body"><p style="line-height:1.7;">${p.parecer_final}</p></div></div>`;
    }
    renderMain('processos', h);
}

async function updateProcesso(id) {
    const estado = v('upd-proc-est');
    if (!estado) { toast('Selecione um estado.', 'err'); return; }
    showLoad();
    const d = await api('/processos-criminais/' + id, { method: 'PUT', body: JSON.stringify({ estado, parecer_final: v('upd-proc-parecer'), destino_remessa: v('upd-proc-dest') || null }) });
    hideLoad();
    if (d?.success) { toast(d.message, 'ok'); viewProcesso(id); }
}

function exportPdfProcesso(id) { window.open('/api/pdf/processo/' + id, '_blank'); }

// ══════════════════
// INVESTIGAÇÕES (melhoradas)
// ══════════════════
async function viewInvestigacao(id) {
    showLoad();
    const inv = await api('/investigacoes/' + id);
    hideLoad();
    if (!inv) return;
    const oc = inv.ocorrencia || {};
    let h = `<div class="page-header"><div><h1 class="page-title">${inv.numero_investigacao}</h1><p class="page-desc">Investigação — ${bEstado(inv.estado)}</p></div><div style="display:flex;gap:8px;"><button class="btn-ghost" onclick="exportPdfInvestigacao(${inv.id})"><i class='bx bx-download'></i> PDF</button><button class="btn-ghost" onclick="voltarPara('investigacoes')"><i class='bx bx-arrow-back'></i> Voltar</button></div></div>`;
    h += `<div class="grid-2">`;
    h += `<div class="card"><div class="card-head"><h3>Dados da Investigação</h3></div><div class="card-body">${dl('Número', inv.numero_investigacao)}${dl('Investigador', inv.investigador?.nome)}${dl('Estado', bEstado(inv.estado))}${dl('Progresso', inv.progresso + '%')}${dl('Data Início', fDate(inv.data_inicio))}${dl('Prazo', fDate(inv.prazo))}${dl('Data Conclusão', fDate(inv.data_fim))}</div></div>`;
    h += `<div class="card"><div class="card-head"><h3>Ocorrência Associada</h3></div><div class="card-body">${dl('Número', oc.numero_ocorrencia)}${dl('Tipo Crime', oc.tipo_crime?.nome)}${dl('Data', fDate(oc.data_ocorrencia))}${dl('Local', oc.local)}${dl('Estado', bEstado(oc.estado))}</div></div>`;
    h += `</div>`;
    if (inv.resumo) h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Resumo</h3></div><div class="card-body"><p style="line-height:1.7;">${inv.resumo}</p></div></div>`;

    // Notas
    const notas = inv.notas || [];
    h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Notas de Investigação (${notas.length})</h3><button class="btn-primary btn-sm" onclick="formAddNotaInvestigacao(${inv.id})"><i class='bx bx-plus'></i> Nota</button></div><div class="card-body">`;
    if (notas.length) {
        notas.forEach(n => {
            h += `<div style="border:1px solid var(--border-light);padding:12px;margin-bottom:8px;border-radius:var(--r-sm);"><div style="display:flex;justify-content:space-between;"><strong style="font-size:13px;">${n.titulo||'Sem título'}</strong><span style="font-size:11px;color:var(--text-4);">${fDT(n.created_at)}</span></div><div style="font-size:12px;color:var(--text-3);margin:4px 0;">${n.agente?.nome||'Sistema'}</div><p style="font-size:13px;line-height:1.6;margin-top:6px;">${n.conteudo}</p></div>`;
        });
    } else {
        h += `<div class="tbl-empty">Sem notas de investigação.</div>`;
    }
    h += `</div></div>`;

    // Envolvidos da ocorrência
    const envs = oc.envolvimentos || [];
    if (envs.length) {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Pessoas Envolvidas</h3></div><div class="tbl"><div class="tbl-head"><div class="col c2">Nome</div><div class="col c1">BI</div><div class="col c1">Tipo</div></div>`;
        envs.forEach(e => h += `<div class="tbl-row"><div class="col c2"><strong>${e.pessoa?.nome||'-'}</strong></div><div class="col c1">${e.pessoa?.bi||'-'}</div><div class="col c1">${bGen(e.tipo_envolvimento?.nome)}</div></div>`);
        h += `</div></div>`;
    }
    // Evidências
    const evs = oc.evidencias || [];
    if (evs.length) {
        h += `<div class="card" style="margin-top:14px;"><div class="card-head"><h3>Evidências (${evs.length})</h3></div><div class="tbl"><div class="tbl-head"><div class="col c1">Código</div><div class="col c1">Tipo</div><div class="col c2">Descrição</div><div class="col c1">Estado</div><div class="col c1">Ficheiro</div></div>`;
        evs.forEach(e => {
            const fileBtn = e.ficheiro ? `<button class="btn-icon" onclick="event.stopPropagation();previewEvidencia(${e.id},'${e.tipo_evidencia?.nome||''}','${e.descricao||''}')" title="Ver ficheiro"><i class='bx bx-show'></i></button>` : '-';
            h += `<div class="tbl-row"><div class="col c1"><strong>${e.codigo}</strong></div><div class="col c1">${e.tipo_evidencia?.nome||'-'}</div><div class="col c2">${e.descricao||'-'}</div><div class="col c1">${e.estado||'-'}</div><div class="col c1">${fileBtn}</div></div>`;
        });
        h += `</div></div>`;
    }
    renderMain('investigacoes', h);
}

function formAddNotaInvestigacao(invId) {
    const card = document.querySelector('#section-investigacoes .card:last-child');
    if (document.getElementById('nota-form-inline')) return;
    const formH = `<div id="nota-form-inline" style="border:1px solid var(--border);padding:16px;border-radius:var(--r);margin-top:12px;background:var(--bg);"><div class="form-col" style="margin-bottom:10px;"><label>Título</label><input type="text" id="nota-tit" placeholder="Título da nota"></div><div class="form-col" style="margin-bottom:10px;"><label>Conteúdo *</label><textarea id="nota-cont" rows="3" placeholder="Descreva as diligências realizadas..."></textarea></div><div style="display:flex;gap:8px;"><button class="btn-primary btn-sm" onclick="submitNotaInvestigacao(${invId})"><i class='bx bx-save'></i> Guardar</button><button class="btn-ghost btn-sm" onclick="document.getElementById('nota-form-inline').remove()">Cancelar</button></div></div>`;
    if (card) card.insertAdjacentHTML('beforeend', formH);
}

async function submitNotaInvestigacao(invId) {
    const cont = v('nota-cont');
    if (!cont) { toast('O conteúdo é obrigatório.', 'err'); return; }
    showLoad();
    const d = await api('/investigacoes/' + invId + '/notas', { method: 'POST', body: JSON.stringify({ titulo: v('nota-tit'), conteudo: cont }) });
    hideLoad();
    if (d?.success) { toast('Nota adicionada.', 'ok'); viewInvestigacao(invId); }
}

function exportPdfInvestigacao(id) { window.open('/api/pdf/investigacao/' + id, '_blank'); }

// ══════════════════
// EVIDÊNCIAS (melhoradas — viewer + upload)
// ══════════════════
function previewEvidencia(evId, tipo, desc) {
    const url = '/api/evidencias/' + evId + '/ficheiro';
    const ext = (tipo || '').toLowerCase();
    let content = '';
    const isImg = ['fotos','foto','imagem','fotografia'].some(t => ext.includes(t));
    const isVid = ['video','vídeo','filmagem'].some(t => ext.includes(t));
    const isAud = ['audio','áudio','gravação','gravacao'].some(t => ext.includes(t));
    const isDoc = ['documento','pdf','doc'].some(t => ext.includes(t));
    if (isImg) {
        content = `<img src="${url}" style="max-width:100%;max-height:70vh;border-radius:var(--r);" alt="${desc}">`;
    } else if (isVid) {
        content = `<video controls style="max-width:100%;max-height:70vh;border-radius:var(--r);"><source src="${url}">Navegador não suporta vídeo.</video>`;
    } else if (isAud) {
        content = `<div style="padding:40px;text-align:center;"><i class='bx bx-music' style="font-size:48px;color:var(--navy);margin-bottom:16px;display:block;"></i><audio controls style="width:100%;"><source src="${url}">Navegador não suporta áudio.</audio></div>`;
    } else if (isDoc) {
        content = `<iframe src="${url}" style="width:100%;height:70vh;border:none;border-radius:var(--r);"></iframe>`;
    } else {
        content = `<div style="padding:40px;text-align:center;"><i class='bx bx-file' style="font-size:48px;color:var(--text-3);margin-bottom:16px;display:block;"></i><p>Tipo de ficheiro não suportado para pré-visualização.</p><a href="${url}" download class="btn-primary" style="margin-top:12px;display:inline-flex;text-decoration:none;"><i class='bx bx-download'></i> Descarregar</a></div>`;
    }
    // Modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'ev-preview-overlay';
    overlay.innerHTML = `<div class="ev-preview-modal"><div class="ev-preview-header"><h3>${desc || 'Evidência'}</h3><div style="display:flex;gap:8px;"><a href="${url}" download class="btn-ghost btn-sm"><i class='bx bx-download'></i> Descarregar</a><button class="btn-ghost btn-sm" onclick="this.closest('.ev-preview-overlay').remove()"><i class='bx bx-x'></i></button></div></div><div class="ev-preview-body">${content}</div></div>`;
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
    document.body.appendChild(overlay);
}

function formNovaEvidencia() {
    renderMain('evidencias', `<div class="page-header"><div><h1 class="page-title">Registar Nova Evidência</h1></div><button class="btn-ghost" onclick="voltarPara('evidencias')"><i class='bx bx-arrow-back'></i> Voltar</button></div>
    <div class="form-card"><div class="form-section">Dados da Evidência</div>
        <div class="form-row"><div class="form-col"><label>Ocorrência *</label><select id="nev-oc" required></select></div><div class="form-col"><label>Tipo *</label>${mkSel('nev-tipo', aux.tipos_evidencia, 'id', 'nome')}</div></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Descrição *</label><textarea id="nev-desc" rows="3" required placeholder="Descreva a evidência..."></textarea></div>
        <div class="form-col" style="margin-bottom:14px;"><label>Ficheiro (imagem, vídeo, PDF, áudio)</label><input type="file" id="nev-file" accept="image/*,video/*,audio/*,.pdf"></div>
        <div id="nev-preview" style="margin-bottom:14px;"></div>
        <div class="form-actions"><button class="btn-ghost" onclick="voltarPara('evidencias')">Cancelar</button><button class="btn-primary" onclick="submitEvidencia()"><i class='bx bx-save'></i> Registar</button></div>
    </div>`);
    loadSelOcorrencias('nev-oc');
    document.getElementById('nev-file')?.addEventListener('change', function() {
        const file = this.files[0];
        const prev = document.getElementById('nev-preview');
        if (!file || !prev) { if(prev) prev.innerHTML=''; return; }
        const url = URL.createObjectURL(file);
        if (file.type.startsWith('image/')) prev.innerHTML = `<img src="${url}" style="max-height:200px;border-radius:var(--r);border:1px solid var(--border);">`;
        else if (file.type.startsWith('video/')) prev.innerHTML = `<video src="${url}" controls style="max-height:200px;border-radius:var(--r);"></video>`;
        else if (file.type.startsWith('audio/')) prev.innerHTML = `<audio src="${url}" controls style="width:100%;"></audio>`;
        else prev.innerHTML = `<div style="padding:12px;background:var(--bg);border-radius:var(--r);"><i class='bx bx-file'></i> ${file.name}</div>`;
    });
}

async function submitEvidencia() {
    limparErros();
    let e = [validarObrigatorio('nev-oc', 'Ocorrência'), validarObrigatorio('nev-tipo', 'Tipo'), validarObrigatorio('nev-desc', 'Descrição')].filter(x => x);
    if (e.length) { toast('Corrija os erros.', 'err'); return; }
    showLoad();
    const fd = new FormData();
    fd.append('ocorrencia_id', v('nev-oc'));
    fd.append('tipo_evidencia_id', v('nev-tipo'));
    fd.append('descricao', v('nev-desc'));
    const file = document.getElementById('nev-file')?.files[0];
    if (file) fd.append('ficheiro', file);
    try {
        const resp = await fetch('/api/evidencias', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' },
            body: fd
        });
        const d = await resp.json();
        hideLoad();
        if (d?.success) { toast('Evidência registada.', 'ok'); voltarPara('evidencias'); }
        else { toast(d?.message || 'Erro ao registar.', 'err'); }
    } catch (err) { hideLoad(); toast('Erro de rede.', 'err'); }
}

// ══════════════════
// UTILITARIOS
// ══════════════════
function v(id) { const el = document.getElementById(id); return el ? el.value.trim() : ''; }
function txt(id, t) { const el = document.getElementById(id); if (el) el.textContent = t; }
function today() { return new Date().toISOString().split('T')[0]; }
function nowLocal() { return new Date().toISOString().slice(0, 16); }
function fDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleDateString('pt-AO'); } catch (e) { return d; } }
function fDT(d) { if (!d) return '-'; try { const dt = new Date(d); return dt.toLocaleDateString('pt-AO') + ' ' + dt.toLocaleTimeString('pt-AO', { hour: '2-digit', minute: '2-digit' }); } catch (e) { return d; } }

function fg(label, input) { return `<div class="form-col"><label>${label}</label>${input}</div>`; }
function dl(l, val) { return `<div class="detail-line"><span class="dl">${l || '-'}</span><span class="dv">${val || '-'}</span></div>`; }

function bPrio(p) { const m = { baixa: 'green', media: 'orange', alta: 'orange', critica: 'red', urgente: 'red', normal: 'blue' }; return `<span class="badge badge-${m[p] || 'gray'}">${p || '-'}</span>`; }
function bEstado(e) { if (!e) return '-'; return `<span class="badge" style="background:${e.cor || '#eee'}20;color:${e.cor || '#666'}">${e.nome}</span>`; }
function bEstadoObj(e) { if (!e) return ''; return `<span class="badge" style="background:${e.cor || '#eee'}20;color:${e.cor || '#666'}">${e.nome}</span>`; }
function bGen(t) { return `<span class="badge badge-blue">${t || '-'}</span>`; }

function toast(msg, type = 'info') { const c = document.getElementById('toast-container'); if (!c) return; const el = document.createElement('div'); el.className = 'toast ' + type; const icos = { ok: 'bx-check-circle', err: 'bx-error-circle', warn: 'bx-error', info: 'bx-info-circle' }; el.innerHTML = `<i class='bx ${icos[type] || icos.info}'></i> ${msg}`; c.appendChild(el); setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 4000); }

function showLoad() { document.getElementById('loading-overlay')?.classList.add('active'); }
function hideLoad() { document.getElementById('loading-overlay')?.classList.remove('active'); }

// Confirmacao (unico modal que existe)
function showConfirm(title, msg, cb) { document.getElementById('confirm-title').textContent = title; document.getElementById('confirm-msg').textContent = msg; confirmCb = cb; document.getElementById('modal-confirm').style.display = 'flex'; }
function closeConfirm() { document.getElementById('modal-confirm').style.display = 'none'; confirmCb = null; }
function execConfirm() { if (confirmCb) confirmCb(); closeConfirm(); }
function confirmarLogout(ev) { ev.preventDefault(); showConfirm('Sair', 'Tem a certeza que deseja sair?', () => document.getElementById('logout-form').submit()); }

function renderPag(cid, data, cb) { const c = document.getElementById(cid); if (!c || !data.last_page || data.last_page <= 1) { if (c) c.innerHTML = ''; return; } let h = ''; if (data.current_page > 1) h += `<button onclick="${cb.name}(${data.current_page - 1})">Anterior</button>`; for (let i = 1; i <= data.last_page; i++) { if (i === 1 || i === data.last_page || Math.abs(i - data.current_page) <= 2) h += `<button class="${i === data.current_page ? 'active' : ''}" onclick="${cb.name}(${i})">${i}</button>`; else if (Math.abs(i - data.current_page) === 3) h += '<button disabled>...</button>'; } if (data.current_page < data.last_page) h += `<button onclick="${cb.name}(${data.current_page + 1})">Seguinte</button>`; c.innerHTML = h; }

function initUserMenu() { const trig = document.getElementById('user-trigger'); const menu = document.getElementById('user-menu'); if (!trig || !menu) return; trig.addEventListener('click', e => { e.stopPropagation(); menu.classList.toggle('open'); }); document.addEventListener('click', e => { if (!trig.contains(e.target) && !menu.contains(e.target)) menu.classList.remove('open'); }); }

function initKeys() { document.addEventListener('keydown', e => { if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); document.getElementById('searchInput')?.focus(); } if (e.key === 'Escape') { closeConfirm(); document.querySelector('.ev-preview-overlay')?.remove(); } }); }

// Quando o utilizador pressiona Enter num campo de busca, aciona a pesquisa
function initSearchEnter() {
    document.querySelectorAll('.filters').forEach(filtersDiv => {
        filtersDiv.querySelectorAll('.search-filter input[type="text"]').forEach(input => {
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const btn = filtersDiv.querySelector('.btn-primary.btn-sm');
                    if (btn) btn.click();
                }
            });
        });
    });
}