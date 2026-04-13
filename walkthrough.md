# Walkthrough — Módulo Processos Criminais & Melhorias do Sistema

## Resumo

Implementação completa do módulo de **Processos Criminais**, melhorias significativas nas **Investigações** e **Evidências**, e integração com o **Dashboard** e sistema de **exportação PDF**.

---

## 1. Processos Criminais (Novo Módulo)

### Backend
| Ficheiro | Acção |
|---|---|
| [ProcessoCriminal.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/app/Models/ProcessoCriminal.php) | Modelo com relações (Ocorrência, Agente, Unidade) e `gerarNumero()` |
| [ProcessoCriminalController.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/app/Http/Controllers/ProcessoCriminalController.php) | CRUD completo com RBAC e logging |
| [create_processos_criminais_table.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/database/migrations/2026_04_13_000001_create_processos_criminais_table.php) | Migration com campos: estado, datas, remessa, parecer, confidencial |
| [api.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/routes/api.php) | 4 rotas: GET/POST/GET{id}/PUT{id} |

### Frontend
| Ficheiro | Acção |
|---|---|
| [processos.blade.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/resources/views/partials/sections/processos.blade.php) | Secção com filtros, tabela e paginação |
| [sidebar.blade.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/resources/views/partials/sidebar.blade.php) | Entrada "Processos Criminais" na navegação |
| [painel.js](file:///c:/Users/Vasconcelos/sistema-comando-viana/public/js/painel.js) | 6 funções: `loadProcessos`, `formNovoProcesso`, `submitProcesso`, `viewProcesso`, `updateProcesso`, `exportPdfProcesso` |

### Fluxo de estados
```
em_instrucao → concluido → remetido_mp → arquivado
                        ↘ arquivado
```

---

## 2. Investigações (Melhoradas)

### Alterações
```diff:InvestigacaoController.php
<?php

namespace App\Http\Controllers;

use App\Models\Investigacao;
use App\Models\NotaInvestigacao;
use App\Models\Log;
use Illuminate\Http\Request;

class InvestigacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Investigacao::with(['ocorrencia.tipoCrime', 'investigador', 'estado']);
        if ($user->temPerfil('investigador')) $q->where('investigador_id', $user->agente->id);
        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('investigador_id')) $q->where('investigador_id', $request->investigador_id);
        if ($request->filled('data_inicio')) $q->where('data_inicio', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_inicio', '<=', $request->data_fim);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('numero_investigacao', 'like', "%$b%")->orWhere('resumo', 'like', "%$b%"));
        }
        return response()->json($q->orderByDesc('created_at')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'investigador_id' => 'required|exists:agentes,id',
        ]);

        $inv = Investigacao::create([
            'numero_investigacao' => Investigacao::gerarNumero(),
            'ocorrencia_id' => $request->ocorrencia_id,
            'investigador_id' => $request->investigador_id,
            'estado_id' => 1,
            'resumo' => $request->resumo,
            'data_inicio' => now(),
            'prazo' => $request->prazo,
            'progresso' => 0,
        ]);

        $inv->ocorrencia->update(['estado_id' => 4]);
        Log::registar('criar', 'investigacoes', $inv->id, "Investigação aberta");
        return response()->json(['success' => true, 'message' => 'Investigação aberta.', 'investigacao' => $inv->load(['ocorrencia', 'investigador', 'estado'])], 201);
    }

    public function update(Request $request, Investigacao $investigacao)
    {
        $investigacao->update($request->only(['estado_id', 'progresso', 'resumo']));
        if ($request->estado_id == 4) $investigacao->update(['data_fim' => now()]);
        Log::registar('editar', 'investigacoes', $investigacao->id, "Investigação actualizada");
        return response()->json(['success' => true, 'message' => 'Actualizada.']);
    }

    public function adicionarNota(Request $request, Investigacao $investigacao)
    {
        $request->validate(['titulo' => 'required|string|max:200', 'conteudo' => 'required|string']);
        $nota = NotaInvestigacao::create([
            'investigacao_id' => $investigacao->id,
            'agente_id' => auth()->user()->agente->id,
            'titulo' => $request->titulo,
            'conteudo' => $request->conteudo,
            'confidencial' => $request->confidencial ?? false,
        ]);
        return response()->json(['success' => true, 'nota' => $nota], 201);
    }

    public function notas(Investigacao $investigacao)
    {
        return response()->json($investigacao->notas()->with('agente')->orderByDesc('created_at')->get());
    }
}
===
<?php

namespace App\Http\Controllers;

use App\Models\Investigacao;
use App\Models\NotaInvestigacao;
use App\Models\Log;
use Illuminate\Http\Request;

class InvestigacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Investigacao::with(['ocorrencia.tipoCrime', 'investigador', 'estado']);
        if ($user->temPerfil('investigador')) $q->where('investigador_id', $user->agente->id);
        if ($request->filled('estado_id')) $q->where('estado_id', $request->estado_id);
        if ($request->filled('investigador_id')) $q->where('investigador_id', $request->investigador_id);
        if ($request->filled('data_inicio')) $q->where('data_inicio', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('data_inicio', '<=', $request->data_fim);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('numero_investigacao', 'like', "%$b%")->orWhere('resumo', 'like', "%$b%"));
        }
        return response()->json($q->orderByDesc('created_at')->paginate($request->per_page ?? 20));
    }

    public function show(Investigacao $investigacao)
    {
        return response()->json($investigacao->load([
            'ocorrencia.tipoCrime.categoria', 'ocorrencia.estado',
            'ocorrencia.agenteRegisto', 'ocorrencia.agenteResponsavel',
            'ocorrencia.unidade', 'ocorrencia.envolvimentos.pessoa',
            'ocorrencia.envolvimentos.tipoEnvolvimento',
            'ocorrencia.evidencias.tipoEvidencia',
            'ocorrencia.detencoes.pessoa', 'ocorrencia.detencoes.estado',
            'investigador', 'estado', 'notas.agente',
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'investigador_id' => 'required|exists:agentes,id',
        ]);

        $inv = Investigacao::create([
            'numero_investigacao' => Investigacao::gerarNumero(),
            'ocorrencia_id' => $request->ocorrencia_id,
            'investigador_id' => $request->investigador_id,
            'estado_id' => 1,
            'resumo' => $request->resumo,
            'data_inicio' => now(),
            'prazo' => $request->prazo,
            'progresso' => 0,
        ]);

        $inv->ocorrencia->update(['estado_id' => 4]);
        Log::registar('criar', 'investigacoes', $inv->id, "Investigação aberta");
        return response()->json(['success' => true, 'message' => 'Investigação aberta.', 'investigacao' => $inv->load(['ocorrencia', 'investigador', 'estado'])], 201);
    }

    public function update(Request $request, Investigacao $investigacao)
    {
        $investigacao->update($request->only(['estado_id', 'progresso', 'resumo']));
        if ($request->estado_id == 4) $investigacao->update(['data_fim' => now()]);
        Log::registar('editar', 'investigacoes', $investigacao->id, "Investigação actualizada");
        return response()->json(['success' => true, 'message' => 'Actualizada.']);
    }

    public function adicionarNota(Request $request, Investigacao $investigacao)
    {
        $request->validate(['titulo' => 'nullable|string|max:200', 'conteudo' => 'required|string']);
        $nota = NotaInvestigacao::create([
            'investigacao_id' => $investigacao->id,
            'agente_id' => auth()->user()->agente->id,
            'titulo' => $request->titulo,
            'conteudo' => $request->conteudo,
            'confidencial' => $request->confidencial ?? false,
        ]);
        return response()->json(['success' => true, 'nota' => $nota], 201);
    }

    public function notas(Investigacao $investigacao)
    {
        return response()->json($investigacao->notas()->with('agente')->orderByDesc('created_at')->get());
    }
}
```

- **Vista detalhada** (`viewInvestigacao`): dados completos, notas, envolvidos, evidências com preview
- **Notas inline**: formulário para adicionar notas directamente na vista da investigação
- **Paginação e filtro por unidade** na listagem
- **Linhas clicáveis** na tabela e na ocorrência detail
- **Exportação PDF** com template dedicado
- **Título da nota agora opcional** (era obrigatório)

---

## 3. Evidências (Melhoradas)

### Viewer Modal
- **Pré-visualização inline** para imagens, vídeos, áudio e PDFs
- Modal com overlay escuro, botão descarregar e fechar (Esc ou click fora)
- Detecção de tipo por nome do `tipo_evidencia` (Fotos, Vídeo, Áudio, Documento)
- Usa rota API `/api/evidencias/{id}/ficheiro` em vez de path directo

### Upload de Evidências
- Formulário `formNovaEvidencia()` com upload de ficheiro
- Preview do ficheiro antes do envio (imagem/vídeo/áudio inline)
- Envio via `FormData` com `multipart/form-data`

### Cards Grid
- Grid responsivo com thumbnails (ícones por tipo)
- Botão "Ver ficheiro" em cada card para evidências com ficheiro

### CSS Adicionado
```diff:app.css
/* ═══════════════════════════════════════════
   SCGD — SISTEMA POLICIAL DE VIANA
   Identidade Visual Institucional
   Paleta: Azul Marinho + Cinza + Branco
   ═══════════════════════════════════════════ */

:root {
    --font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

    /* Cores institucionais */
    --navy: #134885;
    --navy-2: #144272;
    --navy-3: #205295;
    --navy-light: #e8edf3;
    --gold: #C4A35A;

    /* Neutros */
    --white: #ffffff;
    --bg: #f4f5f7;
    --bg-2: #eaecf0;
    --border: #d0d5dd;
    --border-light: #e4e7ec;

    /* Texto */
    --text: #101828;
    --text-2: #344054;
    --text-3: #667085;
    --text-4: #98a2b3;

    /* Funcionais (mínimas) */
    --success: #12B76A;
    --success-bg: #ECFDF3;
    --warning: #F79009;
    --warning-bg: #FFFAEB;
    --danger: #F04438;
    --alert: #dc2626b0;
    --danger-bg: #FEF3F2;
    --info: #0A2647;
    --info-bg: #e8edf3;

    /* Estrutura */
    --r: 8px;
    --r-sm: 6px;
    --shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    --shadow-md: 0 4px 8px -2px rgba(16, 24, 40, 0.1);
    --shadow-lg: 0 12px 24px -4px rgba(16, 24, 40, 0.1);
    --header-h: 56px;
    --sidebar-w: 250px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    line-height: 1.5;
    overflow: hidden;
}

/* ══════════════════
   TOPBAR
   ══════════════════ */
.topbar {
    height: var(--header-h);
    background: var(--white);
    border-bottom: 0.3px solid rgba(209, 209, 209, 0.418);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.topbar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.topbar-logo img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
}

.topbar-center {
    flex: 1;
    max-width: 420px;
    margin: 0 32px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(236, 236, 236, 0.514);
    border: 1px solid rgba(138, 138, 138, 0.075);
    border-radius: var(--r);
    padding: 7px 12px;
    transition: all .2s;
}

.search-box:focus-within {
    background:  rgba(209, 209, 209, 0.425);
    border-color: rgba(113, 154, 230, 0.5);
}

.search-box i {
    color: rgba(121, 121, 121, 0.568);
    font-size: 16px;
}

.search-box input {
    flex: 1;
    border: none;
    background: none;
    outline: none;
    font-size: 13px;
    color: var(--text-3);
    font-family: var(--font);
}

.search-box input::placeholder {
    color: rgba(133, 132, 132, 0.4);
}

.search-box kbd {
    font-size: 10px;
    color: rgba(121, 121, 121, 0.568);
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 3px;
    padding: 1px 5px;
    font-family: var(--font);
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 6px;
}

.topbar-icon {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: var(--r-sm);
    color: var(--text-3);
    font-size: 20px;
    transition: all .15s;
}

.topbar-icon:hover {
    background: rgba(255, 255, 255, 0.1);
    color:  rgba(92, 92, 92, 0.438);;
}

.notif-dot {
    position: absolute;
    top: 4px;
    right: 9px;
    width: 6.5px;
    height: 6.5px;
    background: var(--alert);
    border-radius: 50%;
    border: 1.5px solid rgba(75, 75, 75, 0.432);
}

/* User dropdown */
.topbar-user {
    position: relative;
}

.user-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--r);
    font-family: var(--font);
    transition: all .15s;
}

.user-trigger:hover {
    background: rgba(209, 209, 209, 0.418);;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 0.3px solid rgba(209, 209, 209, 0.418);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
}

.avatar-icon{
    width: 50%;
    height: 50%;
}

.user-avatar.sm {
    width: 28px;
    height: 28px;
    font-size: 11px;
}

.user-name-short {
    font-size: 13px;
    font-weight: 500;
     color: var(--text);
}

.user-trigger>i {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
}

.user-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 280px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    box-shadow: var(--shadow-lg);
    z-index: 200;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-4px);
    transition: all .15s;
}

.user-menu.open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-menu-header {
    padding: 14px 16px;
    background: var(--bg);
    border-bottom: 1px solid var(--border-light);
    border-radius: var(--r) var(--r) 0 0;
}

.user-menu-header strong {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.user-menu-header span {
    display: block;
    font-size: 12px;
    color: var(--text-3);
    margin-top: 1px;
}

.user-role-tag {
    display: inline-block;
    font-size: 10px;
    color: var(--navy);
    background: var(--navy-light);
    padding: 2px 8px;
    border-radius: 3px;
    margin-top: 6px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.user-menu-divider {
    height: 1px;
    background: var(--border-light);
    margin: 4px 0;
}

.user-menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    font-size: 13px;
    color: var(--text-2);
    text-decoration: none;
    border: none;
    background: none;
    cursor: pointer;
    width: 100%;
    font-family: var(--font);
    transition: background .1s;
}

.user-menu-item:hover {
    background: var(--bg);
}

.user-menu-item.logout {
    color: var(--danger);
}

.user-menu-item.logout:hover {
    background: var(--danger-bg);
}

.user-menu-item i {
    font-size: 16px;
    color: var(--text-3);
}

.user-menu-item.logout i {
    color: var(--danger);
}

/* ══════════════════
   SIDEBAR
   ══════════════════ */
.app-layout {
    display: flex;
    height: calc(100vh - var(--header-h));
    margin-top: var(--header-h);
}

.sidebar {
    width: var(--sidebar-w);
    background: var(--white);
    border-right: 1px solid var(--border);
    height: calc(100vh - var(--header-h));
    position: fixed;
    top: var(--header-h);
    bottom: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    z-index: 50;
}

.sidebar-nav {
    flex: 1;
    padding: 8px 0 20px;
    overflow-y: auto;
}

.nav-group {
    margin-bottom: 2px;
}

.nav-label {
    padding: 12px 20px 4px;
    font-size: 10px;
    font-weight: 700;
    color: var(--text-4);
    text-transform: uppercase;
    letter-spacing: .8px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 20px;
    font-size: 13px;
    color: var(--text-3);
    cursor: pointer;
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all .1s;
    margin: 1px 0;
}

.nav-item i {
    font-size: 18px;
    width: 18px;
    text-align: center;
}

.nav-item:hover {
    background: var(--bg);
    color: var(--text-2);
}

.nav-item.active {
    background: var(--navy-light);
    color: var(--navy);
    border-left-color: var(--navy);
    font-weight: 600;
}

.nav-item.active i {
    color: var(--navy);
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: var(--r);
}

.sidebar-user:hover {
    background: var(--bg);
}

.sidebar-user-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
    display: block;
}

.sidebar-user-role {
    font-size: 10px;
    color: var(--text-4);
    display: block;
}

.sidebar-user-info {
    flex: 1;
}

.sidebar-user>i {
    color: var(--text-4);
    font-size: 14px;
}

/* ══════════════════
   MAIN
   ══════════════════ */
.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    overflow-y: auto;
    padding: 24px 28px;
}

.main,
.sidebar {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.main::-webkit-scrollbar,
.sidebar::-webkit-scrollbar {
    width: 0;
    height: 0;
}

.sidebar-nav {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.sidebar-nav::-webkit-scrollbar {
    width: 0;
    height: 0;
}

.section {
    display: none;
}

.section.active {
    display: block;
}

/* ══════════════════
   PAGE HEADER
   ══════════════════ */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    gap: 12px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
}

.page-desc {
    font-size: 13px;
    color: var(--text-3);
    margin-top: 2px;
}

/* ══════════════════
   BUTTONS
   ══════════════════ */
.btn-primary,
.btn-danger,
.btn-ghost,
.btn-success {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border: none;
    border-radius: var(--r-sm);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    font-family: var(--font);
    transition: all .15s;
}

.btn-primary {
    background: var(--navy);
    color: var(--white);
}

.btn-primary:hover {
    background: var(--navy-2);
}

.btn-danger {
    background: var(--danger);
    color: var(--white);
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-success {
    background: var(--success);
    color: var(--white);
}

.btn-success:hover {
    background: #059669;
}

.btn-ghost {
    background: none;
    color: var(--text-2);
    border: 1px solid var(--border);
}

.btn-ghost:hover {
    background: var(--bg);
    color: var(--text);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.link-btn {
    font-size: 13px;
    color: var(--navy-3);
    cursor: pointer;
    background: none;
    border: none;
    font-weight: 500;
    font-family: var(--font);
    text-decoration: none;
}

.link-btn:hover {
    text-decoration: underline;
}

.btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-3);
    font-size: 18px;
    padding: 4px;
    border-radius: var(--r-sm);
}

.btn-icon:hover {
    background: var(--bg);
    color: var(--navy);
}

/* ══════════════════
   STAT CARDS
   ══════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 16px;
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: var(--r);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-icon.blue {
    background: var(--navy-light);
    color: var(--navy);
}

.stat-icon.green {
    background: var(--success-bg);
    color: var(--success);
}

.stat-icon.orange {
    background: var(--warning-bg);
    color: var(--warning);
}

.stat-icon.red {
    background: var(--danger-bg);
    color: var(--danger);
}

.stat-icon.purple {
    background: var(--navy-light);
    color: var(--navy-3);
}

.stat-icon.teal {
    background: var(--navy-light);
    color: var(--navy-2);
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--text);
    display: block;
}

.stat-label {
    font-size: 11px;
    color: var(--text-3);
    text-transform: uppercase;
    letter-spacing: .3px;
}

/* ══════════════════
   CARDS
   ══════════════════ */
.card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
}

.card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-light);
}

.card-head h3 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.card-body {
    padding: 16px 20px;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

/* ══════════════════
   TABLES
   ══════════════════ */
.tbl {
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    background: var(--white);
    margin-top: 8px;
}

.tbl-head,
.tbl-row {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    font-size: 13px;
}

.tbl-head {
    background: var(--bg);
    font-weight: 600;
    color: var(--text-2);
    border-bottom: 1px solid var(--border);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.tbl-row {
    border-bottom: 1px solid var(--border-light);
    cursor: pointer;
    transition: background .1s;
}

.tbl-row:last-child {
    border-bottom: none;
}

.tbl-row:hover {
    background: var(--bg);
}

.tbl-empty {
    padding: 48px;
    text-align: center;
    color: var(--text-4);
    font-size: 13px;
}

.col {
    padding: 0 6px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.c0 {
    flex: 0 0 30px;
    max-width: 30px;
}

.c1 {
    flex: 1;
    min-width: 0;
}

.c2 {
    flex: 2;
    min-width: 0;
}

.c3 {
    flex: 3;
    min-width: 0;
}

/* ══════════════════
   FILTERS
   ══════════════════ */
.filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 8px;
}

.filters select,
.filters input[type="date"] {
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    font-size: 13px;
    font-family: var(--font);
    background: var(--white);
    color: var(--text);
    outline: none;
}

.filters select:focus,
.filters input:focus {
    border-color: var(--navy-3);
}

.search-filter {
    display: flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    padding: 0 10px;
    background: var(--white);
}

.search-filter.full {
    flex: 1;
}

.search-filter i {
    color: var(--text-4);
    font-size: 16px;
}

.search-filter input {
    border: none;
    outline: none;
    padding: 8px 0;
    font-size: 13px;
    font-family: var(--font);
    width: 100%;
    background: none;
}


/* ══════════════════
   SEARCH INLINE BTN
   ══════════════════ */
.search-filter .btn-ghost {
    padding: 4px 6px;
    margin: 0;
    border: none;
    color: var(--text-3);
    flex-shrink: 0;
}

.search-filter .btn-ghost:hover {
    color: var(--navy);
    background: var(--bg);
}
/* ══════════════════
   TABS
   ══════════════════ */
.tabs-bar {
    display: flex;
    border-bottom: 2px solid var(--border);
    margin-bottom: 16px;
    gap: 0;
}

.tab {
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-3);
    border: none;
    background: none;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    font-family: var(--font);
    transition: all .12s;
}

.tab:hover {
    color: var(--text);
}

.tab.active {
    color: var(--navy);
    border-bottom-color: var(--navy);
    font-weight: 600;
}

.idtab {
    display: none;
}

.idtab.active {
    display: block;
}

/* ══════════════════
   BADGES
   ══════════════════ */
.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .2px;
}

.badge-green {
    background: var(--success-bg);
    color: var(--success);
}

.badge-orange {
    background: var(--warning-bg);
    color: #b45309;
}

.badge-red {
    background: var(--danger-bg);
    color: var(--danger);
}

.badge-blue {
    background: var(--navy-light);
    color: var(--navy);
}

.badge-gray {
    background: var(--bg);
    color: var(--text-3);
}

/* ══════════════════
   PROGRESS
   ══════════════════ */
.progress-track {
    width: 100%;
    background: var(--bg-2);
    height: 6px;
    border-radius: 3px;
    margin-top: 4px;
}

.progress-fill {
    height: 100%;
    background: var(--navy-3);
    border-radius: 3px;
    transition: width .3s;
}

/* ══════════════════
   BAR CHART
   ══════════════════ */
.bar-chart {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.bar-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bar-label {
    width: 110px;
    font-size: 11px;
    color: var(--text-3);
    text-align: right;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bar-bg {
    flex: 1;
    background: var(--bg-2);
    height: 20px;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fg {
    height: 100%;
    background: var(--navy-3);
    border-radius: 4px;
    display: flex;
    align-items: center;
    padding-left: 6px;
    transition: width .5s;
}

.bar-val {
    font-size: 10px;
    color: var(--white);
    font-weight: 600;
}

/* ══════════════════
   EVIDENCE GRID
   ══════════════════ */
.evidence-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 12px;
}

.ev-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: border .15s;
}

.ev-card:hover {
    border-color: var(--navy-3);
}

.ev-icon {
    font-size: 32px;
    color: var(--text-3);
    margin-bottom: 8px;
}

.ev-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ev-meta {
    font-size: 10px;
    color: var(--text-4);
    margin-top: 3px;
}

/* ══════════════════
   ALERTS
   ══════════════════ */
.alerts-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.alert-card {
    display: flex;
    gap: 14px;
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: var(--r);
    background: var(--white);
}

.alert-ico {
    font-size: 24px;
    color: var(--danger);
}

.alert-info {
    flex: 1;
}

.alert-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 3px;
    color: var(--text);
}

.alert-info p {
    font-size: 12px;
    color: var(--text-3);
    margin-bottom: 6px;
}

.alert-meta {
    font-size: 10px;
    color: var(--text-4);
}

/* ══════════════════
   FORMS (no main, não em modal)
   ══════════════════ */
.form-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 28px;
    margin-top: 8px;
}

.form-card h3 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--text);
}

.form-card .form-desc {
    font-size: 13px;
    color: var(--text-3);
    margin-bottom: 20px;
}

.form-section {
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
    text-transform: uppercase;
    margin: 24px 0 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border-light);
    letter-spacing: .5px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

.form-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

.form-col {
    display: flex;
    flex-direction: column;
}

.form-col label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-2);
    margin-bottom: 4px;
}

.form-col input,
.form-col select,
.form-col textarea {
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    font-size: 13px;
    font-family: var(--font);
    outline: none;
    color: var(--text);
    background: var(--white);
    transition: border .15s;
}

.form-col input:focus,
.form-col select:focus,
.form-col textarea:focus {
    border-color: var(--navy-3);
    box-shadow: 0 0 0 3px rgba(10, 38, 71, .06);
}

.form-col textarea {
    min-height: 80px;
    resize: vertical;
}

.form-col input:disabled,
.form-col select:disabled {
    background: var(--bg);
    color: var(--text-3);
    cursor: not-allowed;
}

.form-col input.error,
.form-col select.error {
    border-color: var(--danger);
}

.form-error {
    font-size: 11px;
    color: var(--danger);
    margin-top: 2px;
}

.form-hint {
    font-size: 11px;
    color: var(--text-4);
    margin-top: 2px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid var(--border-light);
}

/* Evidências inline no formulário */
.ev-inline-list {
    margin-top: 12px;
}

.ev-inline-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--bg);
    border: 1px solid var(--border-light);
    border-radius: var(--r-sm);
    margin-bottom: 6px;
    font-size: 12px;
}

.ev-inline-item i {
    font-size: 18px;
    color: var(--navy-3);
}

.ev-inline-item .ev-inline-name {
    flex: 1;
    font-weight: 500;
}

.ev-inline-item .ev-inline-remove {
    color: var(--danger);
    cursor: pointer;
    font-size: 16px;
}

/* ══════════════════
   DETAIL VIEW (no main, não em modal)
   ══════════════════ */
.detail-view {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 28px;
}

.detail-view-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.detail-view-header h2 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
}

.detail-view-actions {
    display: flex;
    gap: 8px;
}

.detail-sect {
    margin-bottom: 24px;
}

.detail-sect h4 {
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border-light);
}

.detail-line {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 13px;
    border-bottom: 1px solid var(--border-light);
}

.detail-line:last-child {
    border-bottom: none;
}

.detail-line .dl {
    color: var(--text-3);
    min-width: 140px;
}

.detail-line .dv {
    font-weight: 500;
    color: var(--text);
    text-align: right;
    max-width: 60%;
}

.detail-desc {
    font-size: 13px;
    color: var(--text);
    line-height: 1.7;
    white-space: pre-wrap;
    background: var(--bg);
    padding: 14px;
    border-radius: var(--r-sm);
    border: 1px solid var(--border-light);
}

/* ══════════════════
   MODALS (apenas para confirmação)
   ══════════════════ */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(16, 24, 40, .5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.modal-container {
    background: var(--white);
    border-radius: var(--r);
    box-shadow: var(--shadow-lg);
    width: 90%;
    max-width: 440px;
    animation: modalIn .2s ease;
}

@keyframes modalIn {
    from {
        transform: translateY(16px);
        opacity: 0
    }

    to {
        transform: translateY(0);
        opacity: 1
    }
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
}

.modal-head h2 {
    font-size: 16px;
    font-weight: 600;
}

.modal-x {
    background: none;
    border: none;
    font-size: 20px;
    color: var(--text-4);
    cursor: pointer;
}

.modal-x:hover {
    color: var(--text);
}

.modal-body {
    padding: 20px 24px;
}

.modal-body p {
    font-size: 13px;
    color: var(--text-2);
    line-height: 1.6;
}

.modal-foot {
    padding: 14px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

/* ══════════════════
   PAGINATION
   ══════════════════ */
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    padding: 14px 0;
}

.pagination button {
    padding: 6px 12px;
    border: 1px solid var(--border);
    background: var(--white);
    border-radius: var(--r-sm);
    cursor: pointer;
    font-size: 12px;
    font-family: var(--font);
    color: var(--text-3);
}

.pagination button.active {
    background: var(--navy);
    color: var(--white);
    border-color: var(--navy);
}

.pagination button:hover:not(.active) {
    background: var(--bg);
}

/* ══════════════════
   TIMELINE (Logs)
   ══════════════════ */
.timeline {
    position: relative;
    padding-left: 28px;
    margin-top: 12px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
}

.tl-item {
    position: relative;
    margin-bottom: 14px;
}

.tl-dot {
    position: absolute;
    left: -28px;
    top: 4px;
    width: 16px;
    height: 16px;
    background: var(--white);
    border: 2px solid var(--navy-3);
    border-radius: 50%;
}

.tl-content {
    background: var(--bg);
    padding: 10px 14px;
    border-radius: var(--r-sm);
    border: 1px solid var(--border-light);
}

.tl-time {
    font-size: 10px;
    color: var(--text-4);
    margin-bottom: 2px;
}

.tl-text {
    font-size: 12px;
}

.tl-user {
    font-weight: 600;
    color: var(--navy);
}

/* ══════════════════
   LOADING
   ══════════════════ */
#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, .7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

#loading-overlay.active {
    display: flex;
}

.loader {
    width: 28px;
    height: 28px;
    border: 3px solid var(--border);
    border-top: 3px solid var(--navy);
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg)
    }
}

/* ══════════════════
   TOAST
   ══════════════════ */
#toast-container {
    position: fixed;
    top: 68px;
    right: 20px;
    z-index: 100000;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.toast {
    padding: 12px 18px;
    border-radius: var(--r-sm);
    color: var(--white);
    font-size: 13px;
    font-weight: 500;
    box-shadow: var(--shadow-md);
    animation: toastIn .25s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 280px;
}

.toast.ok {
    background: var(--success);
}

.toast.err {
    background: var(--danger);
}

.toast.warn {
    background: var(--warning);
}

.toast.info {
    background: var(--navy);
}

@keyframes toastIn {
    from {
        transform: translateX(100%);
        opacity: 0
    }

    to {
        transform: translateX(0);
        opacity: 1
    }
}

.text-muted {
    color: var(--text-4);
    font-size: 13px;
}

/* ══════════════════
   RESPONSIVE
   ══════════════════ */
@media(max-width:1100px) {
    .grid-2 {
        grid-template-columns: 1fr
    }

    .form-row,
    .form-row-3 {
        grid-template-columns: 1fr
    }
}

@media(max-width:768px) {
    .sidebar {
        width: 56px
    }

    .nav-item span,
    .nav-label,
    .sidebar-footer {
        display: none
    }

    .main {
        margin-left: 56px
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr
    }
}
===
/* ═══════════════════════════════════════════
   SCGD — SISTEMA POLICIAL DE VIANA
   Identidade Visual Institucional
   Paleta: Azul Marinho + Cinza + Branco
   ═══════════════════════════════════════════ */

:root {
    --font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

    /* Cores institucionais */
    --navy: #134885;
    --navy-2: #144272;
    --navy-3: #205295;
    --navy-light: #e8edf3;
    --gold: #C4A35A;

    /* Neutros */
    --white: #ffffff;
    --bg: #f4f5f7;
    --bg-2: #eaecf0;
    --border: #d0d5dd;
    --border-light: #e4e7ec;

    /* Texto */
    --text: #101828;
    --text-2: #344054;
    --text-3: #667085;
    --text-4: #98a2b3;

    /* Funcionais (mínimas) */
    --success: #12B76A;
    --success-bg: #ECFDF3;
    --warning: #F79009;
    --warning-bg: #FFFAEB;
    --danger: #F04438;
    --alert: #dc2626b0;
    --danger-bg: #FEF3F2;
    --info: #0A2647;
    --info-bg: #e8edf3;

    /* Estrutura */
    --r: 8px;
    --r-sm: 6px;
    --shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    --shadow-md: 0 4px 8px -2px rgba(16, 24, 40, 0.1);
    --shadow-lg: 0 12px 24px -4px rgba(16, 24, 40, 0.1);
    --header-h: 56px;
    --sidebar-w: 250px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    line-height: 1.5;
    overflow: hidden;
}

/* ══════════════════
   TOPBAR
   ══════════════════ */
.topbar {
    height: var(--header-h);
    background: var(--white);
    border-bottom: 0.3px solid rgba(209, 209, 209, 0.418);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.topbar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.topbar-logo img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
}

.topbar-center {
    flex: 1;
    max-width: 420px;
    margin: 0 32px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(236, 236, 236, 0.514);
    border: 1px solid rgba(138, 138, 138, 0.075);
    border-radius: var(--r);
    padding: 7px 12px;
    transition: all .2s;
}

.search-box:focus-within {
    background:  rgba(209, 209, 209, 0.425);
    border-color: rgba(113, 154, 230, 0.5);
}

.search-box i {
    color: rgba(121, 121, 121, 0.568);
    font-size: 16px;
}

.search-box input {
    flex: 1;
    border: none;
    background: none;
    outline: none;
    font-size: 13px;
    color: var(--text-3);
    font-family: var(--font);
}

.search-box input::placeholder {
    color: rgba(133, 132, 132, 0.4);
}

.search-box kbd {
    font-size: 10px;
    color: rgba(121, 121, 121, 0.568);
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 3px;
    padding: 1px 5px;
    font-family: var(--font);
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 6px;
}

.topbar-icon {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: var(--r-sm);
    color: var(--text-3);
    font-size: 20px;
    transition: all .15s;
}

.topbar-icon:hover {
    background: rgba(255, 255, 255, 0.1);
    color:  rgba(92, 92, 92, 0.438);;
}

.notif-dot {
    position: absolute;
    top: 4px;
    right: 9px;
    width: 6.5px;
    height: 6.5px;
    background: var(--alert);
    border-radius: 50%;
    border: 1.5px solid rgba(75, 75, 75, 0.432);
}

/* User dropdown */
.topbar-user {
    position: relative;
}

.user-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--r);
    font-family: var(--font);
    transition: all .15s;
}

.user-trigger:hover {
    background: rgba(209, 209, 209, 0.418);;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 0.3px solid rgba(209, 209, 209, 0.418);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
}

.avatar-icon{
    width: 50%;
    height: 50%;
}

.user-avatar.sm {
    width: 28px;
    height: 28px;
    font-size: 11px;
}

.user-name-short {
    font-size: 13px;
    font-weight: 500;
     color: var(--text);
}

.user-trigger>i {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
}

.user-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 280px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    box-shadow: var(--shadow-lg);
    z-index: 200;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-4px);
    transition: all .15s;
}

.user-menu.open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-menu-header {
    padding: 14px 16px;
    background: var(--bg);
    border-bottom: 1px solid var(--border-light);
    border-radius: var(--r) var(--r) 0 0;
}

.user-menu-header strong {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.user-menu-header span {
    display: block;
    font-size: 12px;
    color: var(--text-3);
    margin-top: 1px;
}

.user-role-tag {
    display: inline-block;
    font-size: 10px;
    color: var(--navy);
    background: var(--navy-light);
    padding: 2px 8px;
    border-radius: 3px;
    margin-top: 6px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.user-menu-divider {
    height: 1px;
    background: var(--border-light);
    margin: 4px 0;
}

.user-menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    font-size: 13px;
    color: var(--text-2);
    text-decoration: none;
    border: none;
    background: none;
    cursor: pointer;
    width: 100%;
    font-family: var(--font);
    transition: background .1s;
}

.user-menu-item:hover {
    background: var(--bg);
}

.user-menu-item.logout {
    color: var(--danger);
}

.user-menu-item.logout:hover {
    background: var(--danger-bg);
}

.user-menu-item i {
    font-size: 16px;
    color: var(--text-3);
}

.user-menu-item.logout i {
    color: var(--danger);
}

/* ══════════════════
   SIDEBAR
   ══════════════════ */
.app-layout {
    display: flex;
    height: calc(100vh - var(--header-h));
    margin-top: var(--header-h);
}

.sidebar {
    width: var(--sidebar-w);
    background: var(--white);
    border-right: 1px solid var(--border);
    height: calc(100vh - var(--header-h));
    position: fixed;
    top: var(--header-h);
    bottom: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    z-index: 50;
}

.sidebar-nav {
    flex: 1;
    padding: 8px 0 20px;
    overflow-y: auto;
}

.nav-group {
    margin-bottom: 2px;
}

.nav-label {
    padding: 12px 20px 4px;
    font-size: 10px;
    font-weight: 700;
    color: var(--text-4);
    text-transform: uppercase;
    letter-spacing: .8px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 20px;
    font-size: 13px;
    color: var(--text-3);
    cursor: pointer;
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all .1s;
    margin: 1px 0;
}

.nav-item i {
    font-size: 18px;
    width: 18px;
    text-align: center;
}

.nav-item:hover {
    background: var(--bg);
    color: var(--text-2);
}

.nav-item.active {
    background: var(--navy-light);
    color: var(--navy);
    border-left-color: var(--navy);
    font-weight: 600;
}

.nav-item.active i {
    color: var(--navy);
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: var(--r);
}

.sidebar-user:hover {
    background: var(--bg);
}

.sidebar-user-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
    display: block;
}

.sidebar-user-role {
    font-size: 10px;
    color: var(--text-4);
    display: block;
}

.sidebar-user-info {
    flex: 1;
}

.sidebar-user>i {
    color: var(--text-4);
    font-size: 14px;
}

/* ══════════════════
   MAIN
   ══════════════════ */
.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    overflow-y: auto;
    padding: 24px 28px;
}

.main,
.sidebar {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.main::-webkit-scrollbar,
.sidebar::-webkit-scrollbar {
    width: 0;
    height: 0;
}

.sidebar-nav {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.sidebar-nav::-webkit-scrollbar {
    width: 0;
    height: 0;
}

.section {
    display: none;
}

.section.active {
    display: block;
}

/* ══════════════════
   PAGE HEADER
   ══════════════════ */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    gap: 12px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
}

.page-desc {
    font-size: 13px;
    color: var(--text-3);
    margin-top: 2px;
}

/* ══════════════════
   BUTTONS
   ══════════════════ */
.btn-primary,
.btn-danger,
.btn-ghost,
.btn-success {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border: none;
    border-radius: var(--r-sm);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    font-family: var(--font);
    transition: all .15s;
}

.btn-primary {
    background: var(--navy);
    color: var(--white);
}

.btn-primary:hover {
    background: var(--navy-2);
}

.btn-danger {
    background: var(--danger);
    color: var(--white);
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-success {
    background: var(--success);
    color: var(--white);
}

.btn-success:hover {
    background: #059669;
}

.btn-ghost {
    background: none;
    color: var(--text-2);
    border: 1px solid var(--border);
}

.btn-ghost:hover {
    background: var(--bg);
    color: var(--text);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.link-btn {
    font-size: 13px;
    color: var(--navy-3);
    cursor: pointer;
    background: none;
    border: none;
    font-weight: 500;
    font-family: var(--font);
    text-decoration: none;
}

.link-btn:hover {
    text-decoration: underline;
}

.btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-3);
    font-size: 18px;
    padding: 4px;
    border-radius: var(--r-sm);
}

.btn-icon:hover {
    background: var(--bg);
    color: var(--navy);
}

/* ══════════════════
   STAT CARDS
   ══════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 16px;
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: var(--r);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-icon.blue {
    background: var(--navy-light);
    color: var(--navy);
}

.stat-icon.green {
    background: var(--success-bg);
    color: var(--success);
}

.stat-icon.orange {
    background: var(--warning-bg);
    color: var(--warning);
}

.stat-icon.red {
    background: var(--danger-bg);
    color: var(--danger);
}

.stat-icon.purple {
    background: var(--navy-light);
    color: var(--navy-3);
}

.stat-icon.teal {
    background: var(--navy-light);
    color: var(--navy-2);
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--text);
    display: block;
}

.stat-label {
    font-size: 11px;
    color: var(--text-3);
    text-transform: uppercase;
    letter-spacing: .3px;
}

/* ══════════════════
   CARDS
   ══════════════════ */
.card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
}

.card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-light);
}

.card-head h3 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.card-body {
    padding: 16px 20px;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

/* ══════════════════
   TABLES
   ══════════════════ */
.tbl {
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    background: var(--white);
    margin-top: 8px;
}

.tbl-head,
.tbl-row {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    font-size: 13px;
}

.tbl-head {
    background: var(--bg);
    font-weight: 600;
    color: var(--text-2);
    border-bottom: 1px solid var(--border);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.tbl-row {
    border-bottom: 1px solid var(--border-light);
    cursor: pointer;
    transition: background .1s;
}

.tbl-row:last-child {
    border-bottom: none;
}

.tbl-row:hover {
    background: var(--bg);
}

.tbl-empty {
    padding: 48px;
    text-align: center;
    color: var(--text-4);
    font-size: 13px;
}

.col {
    padding: 0 6px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.c0 {
    flex: 0 0 30px;
    max-width: 30px;
}

.c1 {
    flex: 1;
    min-width: 0;
}

.c2 {
    flex: 2;
    min-width: 0;
}

.c3 {
    flex: 3;
    min-width: 0;
}

/* ══════════════════
   FILTERS
   ══════════════════ */
.filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 8px;
}

.filters select,
.filters input[type="date"] {
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    font-size: 13px;
    font-family: var(--font);
    background: var(--white);
    color: var(--text);
    outline: none;
}

.filters select:focus,
.filters input:focus {
    border-color: var(--navy-3);
}

.search-filter {
    display: flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    padding: 0 10px;
    background: var(--white);
}

.search-filter.full {
    flex: 1;
}

.search-filter i {
    color: var(--text-4);
    font-size: 16px;
}

.search-filter input {
    border: none;
    outline: none;
    padding: 8px 0;
    font-size: 13px;
    font-family: var(--font);
    width: 100%;
    background: none;
}


/* ══════════════════
   SEARCH INLINE BTN (legacy, kept for compatibility)
   ══════════════════ */
.search-filter .btn-ghost {
    padding: 4px 6px;
    margin: 0;
    border: none;
    color: var(--text-3);
    flex-shrink: 0;
}

.search-filter .btn-ghost:hover {
    color: var(--navy);
    background: var(--bg);
}

/* ══════════════════
   EVIDENCE GRID
   ══════════════════ */
.evidence-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
    margin-top: 8px;
}

.ev-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    cursor: pointer;
    transition: all .15s;
}

.ev-card:hover {
    box-shadow: var(--shadow-md);
    border-color: var(--navy-3);
}

.ev-card-thumb {
    height: 120px;
    background: var(--bg-2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: var(--text-4);
}

.ev-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ev-card-body {
    padding: 12px;
}

.ev-card-body strong {
    display: block;
    font-size: 13px;
    color: var(--text);
    margin-bottom: 4px;
}

.ev-card-body .ev-card-desc {
    font-size: 12px;
    color: var(--text-3);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ev-card-body .ev-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 11px;
    color: var(--text-4);
}

/* ══════════════════
   EVIDENCE PREVIEW MODAL
   ══════════════════ */
.ev-preview-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    animation: fadeIn .2s ease;
}

.ev-preview-modal {
    background: var(--white);
    border-radius: var(--r);
    box-shadow: var(--shadow-lg);
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.ev-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-light);
}

.ev-preview-header h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    margin-right: 12px;
}

.ev-preview-body {
    padding: 20px;
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.text-muted {
    color: var(--text-3);
}

/* ══════════════════
   TABS
   ══════════════════ */
.tabs-bar {
    display: flex;
    border-bottom: 2px solid var(--border);
    margin-bottom: 16px;
    gap: 0;
}

.tab {
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-3);
    border: none;
    background: none;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    font-family: var(--font);
    transition: all .12s;
}

.tab:hover {
    color: var(--text);
}

.tab.active {
    color: var(--navy);
    border-bottom-color: var(--navy);
    font-weight: 600;
}

.idtab {
    display: none;
}

.idtab.active {
    display: block;
}

/* ══════════════════
   BADGES
   ══════════════════ */
.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .2px;
}

.badge-green {
    background: var(--success-bg);
    color: var(--success);
}

.badge-orange {
    background: var(--warning-bg);
    color: #b45309;
}

.badge-red {
    background: var(--danger-bg);
    color: var(--danger);
}

.badge-blue {
    background: var(--navy-light);
    color: var(--navy);
}

.badge-gray {
    background: var(--bg);
    color: var(--text-3);
}

/* ══════════════════
   PROGRESS
   ══════════════════ */
.progress-track {
    width: 100%;
    background: var(--bg-2);
    height: 6px;
    border-radius: 3px;
    margin-top: 4px;
}

.progress-fill {
    height: 100%;
    background: var(--navy-3);
    border-radius: 3px;
    transition: width .3s;
}

/* ══════════════════
   BAR CHART
   ══════════════════ */
.bar-chart {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.bar-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bar-label {
    width: 110px;
    font-size: 11px;
    color: var(--text-3);
    text-align: right;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bar-bg {
    flex: 1;
    background: var(--bg-2);
    height: 20px;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fg {
    height: 100%;
    background: var(--navy-3);
    border-radius: 4px;
    display: flex;
    align-items: center;
    padding-left: 6px;
    transition: width .5s;
}

.bar-val {
    font-size: 10px;
    color: var(--white);
    font-weight: 600;
}

/* ══════════════════
   EVIDENCE GRID
   ══════════════════ */
.evidence-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 12px;
}

.ev-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: border .15s;
}

.ev-card:hover {
    border-color: var(--navy-3);
}

.ev-icon {
    font-size: 32px;
    color: var(--text-3);
    margin-bottom: 8px;
}

.ev-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ev-meta {
    font-size: 10px;
    color: var(--text-4);
    margin-top: 3px;
}

/* ══════════════════
   ALERTS
   ══════════════════ */
.alerts-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.alert-card {
    display: flex;
    gap: 14px;
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: var(--r);
    background: var(--white);
}

.alert-ico {
    font-size: 24px;
    color: var(--danger);
}

.alert-info {
    flex: 1;
}

.alert-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 3px;
    color: var(--text);
}

.alert-info p {
    font-size: 12px;
    color: var(--text-3);
    margin-bottom: 6px;
}

.alert-meta {
    font-size: 10px;
    color: var(--text-4);
}

/* ══════════════════
   FORMS (no main, não em modal)
   ══════════════════ */
.form-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 28px;
    margin-top: 8px;
}

.form-card h3 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--text);
}

.form-card .form-desc {
    font-size: 13px;
    color: var(--text-3);
    margin-bottom: 20px;
}

.form-section {
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
    text-transform: uppercase;
    margin: 24px 0 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border-light);
    letter-spacing: .5px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

.form-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

.form-col {
    display: flex;
    flex-direction: column;
}

.form-col label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-2);
    margin-bottom: 4px;
}

.form-col input,
.form-col select,
.form-col textarea {
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    font-size: 13px;
    font-family: var(--font);
    outline: none;
    color: var(--text);
    background: var(--white);
    transition: border .15s;
}

.form-col input:focus,
.form-col select:focus,
.form-col textarea:focus {
    border-color: var(--navy-3);
    box-shadow: 0 0 0 3px rgba(10, 38, 71, .06);
}

.form-col textarea {
    min-height: 80px;
    resize: vertical;
}

.form-col input:disabled,
.form-col select:disabled {
    background: var(--bg);
    color: var(--text-3);
    cursor: not-allowed;
}

.form-col input.error,
.form-col select.error {
    border-color: var(--danger);
}

.form-error {
    font-size: 11px;
    color: var(--danger);
    margin-top: 2px;
}

.form-hint {
    font-size: 11px;
    color: var(--text-4);
    margin-top: 2px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid var(--border-light);
}

/* Evidências inline no formulário */
.ev-inline-list {
    margin-top: 12px;
}

.ev-inline-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--bg);
    border: 1px solid var(--border-light);
    border-radius: var(--r-sm);
    margin-bottom: 6px;
    font-size: 12px;
}

.ev-inline-item i {
    font-size: 18px;
    color: var(--navy-3);
}

.ev-inline-item .ev-inline-name {
    flex: 1;
    font-weight: 500;
}

.ev-inline-item .ev-inline-remove {
    color: var(--danger);
    cursor: pointer;
    font-size: 16px;
}

/* ══════════════════
   DETAIL VIEW (no main, não em modal)
   ══════════════════ */
.detail-view {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 28px;
}

.detail-view-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.detail-view-header h2 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
}

.detail-view-actions {
    display: flex;
    gap: 8px;
}

.detail-sect {
    margin-bottom: 24px;
}

.detail-sect h4 {
    font-size: 11px;
    font-weight: 700;
    color: var(--navy);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border-light);
}

.detail-line {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 13px;
    border-bottom: 1px solid var(--border-light);
}

.detail-line:last-child {
    border-bottom: none;
}

.detail-line .dl {
    color: var(--text-3);
    min-width: 140px;
}

.detail-line .dv {
    font-weight: 500;
    color: var(--text);
    text-align: right;
    max-width: 60%;
}

.detail-desc {
    font-size: 13px;
    color: var(--text);
    line-height: 1.7;
    white-space: pre-wrap;
    background: var(--bg);
    padding: 14px;
    border-radius: var(--r-sm);
    border: 1px solid var(--border-light);
}

/* ══════════════════
   MODALS (apenas para confirmação)
   ══════════════════ */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(16, 24, 40, .5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.modal-container {
    background: var(--white);
    border-radius: var(--r);
    box-shadow: var(--shadow-lg);
    width: 90%;
    max-width: 440px;
    animation: modalIn .2s ease;
}

@keyframes modalIn {
    from {
        transform: translateY(16px);
        opacity: 0
    }

    to {
        transform: translateY(0);
        opacity: 1
    }
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
}

.modal-head h2 {
    font-size: 16px;
    font-weight: 600;
}

.modal-x {
    background: none;
    border: none;
    font-size: 20px;
    color: var(--text-4);
    cursor: pointer;
}

.modal-x:hover {
    color: var(--text);
}

.modal-body {
    padding: 20px 24px;
}

.modal-body p {
    font-size: 13px;
    color: var(--text-2);
    line-height: 1.6;
}

.modal-foot {
    padding: 14px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

/* ══════════════════
   PAGINATION
   ══════════════════ */
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    padding: 14px 0;
}

.pagination button {
    padding: 6px 12px;
    border: 1px solid var(--border);
    background: var(--white);
    border-radius: var(--r-sm);
    cursor: pointer;
    font-size: 12px;
    font-family: var(--font);
    color: var(--text-3);
}

.pagination button.active {
    background: var(--navy);
    color: var(--white);
    border-color: var(--navy);
}

.pagination button:hover:not(.active) {
    background: var(--bg);
}

/* ══════════════════
   TIMELINE (Logs)
   ══════════════════ */
.timeline {
    position: relative;
    padding-left: 28px;
    margin-top: 12px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
}

.tl-item {
    position: relative;
    margin-bottom: 14px;
}

.tl-dot {
    position: absolute;
    left: -28px;
    top: 4px;
    width: 16px;
    height: 16px;
    background: var(--white);
    border: 2px solid var(--navy-3);
    border-radius: 50%;
}

.tl-content {
    background: var(--bg);
    padding: 10px 14px;
    border-radius: var(--r-sm);
    border: 1px solid var(--border-light);
}

.tl-time {
    font-size: 10px;
    color: var(--text-4);
    margin-bottom: 2px;
}

.tl-text {
    font-size: 12px;
}

.tl-user {
    font-weight: 600;
    color: var(--navy);
}

/* ══════════════════
   LOADING
   ══════════════════ */
#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, .7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

#loading-overlay.active {
    display: flex;
}

.loader {
    width: 28px;
    height: 28px;
    border: 3px solid var(--border);
    border-top: 3px solid var(--navy);
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg)
    }
}

/* ══════════════════
   TOAST
   ══════════════════ */
#toast-container {
    position: fixed;
    top: 68px;
    right: 20px;
    z-index: 100000;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.toast {
    padding: 12px 18px;
    border-radius: var(--r-sm);
    color: var(--white);
    font-size: 13px;
    font-weight: 500;
    box-shadow: var(--shadow-md);
    animation: toastIn .25s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 280px;
}

.toast.ok {
    background: var(--success);
}

.toast.err {
    background: var(--danger);
}

.toast.warn {
    background: var(--warning);
}

.toast.info {
    background: var(--navy);
}

@keyframes toastIn {
    from {
        transform: translateX(100%);
        opacity: 0
    }

    to {
        transform: translateX(0);
        opacity: 1
    }
}

.text-muted {
    color: var(--text-4);
    font-size: 13px;
}

/* ══════════════════
   RESPONSIVE
   ══════════════════ */
@media(max-width:1100px) {
    .grid-2 {
        grid-template-columns: 1fr
    }

    .form-row,
    .form-row-3 {
        grid-template-columns: 1fr
    }
}

@media(max-width:768px) {
    .sidebar {
        width: 56px
    }

    .nav-item span,
    .nav-label,
    .sidebar-footer {
        display: none
    }

    .main {
        margin-left: 56px
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr
    }
}
```

---

## 4. Exportação PDF

| Template | Descrição |
|---|---|
| [ficha-processo.blade.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/resources/views/pdf/ficha-processo.blade.php) | Ficha completa do processo criminal |
| [ficha-investigacao.blade.php](file:///c:/Users/Vasconcelos/sistema-comando-viana/resources/views/pdf/ficha-investigacao.blade.php) | Ficha de investigação com notas |

```diff:ExportPdfController.php
<?php

namespace App\Http\Controllers;

use App\Models\Ocorrencia;
use App\Models\Detencao;
use App\Models\Agente;
use App\Models\Unidade;
use App\Models\Alerta;
use App\Models\Configuracao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportPdfController extends Controller
{
    public function relatorioCriminalidade(Request $request)
    {
        $request->validate([
            'periodo_inicio' => 'required|date',
            'periodo_fim' => 'required|date',
        ]);

        $inicio = $request->periodo_inicio;
        $fim = $request->periodo_fim;
        $uid = $request->unidade_id;

        $qOc = Ocorrencia::whereDate('data_ocorrencia', '>=', $inicio)
                          ->whereDate('data_ocorrencia', '<=', $fim);
        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);

        if ($uid) {
            $qOc->where('unidade_id', $uid);
            $qDt->where('unidade_id', $uid);
        }

        $totalOc = (clone $qOc)->count();
        $resolvidas = (clone $qOc)->where('estado_id', 5)->count();
        $abertas = (clone $qOc)->whereNotIn('estado_id', [5, 6, 7])->count();
        $tribunal = (clone $qOc)->where('estado_id', 6)->count();
        $arquivadas = (clone $qOc)->where('estado_id', 7)->count();
        $totalDet = (clone $qDt)->count();
        $taxa = $totalOc > 0 ? round(($resolvidas / $totalOc) * 100, 1) : 0;

        $crimesTipo = (clone $qOc)->selectRaw('tipo_crime_id, COUNT(*) as total')
            ->groupBy('tipo_crime_id')->orderByDesc('total')->limit(15)->get()
            ->map(fn($i) => ['tipo' => $i->tipoCrime?->nome ?? 'N/A', 'total' => $i->total]);

        $crimesBairro = (clone $qOc)->selectRaw('bairro, COUNT(*) as total')
            ->whereNotNull('bairro')->where('bairro', '!=', '')
            ->groupBy('bairro')->orderByDesc('total')->limit(15)->get();

        $crimesPrio = (clone $qOc)->selectRaw('prioridade, COUNT(*) as total')
            ->groupBy('prioridade')->get();

        $crimesMes = (clone $qOc)->selectRaw('MONTH(data_ocorrencia) as mes, COUNT(*) as total')
            ->groupBy('mes')->orderBy('mes')->get();

        $crimesPorUnidade = (clone $qOc)->selectRaw('unidade_id, COUNT(*) as total')
            ->groupBy('unidade_id')->orderByDesc('total')->get()
            ->map(fn($i) => ['unidade' => $i->unidade?->nome ?? 'N/A', 'total' => $i->total]);

        $unidadeNome = $uid ? (Unidade::find($uid)?->nome ?? 'N/A') : 'Todas as Unidades';
        $entidade = Configuracao::valor('entidade', 'Comando Municipal de Viana');

        $data = [
            'entidade' => $entidade,
            'unidade_nome' => $unidadeNome,
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'total_ocorrencias' => $totalOc,
            'resolvidas' => $resolvidas,
            'abertas' => $abertas,
            'tribunal' => $tribunal,
            'arquivadas' => $arquivadas,
            'total_detencoes' => $totalDet,
            'taxa_resolucao' => $taxa,
            'crimes_por_tipo' => $crimesTipo,
            'crimes_por_bairro' => $crimesBairro,
            'crimes_por_prioridade' => $crimesPrio,
            'crimes_por_mes' => $crimesMes,
            'crimes_por_unidade' => $crimesPorUnidade,
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.relatorio-criminalidade', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('relatorio-' . $inicio . '-a-' . $fim . '.pdf');
    }

    public function fichaOcorrencia(Ocorrencia $ocorrencia)
    {
        $ocorrencia->load([
            'tipoCrime.categoria', 'estado', 'agenteRegisto', 'agenteResponsavel',
            'unidade', 'envolvimentos.pessoa', 'envolvimentos.tipoEnvolvimento',
            'evidencias.tipoEvidencia', 'detencoes.pessoa', 'detencoes.estado',
            'investigacoes.investigador', 'investigacoes.estado',
        ]);

        $data = [
            'oc' => $ocorrencia,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-ocorrencia', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('ocorrencia-' . $ocorrencia->numero_ocorrencia . '.pdf');
    }

    public function fichaDetencao(Detencao $detencao)
    {
        $detencao->load(['pessoa', 'ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade', 'estado']);

        $data = [
            'dt' => $detencao,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-detencao', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('detencao-' . $detencao->numero_detencao . '.pdf');
    }

    public function listaAgentes(Request $request)
    {
        $q = Agente::with(['patente', 'unidade', 'user.perfil'])->orderBy('nome');
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);

        $data = [
            'agentes' => $q->get(),
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'filtro_estado' => $request->estado ?? 'Todos',
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.lista-agentes', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('agentes-' . date('Y-m-d') . '.pdf');
    }

    public function relatorioAlertas(Request $request)
    {
        $q = Alerta::with(['tipoAlerta', 'pessoa', 'criadoPor'])->orderByDesc('created_at');
        if ($request->filled('estado')) $q->where('estado', $request->estado);

        $data = [
            'alertas' => $q->get(),
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.relatorio-alertas', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('alertas-' . date('Y-m-d') . '.pdf');
    }
}
===
<?php

namespace App\Http\Controllers;

use App\Models\Ocorrencia;
use App\Models\Detencao;
use App\Models\Agente;
use App\Models\Unidade;
use App\Models\Alerta;
use App\Models\ProcessoCriminal;
use App\Models\Investigacao;
use App\Models\Configuracao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportPdfController extends Controller
{
    public function relatorioCriminalidade(Request $request)
    {
        $request->validate([
            'periodo_inicio' => 'required|date',
            'periodo_fim' => 'required|date',
        ]);

        $inicio = $request->periodo_inicio;
        $fim = $request->periodo_fim;
        $uid = $request->unidade_id;

        $qOc = Ocorrencia::whereDate('data_ocorrencia', '>=', $inicio)
                          ->whereDate('data_ocorrencia', '<=', $fim);
        $qDt = Detencao::whereDate('data_detencao', '>=', $inicio)
                        ->whereDate('data_detencao', '<=', $fim);

        if ($uid) {
            $qOc->where('unidade_id', $uid);
            $qDt->where('unidade_id', $uid);
        }

        $totalOc = (clone $qOc)->count();
        $resolvidas = (clone $qOc)->where('estado_id', 5)->count();
        $abertas = (clone $qOc)->whereNotIn('estado_id', [5, 6, 7])->count();
        $tribunal = (clone $qOc)->where('estado_id', 6)->count();
        $arquivadas = (clone $qOc)->where('estado_id', 7)->count();
        $totalDet = (clone $qDt)->count();
        $taxa = $totalOc > 0 ? round(($resolvidas / $totalOc) * 100, 1) : 0;

        $crimesTipo = (clone $qOc)->selectRaw('tipo_crime_id, COUNT(*) as total')
            ->groupBy('tipo_crime_id')->orderByDesc('total')->limit(15)->get()
            ->map(fn($i) => ['tipo' => $i->tipoCrime?->nome ?? 'N/A', 'total' => $i->total]);

        $crimesBairro = (clone $qOc)->selectRaw('bairro, COUNT(*) as total')
            ->whereNotNull('bairro')->where('bairro', '!=', '')
            ->groupBy('bairro')->orderByDesc('total')->limit(15)->get();

        $crimesPrio = (clone $qOc)->selectRaw('prioridade, COUNT(*) as total')
            ->groupBy('prioridade')->get();

        $crimesMes = (clone $qOc)->selectRaw('MONTH(data_ocorrencia) as mes, COUNT(*) as total')
            ->groupBy('mes')->orderBy('mes')->get();

        $crimesPorUnidade = (clone $qOc)->selectRaw('unidade_id, COUNT(*) as total')
            ->groupBy('unidade_id')->orderByDesc('total')->get()
            ->map(fn($i) => ['unidade' => $i->unidade?->nome ?? 'N/A', 'total' => $i->total]);

        $unidadeNome = $uid ? (Unidade::find($uid)?->nome ?? 'N/A') : 'Todas as Unidades';
        $entidade = Configuracao::valor('entidade', 'Comando Municipal de Viana');

        $data = [
            'entidade' => $entidade,
            'unidade_nome' => $unidadeNome,
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'total_ocorrencias' => $totalOc,
            'resolvidas' => $resolvidas,
            'abertas' => $abertas,
            'tribunal' => $tribunal,
            'arquivadas' => $arquivadas,
            'total_detencoes' => $totalDet,
            'taxa_resolucao' => $taxa,
            'crimes_por_tipo' => $crimesTipo,
            'crimes_por_bairro' => $crimesBairro,
            'crimes_por_prioridade' => $crimesPrio,
            'crimes_por_mes' => $crimesMes,
            'crimes_por_unidade' => $crimesPorUnidade,
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.relatorio-criminalidade', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('relatorio-' . $inicio . '-a-' . $fim . '.pdf');
    }

    public function fichaOcorrencia(Ocorrencia $ocorrencia)
    {
        $ocorrencia->load([
            'tipoCrime.categoria', 'estado', 'agenteRegisto', 'agenteResponsavel',
            'unidade', 'envolvimentos.pessoa', 'envolvimentos.tipoEnvolvimento',
            'evidencias.tipoEvidencia', 'detencoes.pessoa', 'detencoes.estado',
            'investigacoes.investigador', 'investigacoes.estado',
        ]);

        $data = [
            'oc' => $ocorrencia,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-ocorrencia', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('ocorrencia-' . $ocorrencia->numero_ocorrencia . '.pdf');
    }

    public function fichaDetencao(Detencao $detencao)
    {
        $detencao->load(['pessoa', 'ocorrencia.tipoCrime', 'agenteResponsavel', 'unidade', 'estado']);

        $data = [
            'dt' => $detencao,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-detencao', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('detencao-' . $detencao->numero_detencao . '.pdf');
    }

    public function listaAgentes(Request $request)
    {
        $q = Agente::with(['patente', 'unidade', 'user.perfil'])->orderBy('nome');
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);

        $data = [
            'agentes' => $q->get(),
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'filtro_estado' => $request->estado ?? 'Todos',
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.lista-agentes', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('agentes-' . date('Y-m-d') . '.pdf');
    }

    public function relatorioAlertas(Request $request)
    {
        $q = Alerta::with(['tipoAlerta', 'pessoa', 'criadoPor'])->orderByDesc('created_at');
        if ($request->filled('estado')) $q->where('estado', $request->estado);

        $data = [
            'alertas' => $q->get(),
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.relatorio-alertas', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('alertas-' . date('Y-m-d') . '.pdf');
    }

    public function fichaProcesso(ProcessoCriminal $processo)
    {
        $processo->load([
            'ocorrencia.tipoCrime.categoria', 'ocorrencia.estado',
            'ocorrencia.agenteRegisto', 'ocorrencia.agenteResponsavel',
            'ocorrencia.unidade', 'ocorrencia.envolvimentos.pessoa',
            'ocorrencia.envolvimentos.tipoEnvolvimento',
            'ocorrencia.evidencias.tipoEvidencia',
            'ocorrencia.detencoes.pessoa', 'ocorrencia.detencoes.estado',
            'ocorrencia.investigacoes.investigador', 'ocorrencia.investigacoes.estado',
            'agenteResponsavel', 'unidade',
        ]);

        $data = [
            'proc' => $processo,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-processo', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('processo-' . $processo->numero_processo . '.pdf');
    }

    public function fichaInvestigacao(Investigacao $investigacao)
    {
        $investigacao->load([
            'ocorrencia.tipoCrime.categoria', 'ocorrencia.estado',
            'ocorrencia.unidade', 'ocorrencia.envolvimentos.pessoa',
            'ocorrencia.envolvimentos.tipoEnvolvimento',
            'investigador', 'estado', 'notas.agente',
        ]);

        $data = [
            'inv' => $investigacao,
            'entidade' => Configuracao::valor('entidade', 'Comando Municipal de Viana'),
            'gerado_por' => auth()->user()->agente?->nome ?? 'Admin',
            'data_geracao' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.ficha-investigacao', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('investigacao-' . $investigacao->numero_investigacao . '.pdf');
    }
}
```

---

## 5. Dashboard

```diff:inicio.blade.php
<div id="section-inicio" class="section active">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-desc">Resumo operacional do Comando Municipal de Viana</p>
        </div>
    </div>

    <div class="stats-grid" id="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class='bx bx-file'></i></div>
            <div><span class="stat-value" id="m-total-oc">—</span><span class="stat-label">Ocorrências</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class='bx bx-error-circle'></i></div>
            <div><span class="stat-value" id="m-abertas">—</span><span class="stat-label">Casos Abertos</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class='bx bx-check-circle'></i></div>
            <div><span class="stat-value" id="m-resolvidas">—</span><span class="stat-label">Resolvidos</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class='bx bx-lock-alt'></i></div>
            <div><span class="stat-value" id="m-detencoes">—</span><span class="stat-label">Detenções (mês)</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class='bx bx-search-alt-2'></i></div>
            <div><span class="stat-value" id="m-inv">—</span><span class="stat-label">Investigações</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class='bx bx-bell-ring'></i></div>
            <div><span class="stat-value" id="m-alertas">—</span><span class="stat-label">Alertas</span></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card"><div class="card-head"><h3>Crimes por Tipo</h3></div><div id="chart-tipo" class="card-body"><p class="text-muted">A carregar...</p></div></div>
        <div class="card"><div class="card-head"><h3>Crimes por Mês</h3></div><div id="chart-mes" class="card-body"><p class="text-muted">A carregar...</p></div></div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-head"><h3>Últimas Ocorrências</h3><a class="link-btn" onclick="showSection('ocorrencias')">Ver todas →</a></div>
        <div class="tbl">
            <div class="tbl-head"><div class="col c2">Número</div><div class="col c2">Tipo</div><div class="col c3">Local</div><div class="col c1">Prioridade</div><div class="col c1">Estado</div><div class="col c1">Data</div></div>
            <div id="dash-ultimas"><div class="tbl-empty">A carregar...</div></div>
        </div>
    </div>
</div>
===
<div id="section-inicio" class="section active">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-desc">Resumo operacional do Comando Municipal de Viana</p>
        </div>
    </div>

    <div class="stats-grid" id="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class='bx bx-file'></i></div>
            <div><span class="stat-value" id="m-total-oc">—</span><span class="stat-label">Ocorrências</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class='bx bx-error-circle'></i></div>
            <div><span class="stat-value" id="m-abertas">—</span><span class="stat-label">Casos Abertos</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class='bx bx-check-circle'></i></div>
            <div><span class="stat-value" id="m-resolvidas">—</span><span class="stat-label">Resolvidos</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class='bx bx-lock-alt'></i></div>
            <div><span class="stat-value" id="m-detencoes">—</span><span class="stat-label">Detenções (mês)</span></div>
        </div>
        <div class="stat-card" onclick="showSection('investigacoes')" style="cursor:pointer;">
            <div class="stat-icon teal"><i class='bx bx-search-alt-2'></i></div>
            <div><span class="stat-value" id="m-inv">—</span><span class="stat-label">Investigações</span></div>
        </div>
        <div class="stat-card" onclick="showSection('processos')" style="cursor:pointer;">
            <div class="stat-icon purple"><i class='bx bx-folder-open'></i></div>
            <div><span class="stat-value" id="m-proc">—</span><span class="stat-label">Processos Activos</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class='bx bx-bell-ring'></i></div>
            <div><span class="stat-value" id="m-alertas">—</span><span class="stat-label">Alertas</span></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card"><div class="card-head"><h3>Crimes por Tipo</h3></div><div id="chart-tipo" class="card-body"><p class="text-muted">A carregar...</p></div></div>
        <div class="card"><div class="card-head"><h3>Crimes por Mês</h3></div><div id="chart-mes" class="card-body"><p class="text-muted">A carregar...</p></div></div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-head"><h3>Últimas Ocorrências</h3><a class="link-btn" onclick="showSection('ocorrencias')">Ver todas →</a></div>
        <div class="tbl">
            <div class="tbl-head"><div class="col c2">Número</div><div class="col c2">Tipo</div><div class="col c3">Local</div><div class="col c1">Prioridade</div><div class="col c1">Estado</div><div class="col c1">Data</div></div>
            <div id="dash-ultimas"><div class="tbl-empty">A carregar...</div></div>
        </div>
    </div>
</div>
```

- **Novo stat card** "Processos Activos" clicável (navega para secção processos)
- **Stat "Investigações"** agora também clicável
- Métricas `processos_activos` e `processos_total` adicionadas ao backend

---

## 6. UI/UX — Filtros Padronizados

Botões de busca movidos para **fora dos inputs** em todas as secções:
- Ocorrências, Pessoas, Detenções, Evidências, Investigações, Viaturas, Armamento, Relatórios, **Processos Criminais**
- `initSearchEnter()` actualizado para o novo padrão de filtros

---

## Verificação

| Teste | Resultado |
|---|---|
| PHP syntax check (4 ficheiros) | ✅ Sem erros |
| `route:list --path=processos` | ✅ 4 rotas |
| `php artisan migrate` | ✅ Tabela criada |
| HTTP GET `/` | ✅ 200 OK |
