<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgenteController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\OcorrenciaController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\DetencaoController;
use App\Http\Controllers\EvidenciaController;
use App\Http\Controllers\InvestigacaoController;
use App\Http\Controllers\DespachoController;
use App\Http\Controllers\PatrulhaController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ViaturaController;
use App\Http\Controllers\ArmamentoController;
use App\Http\Controllers\MensagemController;
use App\Http\Controllers\MandadoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\QueixaCidadaoController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\DadosAuxiliaresController;

Route::middleware('auth:web')->group(function () {

    // Dashboard
    Route::get('/dashboard/metricas', [DashboardController::class, 'metricas']);

    // Dados auxiliares (selects, tipos, etc)
    Route::get('/dados-auxiliares', [DadosAuxiliaresController::class, 'todos']);

    // Agentes
    Route::get('/agentes', [AgenteController::class, 'index']);
    Route::post('/agentes', [AgenteController::class, 'store']);
    Route::get('/agentes/{agente}', [AgenteController::class, 'show']);
    Route::put('/agentes/{agente}', [AgenteController::class, 'update']);
    Route::patch('/agentes/{agente}/toggle-estado', [AgenteController::class, 'toggleEstado']);

    // Unidades
    Route::get('/unidades', [UnidadeController::class, 'index']);
    Route::post('/unidades', [UnidadeController::class, 'store']);
    Route::put('/unidades/{unidade}', [UnidadeController::class, 'update']);
    Route::patch('/unidades/{unidade}/toggle-estado', [UnidadeController::class, 'toggleEstado']);
    Route::get('/unidades/{unidade}/estatisticas', [UnidadeController::class, 'estatisticas']);
    Route::get('/esquadras', [UnidadeController::class, 'esquadras']);

    // Ocorrências
    Route::get('/ocorrencias', [OcorrenciaController::class, 'index']);
    Route::post('/ocorrencias', [OcorrenciaController::class, 'store']);
    Route::get('/ocorrencias/{ocorrencia}', [OcorrenciaController::class, 'show']);
    Route::put('/ocorrencias/{ocorrencia}', [OcorrenciaController::class, 'update']);
    Route::post('/ocorrencias/{ocorrencia}/envolvidos', [OcorrenciaController::class, 'adicionarEnvolvido']);

    // Pessoas
    Route::get('/pessoas', [PessoaController::class, 'index']);
    Route::post('/pessoas', [PessoaController::class, 'store']);
    Route::get('/pessoas/{pessoa}', [PessoaController::class, 'show']);
    Route::put('/pessoas/{pessoa}', [PessoaController::class, 'update']);

    // Detenções
    Route::get('/detencoes', [DetencaoController::class, 'index']);
    Route::post('/detencoes', [DetencaoController::class, 'store']);
    Route::get('/detencoes/{detencao}', [DetencaoController::class, 'show']);
    Route::patch('/detencoes/{detencao}/estado', [DetencaoController::class, 'actualizarEstado']);

    // Evidências
    Route::get('/evidencias', [EvidenciaController::class, 'index']);
    Route::post('/evidencias', [EvidenciaController::class, 'store']);
    Route::post('/evidencias/{evidencia}/transferir', [EvidenciaController::class, 'transferirCustodia']);
    Route::get('/evidencias/{evidencia}/custodia', [EvidenciaController::class, 'historicoCustodia']);

    // Investigações
    Route::get('/investigacoes', [InvestigacaoController::class, 'index']);
    Route::post('/investigacoes', [InvestigacaoController::class, 'store']);
    Route::put('/investigacoes/{investigacao}', [InvestigacaoController::class, 'update']);
    Route::post('/investigacoes/{investigacao}/notas', [InvestigacaoController::class, 'adicionarNota']);
    Route::get('/investigacoes/{investigacao}/notas', [InvestigacaoController::class, 'notas']);

    // Despachos
    Route::get('/despachos', [DespachoController::class, 'index']);
    Route::post('/despachos', [DespachoController::class, 'store']);
    Route::patch('/despachos/{despacho}/responder', [DespachoController::class, 'responder']);

    // Patrulhas
    Route::get('/patrulhas', [PatrulhaController::class, 'index']);
    Route::post('/patrulhas', [PatrulhaController::class, 'store']);
    Route::patch('/patrulhas/{patrulha}/estado', [PatrulhaController::class, 'actualizarEstado']);
    Route::post('/patrulhas/{patrulha}/incidentes', [PatrulhaController::class, 'registarIncidente']);

    // Alertas
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::post('/alertas', [AlertaController::class, 'store']);
    Route::patch('/alertas/{alerta}/visualizar', [AlertaController::class, 'confirmarVisualizacao']);
    Route::patch('/alertas/{alerta}/resolver', [AlertaController::class, 'resolver']);

    // Viaturas
    Route::get('/viaturas', [ViaturaController::class, 'index']);
    Route::post('/viaturas', [ViaturaController::class, 'store']);
    Route::post('/viaturas/{viatura}/atribuir', [ViaturaController::class, 'atribuir']);
    Route::patch('/viatura-atribuicoes/{atribuicao}/devolver', [ViaturaController::class, 'devolver']);

    // Armamento
    Route::get('/armamento', [ArmamentoController::class, 'index']);
    Route::post('/armamento', [ArmamentoController::class, 'store']);
    Route::post('/armamento/{armamento}/atribuir', [ArmamentoController::class, 'atribuir']);
    Route::patch('/armamento/{armamento}/devolver', [ArmamentoController::class, 'devolver']);

    // Mandados
    Route::get('/mandados', [MandadoController::class, 'index']);
    Route::post('/mandados', [MandadoController::class, 'store']);
    Route::patch('/mandados/{mandado}/estado', [MandadoController::class, 'actualizarEstado']);

    // Mensagens
    Route::get('/mensagens/inbox', [MensagemController::class, 'inbox']);
    Route::get('/mensagens/enviadas', [MensagemController::class, 'enviadas']);
    Route::post('/mensagens', [MensagemController::class, 'store']);
    Route::patch('/mensagens/{mensagem}/lida', [MensagemController::class, 'marcarLida']);
    Route::get('/mensagens/nao-lidas', [MensagemController::class, 'naoLidas']);

    // Notificações
    Route::get('/notificacoes', [NotificacaoController::class, 'index']);
    Route::get('/notificacoes/nao-lidas', [NotificacaoController::class, 'naoLidas']);
    Route::patch('/notificacoes/{notificacao}/lida', [NotificacaoController::class, 'marcarLida']);
    Route::patch('/notificacoes/marcar-todas', [NotificacaoController::class, 'marcarTodasLidas']);

    // Queixas (parte interna)
    Route::get('/queixas', [QueixaCidadaoController::class, 'index']);
    Route::post('/queixas/{queixa}/converter', [QueixaCidadaoController::class, 'converter']);
    Route::patch('/queixas/{queixa}/rejeitar', [QueixaCidadaoController::class, 'rejeitar']);

    // Relatórios
    Route::get('/relatorios', [RelatorioController::class, 'index']);
    Route::post('/relatorios/gerar', [RelatorioController::class, 'gerar']);

    // Turnos
    Route::get('/turnos', [TurnoController::class, 'turnos']);
    Route::get('/escala-turnos', [TurnoController::class, 'escala']);
    Route::post('/escala-turnos', [TurnoController::class, 'definirEscala']);

    // Logs
    Route::get('/logs', [LogController::class, 'index'])->middleware('permissao:logs.ver');

    // Configurações
    Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->middleware('permissao:configuracoes.gerir');
    Route::put('/configuracoes', [ConfiguracaoController::class, 'update'])->middleware('permissao:configuracoes.gerir');

    // PDF Export
    Route::get('/pdf/relatorio-criminalidade', [\App\Http\Controllers\ExportPdfController::class, 'relatorioCriminalidade']);
    Route::get('/pdf/ocorrencia/{ocorrencia}', [\App\Http\Controllers\ExportPdfController::class, 'fichaOcorrencia']);
    Route::get('/pdf/detencao/{detencao}', [\App\Http\Controllers\ExportPdfController::class, 'fichaDetencao']);
    Route::get('/pdf/agentes', [\App\Http\Controllers\ExportPdfController::class, 'listaAgentes']);
    Route::get('/pdf/alertas', [\App\Http\Controllers\ExportPdfController::class, 'relatorioAlertas']);

    });

// Endpoints públicos (cidadão)
Route::post('/cidadao/queixa', [QueixaCidadaoController::class, 'submeter']);
Route::get('/cidadao/queixa/{protocolo}', [QueixaCidadaoController::class, 'consultar']);