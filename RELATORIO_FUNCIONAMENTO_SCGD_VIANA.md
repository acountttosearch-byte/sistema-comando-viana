# Relatorio de Funcionamento do SCGD Viana

## 1. Enquadramento

O SCGD Viana e um sistema de comunicacao e gerenciamento de dados para apoio operacional ao Comando Municipal de Viana, em Luanda, Angola. O sistema organiza ocorrencias, agentes, unidades, investigacoes, detencoes, evidencias, patrulhas, alertas, despachos, mensagens, queixas e relatorios, respeitando a estrutura local de comando, esquadras e postos policiais.

O modelo operacional parte de uma entidade central, o Comando Municipal de Viana, com unidades subordinadas como esquadras e postos policiais. As areas de actuacao foram modeladas com bairros e zonas de patrulha ligados ao municipio de Viana, incluindo Viana Sede, Zango, Kikuxi, Vila Flor, Estalagem, Baia e Calumbo.

## 2. Perfis de Utilizador

Admin:
Tem acesso completo ao sistema, incluindo configuracoes, utilizadores, auditoria, cadastros e operacoes.

Comandante Municipal:
Tem visao global das ocorrencias, unidades, relatorios, despachos, alertas e desempenho operacional do municipio.

Perfil Chefe de Esquadra:
Gere a actividade da sua unidade. Ve ocorrencias, detencoes, investigacoes, patrulhas, viaturas, armamento e relatorios associados a sua esquadra ou posto.

Investigador:
Actua sobre investigacoes e processos criminais. Pode acompanhar ocorrencias que lhe foram atribuidas, inserir notas e consultar evidencias autorizadas.

Agente Operacional:
Regista ocorrencias, participa em patrulhas, responde a despachos e consulta os dados operacionais permitidos pelo seu fluxo de trabalho.

Operador de Atendimento:
Regista entradas iniciais, queixas e ocorrencias, mantendo o fluxo de atendimento sem acesso administrativo amplo.

## 3. Fluxo Principal de Ocorrencias

1. O operador ou agente regista uma ocorrencia com tipo de crime, prioridade, data, local, bairro, unidade responsavel e descricao.
2. A ocorrencia recebe uma numeracao padronizada com prefixo do municipio, como OC-VNA.
3. A ocorrencia entra no estado Registada ou Em Triagem, conforme o tratamento operacional.
4. Um comandante ou responsavel autorizado pode despachar a ocorrencia para uma unidade ou agente.
5. Se houver materia criminal que exija apuramento, a ocorrencia pode originar uma investigacao ou processo criminal.
6. Pessoas envolvidas podem ser associadas como suspeito, vitima ou testemunha.
7. Evidencias podem ser anexadas e acompanhadas por cadeia de custodia.
8. O caso pode evoluir para Resolvida, Encaminhada ao Tribunal ou Arquivada.

## 4. Regras de Negocio Aplicadas

Autenticacao:
Todas as rotas operacionais exigem utilizador autenticado. O login valida email, palavra-passe e estado activo do utilizador.

Autorizacao por perfil:
Cada modulo respeita o perfil do utilizador. Comandantes veem mais informacao, comandantes de esquadra ficam limitados a propria unidade, investigadores ficam limitados a investigacoes atribuidas e agentes a fluxos operacionais autorizados.

Escopo por unidade:
Registos sensiveis, como ocorrencias, detencoes, investigacoes, processos, evidencias, viaturas e armamento, sao filtrados por unidade quando o utilizador nao tem visao global.

Validacao de formularios:
Os campos obrigatorios continuam tecnicamente obrigatorios no HTML e no backend. A interface deixou de depender de asteriscos visuais, mas as regras permanecem activas no servidor.

Registo institucional de agentes:
Novas contas de agentes devem usar email institucional com dominio @policia-viana.ao, NIP no formato NIP-00000, BI angolano no formato 0012345678LA042 e telefone movel angolano coerente com o indicativo nacional. O codigo provincial do BI e validado contra a lista de 21 provincias de Angola configurada no sistema, e as letras digitadas em minusculas sao normalizadas para maiusculas.

Datas:
Ocorrencias e detencoes nao devem aceitar datas futuras quando o fluxo representa factos ja ocorridos. Patrulhas podem ser planeadas para datas futuras.

