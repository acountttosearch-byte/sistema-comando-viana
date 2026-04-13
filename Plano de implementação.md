# Módulo de Processos Criminais + Correcção de Filtros + Melhorias de Investigações

## Contexto

O ficheiro `PENDENTE.md` solicita três actualizações ao Sistema de Comando de Viana:

1. **Processos Criminais** — Novo módulo completo (criar, ler, exportar PDF)
2. **Corrigir Filtros** — Botão de pesquisa fora das inputs em todas as secções
3. **Investigações** — Melhorias na secção de investigações (view detalhada, paginação, exportação)

---

## Proposta de Alterações

### 1. Módulo de Processos Criminais

Um **Processo Criminal** na realidade angolana é o dossiê formal que agrega toda a documentação de um caso para remessa ao Ministério Público / Procuradoria. Cada processo está ligado a uma ocorrência e consolida detenções, evidências, investigações, envolvidos e mandados.

#### [NEW] Migration: `2026_04_13_000001_create_processos_criminais_table.php`
- `id`, `numero_processo` (auto-gerado: `PC-VNA-2026-00001`)
- `ocorrencia_id` (FK → ocorrências)
- `agente_responsavel_id` (FK → agentes)
- `unidade_id` (FK → unidades)
- `estado` (enum: `em_instrucao`, `concluido`, `remetido_mp`, `arquivado`)
- `data_abertura`, `data_conclusao`, `data_remessa`
- `resumo`, `parecer_final`, `destino_remessa`
- `confidencial` (boolean)
- Timestamps + SoftDeletes

#### [NEW] Model: `ProcessoCriminal.php`
- Relações: `ocorrencia`, `agenteResponsavel`, `unidade`
- `gerarNumero()` com prefixo `PC-VNA`

#### [NEW] Controller: `ProcessoCriminalController.php`
- `index` — listagem paginada com RBAC + filtros (estado, unidade, busca, datas)
- `store` — criar processo a partir de uma ocorrência
- `show` — detalhes completos (ocorrência + envolvidos + detenções + evidências + investigações + mandados)
- `update` — actualizar estado, parecer, destino

#### [NEW] View: `partials/sections/processos.blade.php`
- Tabela com filtros: busca, estado, unidade (para admin/comandante), datas
- Botão "Novo Processo Criminal"

#### [NEW] PDF: `pdf/ficha-processo.blade.php`
- Ficha completa do processo criminal com todos os dados consolidados

#### [MODIFY] Ficheiros existentes:
- `sidebar.blade.php` — Adicionar link "Processos Criminais" na secção OPERACIONAL
- `painel/index.blade.php` — Incluir o `@include('partials.sections.processos')`
- `routes/api.php` — Adicionar rotas `/processos-criminais`
- `ExportPdfController.php` — Adicionar `fichaProcesso()`
- `DadosAuxiliaresController.php` — Não necessita alteração (dados existentes suficientes)
- `painel.js` — Adicionar funções: `loadProcessos()`, `formNovoProcesso()`, `viewProcesso()`, `submitProcesso()`, `exportPdfProcesso()`
- Adicionar `processos` ao `showSection()` no JS

---

### 2. Corrigir Filtros (todas as secções)

> [!IMPORTANT]
> O botão de pesquisar sai do interior do `.search-filter` e fica como elemento separado após o `div.search-filter`.

**Secções afectadas:**
- `ocorrencias.blade.php`
- `pessoas.blade.php`
- `detencoes.blade.php`
- `evidencias.blade.php`
- `investigacoes.blade.php`
- `viaturas.blade.php`
- `armamento.blade.php`
- `relatorios.blade.php`

**Padrão actual:**
```html
<div class="search-filter">
  <i class='bx bx-search'></i>
  <input type="text" id="..." placeholder="...">
  <button class="btn-ghost btn-sm" onclick="load...()" style="border:none;"><i class='bx bx-search'></i></button>
</div>
```

**Padrão corrigido:**
```html
<div class="search-filter">
  <i class='bx bx-search'></i>
  <input type="text" id="..." placeholder="...">
</div>
<button class="btn-primary btn-sm" onclick="load...()"><i class='bx bx-search'></i> Buscar</button>
```

**CSS:** Actualizar `.filters` para suportar botão externo com alinhamento correcto.

---

### 3. Melhorias no Módulo de Investigações

#### [MODIFY] `investigacoes.blade.php`
- Adicionar paginação (`pag-inv`)
- Adicionar filtro de unidade (para admin/comandante)

#### [MODIFY] `InvestigacaoController.php`
- Adicionar `show()` para vista detalhada

#### [MODIFY] `painel.js`
- Adicionar `viewInvestigacao(id)` — vista detalhada com notas, ocorrência, evidências
- Adicionar `formAddNotaInvestigacao(id)` — formulário para adicionar notas
- Adicionar paginação na listagem
- Adicionar `exportPdfInvestigacao(id)` — funcionalidade futura

#### [MODIFY] `routes/api.php`
- Adicionar rota `GET /investigacoes/{investigacao}` para show

---

## Ficheiros a Criar

| Ficheiro | Descrição |
|---|---|
| `database/migrations/2026_04_13_000001_create_processos_criminais_table.php` | Migration |
| `app/Models/ProcessoCriminal.php` | Modelo Eloquent |
| `app/Http/Controllers/ProcessoCriminalController.php` | Controller |
| `resources/views/partials/sections/processos.blade.php` | Secção da view |
| `resources/views/pdf/ficha-processo.blade.php` | Template PDF |

## Ficheiros a Modificar

| Ficheiro | Alteração |
|---|---|
| `resources/views/partials/sidebar.blade.php` | Adicionar nav item para processos |
| `resources/views/painel/index.blade.php` | Incluir secção processos |
| `routes/api.php` | Rotas de processos e show investigação |
| `app/Http/Controllers/ExportPdfController.php` | Exportação PDF de processo |
| `app/Http/Controllers/InvestigacaoController.php` | Adicionar show() |
| `resources/views/partials/sections/investigacoes.blade.php` | Paginação + filtro unidade |
| `resources/views/partials/sections/ocorrencias.blade.php` | Corrigir filtro |
| `resources/views/partials/sections/pessoas.blade.php` | Corrigir filtro |
| `resources/views/partials/sections/detencoes.blade.php` | Corrigir filtro |
| `resources/views/partials/sections/evidencias.blade.php` | Corrigir filtro |
| `resources/views/partials/sections/viaturas.blade.php` | Corrigir filtro |
| `resources/views/partials/sections/armamento.blade.php` | Corrigir filtro |
| `resources/views/partials/sections/relatorios.blade.php` | Corrigir filtro |
| `public/js/painel.js` | Processos + investigações melhoradas |
| `public/css/app.css` | Estilos para filtros corrigidos |

---

## Plano de Verificação

### Testes Automatizados
- `php artisan migrate` — Verificar que a migration corre sem erros

### Verificação Manual
- Navegar pelo sistema e confirmar:
  - Módulo de processos criminais visível no sidebar
  - Criar, visualizar e exportar PDF de um processo
  - Filtros com botão de busca externo em TODAS as secções
  - Investigações com vista detalhada, notas e paginação
