<div id="section-evidencias" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Cofre de Evidências</h1><p class="page-desc">Repositório digital e físico</p></div>
        <button class="btn-primary" onclick="modalNovaEvidencia()"><i class='bx bx-plus'></i> Nova Evidência</button>
    </div>
    <div class="tabs-bar">
        <button class="tab active" onclick="filtEv('todos',event)">Todos</button>
        <button class="tab" onclick="filtEv(1,event)">Fotos</button>
        <button class="tab" onclick="filtEv(2,event)">Vídeos</button>
        <button class="tab" onclick="filtEv(3,event)">Documentos</button>
        <button class="tab" onclick="filtEv(4,event)">Áudio</button>
        <button class="tab" onclick="filtEv(5,event)">Físicos</button>
    </div>
    <div class="evidence-grid" id="list-ev"><div class="tbl-empty" style="grid-column:1/-1;">Sem dados.</div></div>
</div>