Hora da ocorrencia:
Quando informada, a hora da ocorrencia e validada em conjunto com a data. Se a ocorrencia for registada com a data actual, o sistema nao aceita uma hora futura.

Estados:
Cada entidade segue estados controlados. Ocorrencias usam estados como Registada, Em Triagem, Despachada, Em Investigacao, Resolvida, Encaminhada ao Tribunal e Arquivada. Detencoes usam Detido, Em Custodia, Apresentado ao Tribunal, Libertado e Transferido.

Integridade operacional:
Um despacho deve ter ocorrencia, prioridade, agente destino e unidade destino. Uma patrulha deve ter data, turno, unidade, zona, lider e agentes. Uma evidencia deve estar associada a uma ocorrencia e possuir descricao.

Proteccao contra abuso:
Os controladores evitam aceitar todos os campos enviados pelo cliente de forma indiscriminada. As entradas sao validadas e apenas campos esperados sao persistidos.

## 5. Modulos do Sistema

Dashboard:
Apresenta indicadores de ocorrencias, alertas, patrulhas, detencoes e actividade operacional, respeitando o perfil do utilizador.

Ocorrencias:
Centraliza o registo, consulta, triagem, despacho e acompanhamento de factos policiais.

Pessoas:
Mantem dados de suspeitos, vitimas e testemunhas, permitindo associacao com ocorrencias e detencoes.

Detencoes:
Regista detidos, motivo, local, data, agente responsavel, unidade e estado da detencao.

Evidencias:
Controla documentos, imagens, videos, audios e objectos ligados a ocorrencias, incluindo historico de custodia.

Investigacoes:
Permite abrir investigacoes, definir investigador, prazo e notas de diligencia.

Processos Criminais:
Organiza casos que evoluem para tratamento criminal mais formal, com estado e grau de confidencialidade.

Despachos:
Formaliza ordens ou encaminhamentos operacionais entre comando, unidade e agente responsavel.

Patrulhas:
Planeia equipas por data, turno, zona, unidade, lider, agentes e viatura.

Alertas:
Emite comunicacoes operacionais para unidades activas, como suspeitos procurados, viaturas roubadas e pessoas desaparecidas.

Viaturas:
Gere frota, estado operacional, unidade proprietaria e atribuicoes.

Armamento:
Controla armas por tipo, numero de serie, estado, unidade e atribuicoes.

Mensagens:
Permite comunicacao interna entre agentes.

Queixas de Cidadao:
Recebe queixas, classifica o estado e pode converter uma queixa em ocorrencia quando for procedente.

Relatorios e PDF:
Gera relatorios de criminalidade, alertas, agentes, ocorrencias, detencoes, investigacoes e processos. Os rodapes de copyright foram removidos conforme solicitado.

## 6. Coerencia de Angola e Viana

O sistema usa nomenclatura adaptada ao contexto policial angolano e municipal:

- Entidade principal como Comando Municipal de Viana.
- Unidades como esquadras e postos policiais.
- Perfis operacionais como Comandante Municipal, Chefe de Esquadra, Investigador, Agente Operacional e Operador de Atendimento.
- Patentes sem uso de funcoes de comando como graduacao isolada.
- Prefixos operacionais com identificador VNA para ocorrencias, detencoes e investigacoes.
- Bairros e zonas de patrulha ligados ao municipio de Viana.

## 7. Pontos de Atencao Futuros

Auditoria:
Registar historico completo de criacao, edicao, eliminacao logica, mudanca de estado e acesso a documentos sensiveis.

Assinaturas:
PDFs podem futuramente incluir assinatura digital, assinatura manual ou carimbo institucional sem footer de copyright.

Dados pessoais:
Reforcar politica de privacidade para BI, telefone, morada e informacao criminal.

Fluxos legais:
Validar prazos de apresentacao ao tribunal, transferencia de custodia e encerramento processual conforme regras internas da instituicao.

Relatorios:
Padronizar relatorios por periodo, unidade, tipo de crime, bairro, prioridade e taxa de resolucao.

## 8. Conclusao

O SCGD Viana funciona como uma plataforma operacional integrada para transformar atendimentos, ocorrencias e queixas em fluxos acompanhaveis por unidade, agente e estado. As correccoes recentes reforcam validacao, coerencia de perfis e patentes, remocao de elementos visuais desnecessarios, controlo de acesso por perfil e regras de negocio mais resistentes a erros de fluxo.
