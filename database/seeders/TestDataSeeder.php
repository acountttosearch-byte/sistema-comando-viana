<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Agente;
use App\Models\Pessoa;
use App\Models\Ocorrencia;
use App\Models\EnvolvimentoOcorrencia;
use App\Models\Detencao;
use App\Models\Evidencia;
use App\Models\Investigacao;
use App\Models\NotaInvestigacao;
use App\Models\Despacho;
use App\Models\Patrulha;
use App\Models\PatrulhaIncidente;
use App\Models\Alerta;
use App\Models\AlertaDestinatario;
use App\Models\Viatura;
use App\Models\ViaturaAtribuicao;
use App\Models\Armamento;
use App\Models\ArmamentoAtribuicao;
use App\Models\Mensagem;
use App\Models\Notificacao;
use App\Models\QueixaCidadao;
use App\Models\EscalaTurno;
use App\Models\GeolocalizacaoOcorrencia;
use App\Models\CadeiaCustodia;
use App\Models\Relatorio;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚔 A gerar dados de teste...');

        // ══════════════════════════════
        // 1. AGENTES (30)
        // ══════════════════════════════
        $this->command->info('   → Agentes...');

        $agentesConfig = [
            ['nome' => 'Comissário António Fernandes', 'cargo' => 'Comandante Municipal', 'unidade' => 1, 'patente' => 1, 'perfil' => 2],
            ['nome' => 'Subcomissário Carlos Domingos', 'cargo' => 'Chefe de Esquadra', 'unidade' => 2, 'patente' => 2, 'perfil' => 3],
            ['nome' => 'Subcomissário Manuel Pereira', 'cargo' => 'Chefe de Esquadra', 'unidade' => 3, 'patente' => 2, 'perfil' => 3],
            ['nome' => 'Intendente Rosa Santos', 'cargo' => 'Chefe de Esquadra', 'unidade' => 4, 'patente' => 3, 'perfil' => 3],
            ['nome' => 'Intendente Pedro Correia', 'cargo' => 'Chefe de Esquadra', 'unidade' => 5, 'patente' => 3, 'perfil' => 3],
            ['nome' => 'Inspector João Baptista', 'cargo' => 'Investigador', 'unidade' => 2, 'patente' => 5, 'perfil' => 4],
            ['nome' => 'Inspector Maria Tavares', 'cargo' => 'Investigador', 'unidade' => 3, 'patente' => 5, 'perfil' => 4],
            ['nome' => 'Subinspector Daniel Gonçalves', 'cargo' => 'Investigador', 'unidade' => 4, 'patente' => 6, 'perfil' => 4],
            ['nome' => 'Inspector Alberto Mendes', 'cargo' => 'Investigador', 'unidade' => 5, 'patente' => 5, 'perfil' => 4],
        ];

        $agentes = [];
        $nipBase = 1001;

        foreach ($agentesConfig as $c) {
            $nipBase++;
            $emailBase = strtolower(str_replace(' ', '.', explode(' ', $c['nome'])[1] ?? 'ag'));
            $user = User::create([
                'email' => $emailBase . $nipBase . '@policia-viana.ao',
                'password' => Hash::make(config('auth.default_agent_password')),
                'perfil_id' => $c['perfil'], 'estado' => 'activo',
            ]);
            $agentes[] = Agente::create([
                'user_id' => $user->id, 'nome' => $c['nome'],
                'nip' => 'NIP-' . str_pad($nipBase, 5, '0', STR_PAD_LEFT),
                'bi' => fake()->numerify('##########LA###'),
                'data_nascimento' => fake()->dateTimeBetween('-50 years', '-25 years'),
                'sexo' => str_contains($c['nome'], 'Maria') || str_contains($c['nome'], 'Rosa') ? 'F' : 'M',
                'telefone' => '9' . fake()->numerify('########'),
                'patente_id' => $c['patente'], 'cargo' => $c['cargo'],
                'unidade_id' => $c['unidade'],
                'data_admissao' => fake()->dateTimeBetween('-15 years', '-1 year'),
                'estado' => 'activo',
            ]);
        }

        // 21 agentes operacionais
        $nomesM = ['Francisco', 'Domingos', 'Sebastião', 'Gilberto', 'Tomás', 'Mateus', 'Simão', 'Ricardo', 'André', 'Fernando', 'Paulo', 'Miguel'];
        $nomesF = ['Ana', 'Teresa', 'Luísa', 'Helena', 'Joana', 'Marta'];
        $apelidos = ['Silva', 'Santos', 'Neto', 'Machado', 'Costa', 'Lopes', 'Sousa', 'Oliveira', 'Bumba', 'Tchikela', 'Kiala', 'Mukuta', 'Ndala'];

        for ($i = 0; $i < 21; $i++) {
            $nipBase++;
            $sexo = fake()->randomElement(['M', 'M', 'M', 'F']);
            $nome = ($sexo === 'M' ? fake()->randomElement($nomesM) : fake()->randomElement($nomesF))
                  . ' ' . fake()->randomElement($apelidos) . ' ' . fake()->randomElement($apelidos);
            $cargo = fake()->randomElement(['Agente Operacional', 'Agente Operacional', 'Agente Operacional', 'Operador de Atendimento']);

            $user = User::create([
                'email' => 'agente' . $nipBase . '@policia-viana.ao',
                'password' => Hash::make(config('auth.default_agent_password')),
                'perfil_id' => $cargo === 'Operador de Atendimento' ? 6 : 5,
                'estado' => 'activo',
            ]);
            $agentes[] = Agente::create([
                'user_id' => $user->id, 'nome' => $nome,
                'nip' => 'NIP-' . str_pad($nipBase, 5, '0', STR_PAD_LEFT),
                'bi' => fake()->numerify('##########LA###'),
                'data_nascimento' => fake()->dateTimeBetween('-40 years', '-22 years'),
                'sexo' => $sexo, 'telefone' => '9' . fake()->numerify('########'),
                'patente_id' => fake()->numberBetween(7, 10), 'cargo' => $cargo,
                'unidade_id' => fake()->numberBetween(2, 6),
                'data_admissao' => fake()->dateTimeBetween('-10 years', '-6 months'),
                'estado' => fake()->randomElement(['activo', 'activo', 'activo', 'activo', 'inactivo']),
            ]);
        }

        $agentesActivos = collect($agentes)->filter(fn($a) => $a->estado === 'activo');
        $agentesIds = $agentesActivos->pluck('id')->toArray();
        $investigadoresIds = collect($agentes)->filter(fn($a) => $a->cargo === 'Investigador')->pluck('id')->toArray();
        $chefesIds = collect($agentes)->filter(fn($a) => in_array($a->cargo, ['Chefe de Esquadra', 'Comandante Municipal']))->pluck('id')->toArray();

        // ══════════════════════════════
        // 2. PESSOAS (80)
        // ══════════════════════════════
        $this->command->info('   → Pessoas...');
        $pessoas = Pessoa::factory()->count(80)->create();
        $pessoasIds = $pessoas->pluck('id')->toArray();

        // ══════════════════════════════
        // 3. OCORRÊNCIAS (150)
        // ══════════════════════════════
        $this->command->info('   → Ocorrências...');
        $ocorrencias = collect();
        for ($i = 0; $i < 150; $i++) {
            $oc = Ocorrencia::factory()->create([
                'agente_registo_id' => fake()->randomElement($agentesIds),
                'agente_responsavel_id' => fake()->boolean(70) ? fake()->randomElement($agentesIds) : null,
            ]);
            $ocorrencias->push($oc);
        }
        $ocorrenciasIds = $ocorrencias->pluck('id')->toArray();

        // ══════════════════════════════
        // 4. ENVOLVIMENTOS
        // ══════════════════════════════
        $this->command->info('   → Envolvimentos...');
        foreach ($ocorrencias as $oc) {
            $n = fake()->numberBetween(1, 3);
            $usadas = [];
            for ($j = 0; $j < $n; $j++) {
                $pid = fake()->randomElement($pessoasIds);
                if (in_array($pid, $usadas)) continue;
                $usadas[] = $pid;
                EnvolvimentoOcorrencia::create([
                    'ocorrencia_id' => $oc->id, 'pessoa_id' => $pid,
                    'tipo_envolvimento_id' => $j === 0 ? fake()->randomElement([1, 2]) : fake()->randomElement([2, 3]),
                ]);
            }
        }

        // ══════════════════════════════
        // 5. GEOLOCALIZAÇÕES
        // ══════════════════════════════
        $this->command->info('   → Geolocalizações...');
        $bairrosIds = DB::table('bairros')->pluck('id')->toArray();
        foreach ($ocorrencias as $oc) {
            if (fake()->boolean(60)) {
                GeolocalizacaoOcorrencia::create([
                    'ocorrencia_id' => $oc->id,
                    'latitude' => -8.9035 + fake()->randomFloat(4, -0.05, 0.05),
                    'longitude' => 13.1740 + fake()->randomFloat(4, -0.05, 0.05),
                    'bairro_id' => fake()->randomElement($bairrosIds),
                ]);
            }
        }

        // ══════════════════════════════
        // 6. DETENÇÕES (40)
        // ══════════════════════════════
        $this->command->info('   → Detenções...');
        for ($i = 0; $i < 40; $i++) {
            $agId = fake()->randomElement($agentesIds);
            $ag = Agente::find($agId);
            Detencao::factory()->create([
                'pessoa_id' => fake()->randomElement($pessoasIds),
                'ocorrencia_id' => fake()->randomElement($ocorrenciasIds),
                'agente_responsavel_id' => $agId,
                'unidade_id' => $ag->unidade_id,
            ]);
        }

        // ══════════════════════════════
        // 7. EVIDÊNCIAS (100)
        // ══════════════════════════════
        $this->command->info('   → Evidências...');
        $evidencias = collect();
        for ($i = 0; $i < 100; $i++) {
            $ev = Evidencia::factory()->create([
                'ocorrencia_id' => fake()->randomElement($ocorrenciasIds),
                'agente_registo_id' => fake()->randomElement($agentesIds),
            ]);
            $evidencias->push($ev);
        }

        // Cadeia de custódia
        $this->command->info('   → Cadeia de custódia...');
        for ($i = 0; $i < 30; $i++) {
            CadeiaCustodia::create([
                'evidencia_id' => $evidencias->random()->id,
                'agente_origem_id' => fake()->randomElement($agentesIds),
                'agente_destino_id' => fake()->randomElement($agentesIds),
                'local_origem' => fake()->randomElement(['Cofre A', 'Sala de evidências']),
                'local_destino' => fake()->randomElement(['Laboratório forense', 'Tribunal Provincial']),
                'data_transferencia' => fake()->dateTimeBetween('-3 months', 'now'),
                'motivo' => fake()->randomElement(['Análise forense', 'Apresentação ao tribunal']),
            ]);
        }

        // ══════════════════════════════
        // 8. INVESTIGAÇÕES (25)
        // ══════════════════════════════
        $this->command->info('   → Investigações...');
        for ($i = 0; $i < 25; $i++) {
            $inv = Investigacao::factory()->create([
                'ocorrencia_id' => fake()->randomElement($ocorrenciasIds),
                'investigador_id' => fake()->randomElement($investigadoresIds),
            ]);
            for ($n = 0; $n < fake()->numberBetween(1, 3); $n++) {
                NotaInvestigacao::create([
                    'investigacao_id' => $inv->id, 'agente_id' => $inv->investigador_id,
                    'titulo' => fake()->randomElement(['Depoimento recolhido', 'Análise CCTV', 'Suspeito identificado', 'Nova pista']),
                    'conteudo' => fake()->randomElement([
                        'Testemunha confirmou presença do suspeito às 22h.',
                        'Imagens de videovigilância mostram dois indivíduos.',
                        'Suspeito identificado com antecedentes criminais.',
                    ]),
                    'confidencial' => fake()->boolean(20),
                    'created_at' => fake()->dateTimeBetween($inv->data_inicio, 'now'),
                ]);
            }
        }

        // ══════════════════════════════
        // 9. DESPACHOS (35)
        // ══════════════════════════════
        $this->command->info('   → Despachos...');
        for ($i = 0; $i < 35; $i++) {
            $agDest = fake()->randomElement($agentesIds);
            $ag = Agente::find($agDest);
            $dataDesp = fake()->dateTimeBetween('-4 months', 'now');
            Despacho::create([
                'ocorrencia_id' => fake()->randomElement($ocorrenciasIds),
                'prioridade' => fake()->randomElement(['baixa', 'media', 'alta', 'critica']),
                'despachado_para' => $agDest,
                'despachado_por' => fake()->randomElement($chefesIds ?: $agentesIds),
                'unidade_destino' => $ag->unidade_id,
                'instrucoes' => fake()->randomElement(['Dirigir-se ao local imediatamente.', 'Proceder com investigação.', 'Reforçar patrulha na zona.', null]),
                'estado' => fake()->randomElement(['pendente', 'aceite', 'em_curso', 'concluido', 'concluido']),
                'data_despacho' => $dataDesp,
                'data_resposta' => fake()->boolean(60) ? fake()->dateTimeBetween($dataDesp, 'now') : null,
                'tempo_resposta_minutos' => fake()->boolean(60) ? fake()->numberBetween(5, 240) : null,
            ]);
        }

        // ══════════════════════════════
        // 10. VIATURAS (15)
        // ══════════════════════════════
        $this->command->info('   → Viaturas...');
        $viaturas = Viatura::factory()->count(15)->create();
        foreach ($viaturas->take(8) as $v) {
            ViaturaAtribuicao::create([
                'viatura_id' => $v->id, 'agente_id' => fake()->randomElement($agentesIds),
                'data_saida' => fake()->dateTimeBetween('-1 month', 'now'),
                'quilometragem_saida' => $v->quilometragem - fake()->numberBetween(100, 500),
            ]);
        }

        // ══════════════════════════════
        // 11. ARMAMENTO (25)
        // ══════════════════════════════
        $this->command->info('   → Armamento...');
        $armas = Armamento::factory()->count(25)->create();
        foreach ($armas->take(18) as $arma) {
            ArmamentoAtribuicao::create([
                'armamento_id' => $arma->id, 'agente_id' => fake()->randomElement($agentesIds),
                'data_atribuicao' => fake()->dateTimeBetween('-6 months', 'now'),
                'estado' => 'atribuido',
            ]);
        }

        // ══════════════════════════════
        // 12. PATRULHAS (20)
        // ══════════════════════════════
        $this->command->info('   → Patrulhas...');
        $zonasIds = DB::table('zonas_patrulha')->pluck('id')->toArray();
        $viaturasIds = $viaturas->pluck('id')->toArray();

        for ($i = 0; $i < 20; $i++) {
            $lider = fake()->randomElement($agentesIds);
            $agObj = Agente::find($lider);
            $patrulha = Patrulha::create([
                'data' => fake()->dateTimeBetween('-2 months', '+3 days'),
                'turno_id' => fake()->numberBetween(1, 3),
                'zona_id' => fake()->randomElement($zonasIds),
                'viatura_id' => fake()->boolean(70) ? fake()->randomElement($viaturasIds) : null,
                'agente_lider_id' => $lider, 'unidade_id' => $agObj->unidade_id,
                'estado' => fake()->randomElement(['planeada', 'em_curso', 'concluida', 'concluida', 'concluida']),
                'hora_inicio' => fake()->boolean(70) ? fake()->time('H:i') : null,
                'hora_fim' => fake()->boolean(50) ? fake()->time('H:i') : null,
            ]);
            $patAgs = fake()->randomElements($agentesIds, min(fake()->numberBetween(2, 4), count($agentesIds)));
            if (!in_array($lider, $patAgs)) array_unshift($patAgs, $lider);
            foreach ($patAgs as $pa) {
                DB::table('patrulha_agentes')->insert([
                    'patrulha_id' => $patrulha->id, 'agente_id' => $pa,
                    'funcao' => $pa === $lider ? 'lider' : 'apoio',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            if (fake()->boolean(40)) {
                PatrulhaIncidente::create([
                    'patrulha_id' => $patrulha->id,
                    'ocorrencia_id' => fake()->boolean(50) ? fake()->randomElement($ocorrenciasIds) : null,
                    'hora_registo' => fake()->time('H:i'), 'local' => 'Zona do mercado',
                    'descricao' => fake()->randomElement(['Indivíduo suspeito abordado.', 'Perturbação resolvida.', 'Auxílio a cidadão.']),
                ]);
            }
        }

        // ══════════════════════════════
        // 13. ALERTAS (8)
        // ══════════════════════════════
        $this->command->info('   → Alertas...');
        $unidadesIds = DB::table('unidades')->where('estado', 'activo')->pluck('id')->toArray();
        $alertasData = [
            ['tipo' => 1, 'titulo' => 'Procura-se suspeito de homicídio no Zango 3', 'desc' => 'Indivíduo do sexo masculino, 1.80m, magro, cicatriz no rosto. Considerado perigoso.', 'prio' => 'urgente'],
            ['tipo' => 1, 'titulo' => 'Suspeito de assaltos em série em Viana Sede', 'desc' => 'Homem de estatura média, usa boné preto e mochila azul.', 'prio' => 'alta'],
            ['tipo' => 2, 'titulo' => 'Toyota Hilux branca roubada', 'desc' => 'Matrícula LD-45-78-AC. Última vez vista na direcção do Zango.', 'prio' => 'alta'],
            ['tipo' => 3, 'titulo' => 'Criança desaparecida no Zango 2', 'desc' => 'Menina de 8 anos, Ana Sebastião. Uniforme escolar azul e branco.', 'prio' => 'urgente'],
            ['tipo' => 3, 'titulo' => 'Idoso desaparecido na Estalagem', 'desc' => 'Homem de 72 anos, sofre de demência. Camisa branca, calças escuras.', 'prio' => 'alta'],
            ['tipo' => 4, 'titulo' => 'Reforço policial no mercado do Zango', 'desc' => 'Actividade criminosa crescente. Reforçar presença nos próximos 7 dias.', 'prio' => 'normal'],
            ['tipo' => 1, 'titulo' => 'Procurado por violação no Kikuxi', 'desc' => 'Mandado de captura emitido pelo tribunal provincial.', 'prio' => 'urgente'],
            ['tipo' => 2, 'titulo' => 'Motorizada Honda roubada em Vila Flor', 'desc' => 'Honda XR 150, vermelha e preta. Furtada junto ao mercado.', 'prio' => 'normal'],
        ];

        foreach ($alertasData as $ad) {
            $alerta = Alerta::create([
                'tipo_alerta_id' => $ad['tipo'], 'titulo' => $ad['titulo'],
                'descricao' => $ad['desc'], 'prioridade' => $ad['prio'],
                'pessoa_id' => fake()->boolean(30) ? fake()->randomElement($pessoasIds) : null,
                'ocorrencia_id' => fake()->boolean(40) ? fake()->randomElement($ocorrenciasIds) : null,
                'estado' => fake()->randomElement(['activo', 'activo', 'activo', 'resolvido']),
                'criado_por' => fake()->randomElement($agentesIds),
                'data_expiracao' => fake()->boolean(50) ? fake()->dateTimeBetween('now', '+30 days') : null,
            ]);
            foreach ($unidadesIds as $uid) {
                AlertaDestinatario::create([
                    'alerta_id' => $alerta->id, 'unidade_id' => $uid,
                    'visualizado' => fake()->boolean(60),
                    'data_visualizacao' => fake()->boolean(60) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                    'visualizado_por' => fake()->boolean(60) ? fake()->randomElement($agentesIds) : null,
                ]);
            }
        }

        // ══════════════════════════════
        // 14. MENSAGENS (30)
        // ══════════════════════════════
        $this->command->info('   → Mensagens...');
        $msgData = [
            ['titulo' => 'Reforço de patrulha solicitado', 'msg' => 'Solicito reforço para a zona do mercado do Zango.'],
            ['titulo' => 'Relatório pendente', 'msg' => 'O relatório da ocorrência OC-VNA-2025-00012 está pendente.'],
            ['titulo' => 'Transferência de evidências', 'msg' => 'Evidências do caso 00045 serão transferidas amanhã às 10h.'],
            ['titulo' => 'Reunião operacional', 'msg' => 'Reunião agendada para sexta às 08h no Comando.'],
            ['titulo' => 'Actualização de caso', 'msg' => 'Suspeito do caso de assalto no Kikuxi identificado.'],
            ['titulo' => 'Viatura em manutenção', 'msg' => 'Viatura LD-45-78-AC em manutenção. Retorno em 3 dias.'],
            ['titulo' => 'Novo agente na esquadra', 'msg' => 'Agente NIP-01025 transferido para esta esquadra.'],
            ['titulo' => 'Operação especial', 'msg' => 'Operação de fiscalização planeada para sábado no Zango.'],
        ];
        for ($i = 0; $i < 30; $i++) {
            $m = fake()->randomElement($msgData);
            Mensagem::create([
                'remetente_id' => fake()->randomElement($agentesIds),
                'destinatario_id' => fake()->randomElement($agentesIds),
                'titulo' => $m['titulo'], 'mensagem' => $m['msg'],
                'lida' => fake()->boolean(60),
                'data_leitura' => fake()->boolean(60) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'prioridade' => fake()->randomElement(['normal', 'normal', 'normal', 'urgente']),
            ]);
        }

        // ══════════════════════════════
        // 15. QUEIXAS CIDADÃO (15)
        // ══════════════════════════════
        $this->command->info('   → Queixas...');
        $qxNum = 0;
        $qxData = [
            ['tipo' => 'Roubo', 'desc' => 'Fui assaltado quando regressava do trabalho.'],
            ['tipo' => 'Furto', 'desc' => 'Entraram na minha residência e furtaram o televisor.'],
            ['tipo' => 'Agressão', 'desc' => 'O meu vizinho agrediu-me sem motivo.'],
            ['tipo' => 'Barulho', 'desc' => 'Vizinhos fazem barulho excessivo todas as noites.'],
            ['tipo' => 'Vandalismo', 'desc' => 'Partiram o vidro do meu carro durante a noite.'],
            ['tipo' => 'Violência doméstica', 'desc' => 'O meu marido agride-me frequentemente.'],
        ];
        // Nomes consistentes: nome feminino para queixas de VD femininas, masculinos para o resto
        $nomesM = ['Manuel Santos', 'Pedro Costa', 'João Pereira', 'Francisco Domingos', 'Sebastião Lopes', 'Gilberto Fernandes'];
        $nomesF = ['Ana Domingos', 'Maria Fernandes', 'Teresa Lopes', 'Joana Correia', 'Esperança Silva', 'Helena Bumba'];
        for ($i = 0; $i < 15; $i++) {
            $qxNum++;
            $qx = fake()->randomElement($qxData);
            $estado = fake()->randomElement(['recebida', 'recebida', 'em_analise', 'convertida', 'rejeitada']);
            QueixaCidadao::create([
                'protocolo' => 'QX-' . date('Y') . '-' . str_pad($qxNum, 5, '0', STR_PAD_LEFT),
                'nome_cidadao' => ($qx['tipo'] === 'Violência doméstica') ? fake()->randomElement($nomesF) : fake()->randomElement(array_merge($nomesM, $nomesF)),
                'bi' => fake()->boolean(60) ? fake()->numerify('##########LA###') : null,
                'telefone' => '9' . fake()->numerify('########'),
                'tipo_queixa' => $qx['tipo'], 'descricao' => $qx['desc'],
                'local' => fake()->randomElement(['Zango 3', 'Viana Sede', 'Kikuxi', 'Vila Flor', 'Estalagem', 'Baía']),
                'estado' => $estado,
                'ocorrencia_id' => $estado === 'convertida' ? fake()->randomElement($ocorrenciasIds) : null,
                'analisado_por' => in_array($estado, ['convertida', 'rejeitada', 'em_analise']) ? fake()->randomElement($agentesIds) : null,
                'justificacao_rejeicao' => $estado === 'rejeitada' ? 'Sem fundamento suficiente.' : null,
            ]);
        }

        // ══════════════════════════════
        // 16. ESCALA DE TURNOS (7 dias)
        // ══════════════════════════════
        $this->command->info('   → Escala de turnos...');
        for ($dia = 0; $dia < 7; $dia++) {
            $data = now()->addDays($dia)->toDateString();
            foreach ($agentesActivos->random(min(15, $agentesActivos->count())) as $ag) {
                EscalaTurno::create([
                    'agente_id' => $ag->id, 'turno_id' => fake()->numberBetween(1, 3),
                    'data' => $data, 'unidade_id' => $ag->unidade_id, 'estado' => 'confirmado',
                ]);
            }
        }

        // ══════════════════════════════
        // 17. NOTIFICAÇÕES
        // ══════════════════════════════
        $this->command->info('   → Notificações...');
        $notifs = [
            ['tipo' => 'alerta', 'titulo' => 'Novo alerta emitido', 'msg' => 'Alerta de suspeito procurado emitido.'],
            ['tipo' => 'ocorrencia', 'titulo' => 'Ocorrência de alta prioridade', 'msg' => 'Ocorrência CRÍTICA registada no Zango 3.'],
            ['tipo' => 'sistema', 'titulo' => 'Backup concluído', 'msg' => 'Backup automático concluído com sucesso.'],
        ];
        foreach ($notifs as $n) {
            Notificacao::create([
                'user_id' => 1, 'tipo' => $n['tipo'], 'titulo' => $n['titulo'],
                'mensagem' => $n['msg'], 'lida' => fake()->boolean(40),
            ]);
        }

        // ══════════════════════════════
        // 18. RELATÓRIOS (3)
        // ══════════════════════════════
        $this->command->info('   → Relatórios...');
        for ($m = 1; $m <= 3; $m++) {
            $inicio = now()->subMonths($m)->startOfMonth();
            $fim = now()->subMonths($m)->endOfMonth();
            Relatorio::create([
                'tipo_relatorio_id' => 1, 'periodo_inicio' => $inicio, 'periodo_fim' => $fim,
                'gerado_por' => 1,
                'dados' => ['total_ocorrencias' => fake()->numberBetween(30, 80), 'taxa_resolucao' => fake()->randomFloat(1, 40, 85)],
                'created_at' => $fim,
            ]);
        }

        // ══════════════════════════════
        // RESUMO
        // ══════════════════════════════
        $this->command->newLine();
        $this->command->info('✅ DADOS DE TESTE CRIADOS!');
        $this->command->table(['Entidade', 'Qtd'], [
            ['Agentes', count($agentes)],
            ['Pessoas', 80],
            ['Ocorrências', 150],
            ['Envolvimentos', DB::table('envolvimento_ocorrencia')->count()],
            ['Geolocalizações', DB::table('geolocalizacao_ocorrencias')->count()],
            ['Detenções', 40],
            ['Evidências', 100],
            ['Cadeia Custódia', 30],
            ['Investigações', 25],
            ['Despachos', 35],
            ['Viaturas', 15],
            ['Armamento', 25],
            ['Patrulhas', 20],
            ['Alertas', 8],
            ['Mensagens', 30],
            ['Queixas', 15],
        ]);
        $this->command->info('📋 Login Admin: admin@policia-viana.ao / Admin@2025');
        $this->command->info('📋 Login Agentes: *@policia-viana.ao / ' . config('auth.default_agent_password'));
    }
}
