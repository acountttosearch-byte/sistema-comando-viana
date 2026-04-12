<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════
        // TIPOS DE UNIDADE
        // ═══════════════════════════════════════
        DB::table('tipos_unidade')->insert([
            ['id' => 1, 'nome' => 'Comando Municipal', 'descricao' => 'Comando central do município', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Esquadra', 'descricao' => 'Unidade operacional', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Posto Policial', 'descricao' => 'Unidade de proximidade', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // UNIDADES POLICIAIS DE VIANA
        // (Distritos urbanos 2025: Viana Sede, Zango, Estalagem, Kikuxi, Vila Flor, Baía)
        // ═══════════════════════════════════════
        DB::table('unidades')->insert([
            ['id' => 1, 'nome' => 'Comando Municipal de Viana', 'tipo_unidade_id' => 1, 'unidade_pai_id' => null, 'endereco' => 'Viana Sede', 'municipio' => 'Viana', 'telefone' => null, 'email' => null, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Esquadra de Viana Sede', 'tipo_unidade_id' => 2, 'unidade_pai_id' => 1, 'endereco' => 'Viana Sede', 'municipio' => 'Viana', 'telefone' => null, 'email' => null, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Esquadra do Zango', 'tipo_unidade_id' => 2, 'unidade_pai_id' => 1, 'endereco' => 'Zango', 'municipio' => 'Viana', 'telefone' => null, 'email' => null, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Esquadra de Kikuxi', 'tipo_unidade_id' => 2, 'unidade_pai_id' => 1, 'endereco' => 'Kikuxi', 'municipio' => 'Viana', 'telefone' => null, 'email' => null, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Esquadra de Vila Flor', 'tipo_unidade_id' => 2, 'unidade_pai_id' => 1, 'endereco' => 'Vila Flor', 'municipio' => 'Viana', 'telefone' => null, 'email' => null, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nome' => 'Posto Policial Zango 3', 'tipo_unidade_id' => 3, 'unidade_pai_id' => 3, 'endereco' => 'Zango 3', 'municipio' => 'Viana', 'telefone' => null, 'email' => null, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // PATENTES
        // ═══════════════════════════════════════
        DB::table('patentes')->insert([
            ['id' => 1, 'nome' => 'Comissário', 'abreviatura' => 'COM', 'nivel_hierarquico' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Subcomissário', 'abreviatura' => 'SCOM', 'nivel_hierarquico' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Intendente', 'abreviatura' => 'INT', 'nivel_hierarquico' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Subintendente', 'abreviatura' => 'SINT', 'nivel_hierarquico' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Inspector', 'abreviatura' => 'INSP', 'nivel_hierarquico' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nome' => 'Subinspector', 'abreviatura' => 'SINSP', 'nivel_hierarquico' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nome' => 'Chefe', 'abreviatura' => 'CH', 'nivel_hierarquico' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'nome' => 'Subchefe', 'abreviatura' => 'SCH', 'nivel_hierarquico' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'nome' => 'Agente de 1ª Classe', 'abreviatura' => 'AG1', 'nivel_hierarquico' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'nome' => 'Agente de 2ª Classe', 'abreviatura' => 'AG2', 'nivel_hierarquico' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // PERFIS
        // ═══════════════════════════════════════
        DB::table('perfis')->insert([
            ['id' => 1, 'nome' => 'admin', 'descricao' => 'Administrador do Sistema', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'comandante', 'descricao' => 'Comandante Municipal', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'chefe_esquadra', 'descricao' => 'Chefe de Esquadra', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'investigador', 'descricao' => 'Investigador Criminal', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'agente', 'descricao' => 'Agente Operacional', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nome' => 'operador', 'descricao' => 'Operador de Atendimento', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // PERMISSÕES
        // ═══════════════════════════════════════
        $permissoes = [
            ['nome' => 'users.ver', 'descricao' => 'Ver utilizadores', 'modulo' => 'utilizadores'],
            ['nome' => 'users.criar', 'descricao' => 'Criar utilizadores', 'modulo' => 'utilizadores'],
            ['nome' => 'users.editar', 'descricao' => 'Editar utilizadores', 'modulo' => 'utilizadores'],
            ['nome' => 'users.apagar', 'descricao' => 'Desactivar utilizadores', 'modulo' => 'utilizadores'],
            ['nome' => 'ocorrencias.ver', 'descricao' => 'Ver ocorrências', 'modulo' => 'ocorrencias'],
            ['nome' => 'ocorrencias.criar', 'descricao' => 'Criar ocorrências', 'modulo' => 'ocorrencias'],
            ['nome' => 'ocorrencias.editar', 'descricao' => 'Editar ocorrências', 'modulo' => 'ocorrencias'],
            ['nome' => 'ocorrencias.apagar', 'descricao' => 'Arquivar ocorrências', 'modulo' => 'ocorrencias'],
            ['nome' => 'ocorrencias.todas', 'descricao' => 'Ver todas as unidades', 'modulo' => 'ocorrencias'],
            ['nome' => 'detencoes.ver', 'descricao' => 'Ver detenções', 'modulo' => 'detencoes'],
            ['nome' => 'detencoes.criar', 'descricao' => 'Registar detenções', 'modulo' => 'detencoes'],
            ['nome' => 'detencoes.editar', 'descricao' => 'Editar detenções', 'modulo' => 'detencoes'],
            ['nome' => 'evidencias.ver', 'descricao' => 'Ver evidências', 'modulo' => 'evidencias'],
            ['nome' => 'evidencias.criar', 'descricao' => 'Adicionar evidências', 'modulo' => 'evidencias'],
            ['nome' => 'evidencias.transferir', 'descricao' => 'Transferir custódia', 'modulo' => 'evidencias'],
            ['nome' => 'investigacoes.ver', 'descricao' => 'Ver investigações', 'modulo' => 'investigacoes'],
            ['nome' => 'investigacoes.criar', 'descricao' => 'Criar investigações', 'modulo' => 'investigacoes'],
            ['nome' => 'investigacoes.editar', 'descricao' => 'Actualizar investigações', 'modulo' => 'investigacoes'],
            ['nome' => 'patrulhas.ver', 'descricao' => 'Ver patrulhas', 'modulo' => 'patrulhas'],
            ['nome' => 'patrulhas.criar', 'descricao' => 'Planear patrulhas', 'modulo' => 'patrulhas'],
            ['nome' => 'alertas.ver', 'descricao' => 'Ver alertas', 'modulo' => 'alertas'],
            ['nome' => 'alertas.criar', 'descricao' => 'Criar alertas', 'modulo' => 'alertas'],
            ['nome' => 'relatorios.ver', 'descricao' => 'Ver relatórios', 'modulo' => 'relatorios'],
            ['nome' => 'relatorios.gerar', 'descricao' => 'Gerar relatórios', 'modulo' => 'relatorios'],
            ['nome' => 'viaturas.ver', 'descricao' => 'Ver viaturas', 'modulo' => 'viaturas'],
            ['nome' => 'viaturas.gerir', 'descricao' => 'Gerir viaturas', 'modulo' => 'viaturas'],
            ['nome' => 'armamento.ver', 'descricao' => 'Ver armamento', 'modulo' => 'armamento'],
            ['nome' => 'armamento.gerir', 'descricao' => 'Gerir armamento', 'modulo' => 'armamento'],
            ['nome' => 'logs.ver', 'descricao' => 'Ver logs do sistema', 'modulo' => 'auditoria'],
            ['nome' => 'configuracoes.gerir', 'descricao' => 'Gerir configurações', 'modulo' => 'configuracoes'],
        ];

        foreach ($permissoes as $p) {
            DB::table('permissoes')->insert(array_merge($p, ['created_at' => now(), 'updated_at' => now()]));
        }

        // Admin tem todas as permissões
        $todasPermissoes = DB::table('permissoes')->pluck('id');
        foreach ($todasPermissoes as $permId) {
            DB::table('perfil_permissoes')->insert([
                'perfil_id' => 1, 'permissao_id' => $permId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ═══════════════════════════════════════
        // UTILIZADOR ADMIN
        // ═══════════════════════════════════════
        DB::table('users')->insert([
            'id' => 1, 'email' => 'admin@policia-viana.ao',
            'password' => Hash::make('Admin@2025'),
            'perfil_id' => 1, 'estado' => 'activo',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('agentes')->insert([
            'id' => 1, 'user_id' => 1, 'nome' => 'Administrador do Sistema',
            'nip' => 'ADMIN-001', 'patente_id' => 1, 'cargo' => 'Administrador',
            'unidade_id' => 1, 'estado' => 'activo',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ═══════════════════════════════════════
        // TURNOS
        // ═══════════════════════════════════════
        DB::table('turnos')->insert([
            ['nome' => 'Manhã', 'hora_inicio' => '06:00', 'hora_fim' => '14:00', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Tarde', 'hora_inicio' => '14:00', 'hora_fim' => '22:00', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Noite', 'hora_inicio' => '22:00', 'hora_fim' => '06:00', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // CATEGORIAS DE CRIME
        // ═══════════════════════════════════════
        DB::table('categorias_crime')->insert([
            ['id' => 1, 'nome' => 'Crimes contra pessoas', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Crimes contra o património', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Crimes contra a ordem pública', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Crimes de trânsito', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Crimes de drogas', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // TIPOS DE CRIME
        // ═══════════════════════════════════════
        DB::table('tipos_crime')->insert([
            ['nome' => 'Homicídio', 'codigo' => 'HOM', 'categoria_id' => 1, 'gravidade' => 'critica', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Tentativa de Homicídio', 'codigo' => 'THOM', 'categoria_id' => 1, 'gravidade' => 'critica', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Agressão Física', 'codigo' => 'AGR', 'categoria_id' => 1, 'gravidade' => 'media', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Violência Doméstica', 'codigo' => 'VD', 'categoria_id' => 1, 'gravidade' => 'alta', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Sequestro', 'codigo' => 'SEQ', 'categoria_id' => 1, 'gravidade' => 'critica', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Violação', 'codigo' => 'VIO', 'categoria_id' => 1, 'gravidade' => 'critica', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Roubo', 'codigo' => 'ROB', 'categoria_id' => 2, 'gravidade' => 'alta', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Furto', 'codigo' => 'FUR', 'categoria_id' => 2, 'gravidade' => 'media', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Assalto à mão armada', 'codigo' => 'AMA', 'categoria_id' => 2, 'gravidade' => 'critica', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Roubo de Viatura', 'codigo' => 'RV', 'categoria_id' => 2, 'gravidade' => 'alta', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Vandalismo', 'codigo' => 'VAN', 'categoria_id' => 2, 'gravidade' => 'baixa', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Burla', 'codigo' => 'BUR', 'categoria_id' => 2, 'gravidade' => 'media', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Perturbação da Ordem', 'codigo' => 'PO', 'categoria_id' => 3, 'gravidade' => 'baixa', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Desacato à Autoridade', 'codigo' => 'DA', 'categoria_id' => 3, 'gravidade' => 'media', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Posse Ilegal de Arma', 'codigo' => 'PIA', 'categoria_id' => 3, 'gravidade' => 'alta', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Acidente de Viação', 'codigo' => 'AV', 'categoria_id' => 4, 'gravidade' => 'media', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Atropelamento', 'codigo' => 'ATR', 'categoria_id' => 4, 'gravidade' => 'alta', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Tráfico de Drogas', 'codigo' => 'TD', 'categoria_id' => 5, 'gravidade' => 'critica', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Consumo de Drogas', 'codigo' => 'CD', 'categoria_id' => 5, 'gravidade' => 'media', 'descricao' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // ESTADOS DE OCORRÊNCIA
        // ═══════════════════════════════════════
        DB::table('estados_ocorrencia')->insert([
            ['id' => 1, 'nome' => 'Registada', 'cor' => '#6c757d', 'ordem' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Em Triagem', 'cor' => '#ffc107', 'ordem' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Despachada', 'cor' => '#17a2b8', 'ordem' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Em Investigação', 'cor' => '#0078d4', 'ordem' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Resolvida', 'cor' => '#28a745', 'ordem' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nome' => 'Encaminhada ao Tribunal', 'cor' => '#6f42c1', 'ordem' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nome' => 'Arquivada', 'cor' => '#343a40', 'ordem' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // TIPOS DE ENVOLVIMENTO
        // ═══════════════════════════════════════
        DB::table('tipos_envolvimento')->insert([
            ['id' => 1, 'nome' => 'Suspeito', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Vítima', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Testemunha', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // ESTADOS DE DETENÇÃO
        // ═══════════════════════════════════════
        DB::table('estados_detencao')->insert([
            ['id' => 1, 'nome' => 'Detido', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Em Custódia', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Apresentado ao Tribunal', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Libertado', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Transferido', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // TIPOS DE EVIDÊNCIA
        // ═══════════════════════════════════════
        DB::table('tipos_evidencia')->insert([
            ['id' => 1, 'nome' => 'Fotografia', 'icone' => 'bxs-image', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Vídeo', 'icone' => 'bxs-video', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Documento', 'icone' => 'bxs-file-pdf', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Áudio', 'icone' => 'bxs-microphone', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Objecto Físico', 'icone' => 'bxs-box', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // ESTADOS DE INVESTIGAÇÃO
        // ═══════════════════════════════════════
        DB::table('estados_investigacao')->insert([
            ['id' => 1, 'nome' => 'Aberta', 'cor' => '#17a2b8', 'ordem' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Em Curso', 'cor' => '#0078d4', 'ordem' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Suspensa', 'cor' => '#ffc107', 'ordem' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Concluída', 'cor' => '#28a745', 'ordem' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Arquivada', 'cor' => '#343a40', 'ordem' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // TIPOS DE MANDADO
        // ═══════════════════════════════════════
        DB::table('tipos_mandado')->insert([
            ['id' => 1, 'nome' => 'Mandado de Captura', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Mandado de Busca', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Mandado de Apreensão', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // TIPOS DE ARMAMENTO
        // ═══════════════════════════════════════
        DB::table('tipos_armamento')->insert([
            ['id' => 1, 'nome' => 'Pistola', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Espingarda', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Metralhadora', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Revólver', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // TIPOS DE ALERTA
        // ═══════════════════════════════════════
        DB::table('tipos_alerta')->insert([
            ['id' => 1, 'nome' => 'Suspeito Procurado', 'icone' => 'bxs-user-x', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Viatura Roubada', 'icone' => 'bxs-car', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Pessoa Desaparecida', 'icone' => 'bxs-user-detail', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Alerta Geral', 'icone' => 'bxs-bell-ring', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // TIPOS DE RELATÓRIO
        // ═══════════════════════════════════════
        DB::table('tipos_relatorio')->insert([
            ['id' => 1, 'nome' => 'Relatório Mensal de Criminalidade', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Relatório de Detenções', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Relatório de Patrulhas', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Relatório de Desempenho', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nome' => 'Relatório Estatístico', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // BAIRROS / DISTRITOS URBANOS DE VIANA (Divisão administrativa 2025)
        // Distritos: Viana Sede, Zango, Estalagem, Kikuxi, Vila Flor, Baía
        // Comuna: Calumbo
        // ═══════════════════════════════════════
        DB::table('bairros')->insert([
            ['nome' => 'Viana Sede', 'municipio' => 'Viana', 'unidade_responsavel_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Bairro da Paz', 'municipio' => 'Viana', 'unidade_responsavel_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Bairro Popular', 'municipio' => 'Viana', 'unidade_responsavel_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zango 0', 'municipio' => 'Viana', 'unidade_responsavel_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zango 1', 'municipio' => 'Viana', 'unidade_responsavel_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zango 2', 'municipio' => 'Viana', 'unidade_responsavel_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zango 3', 'municipio' => 'Viana', 'unidade_responsavel_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zango 4', 'municipio' => 'Viana', 'unidade_responsavel_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zango 5', 'municipio' => 'Viana', 'unidade_responsavel_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Kikuxi', 'municipio' => 'Viana', 'unidade_responsavel_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Estalagem', 'municipio' => 'Viana', 'unidade_responsavel_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Vila Flor', 'municipio' => 'Viana', 'unidade_responsavel_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Baía', 'municipio' => 'Viana', 'unidade_responsavel_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Calumbo', 'municipio' => 'Viana', 'unidade_responsavel_id' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // ZONAS DE PATRULHA (baseadas nos distritos urbanos 2025)
        // ═══════════════════════════════════════
        DB::table('zonas_patrulha')->insert([
            ['nome' => 'Zona Viana Sede', 'descricao' => 'Área central e comercial de Viana Sede', 'unidade_id' => 2, 'nivel_risco' => 'medio', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zona Zango Norte', 'descricao' => 'Zango 0, Zango 1, Zango 2', 'unidade_id' => 3, 'nivel_risco' => 'alto', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zona Zango Sul', 'descricao' => 'Zango 3, Zango 4, Zango 5', 'unidade_id' => 3, 'nivel_risco' => 'alto', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zona Kikuxi', 'descricao' => 'Distrito urbano de Kikuxi', 'unidade_id' => 4, 'nivel_risco' => 'medio', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zona Vila Flor', 'descricao' => 'Distrito urbano de Vila Flor e zona industrial', 'unidade_id' => 5, 'nivel_risco' => 'medio', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zona Estalagem', 'descricao' => 'Distrito urbano da Estalagem', 'unidade_id' => 2, 'nivel_risco' => 'medio', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Zona Baía', 'descricao' => 'Distrito urbano da Baía', 'unidade_id' => 5, 'nivel_risco' => 'baixo', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ═══════════════════════════════════════
        // CONFIGURAÇÕES
        // ═══════════════════════════════════════
        DB::table('configuracoes')->insert([
            ['chave' => 'nome_sistema', 'valor' => 'Sistema de Comunicação e Gerenciamento de Dados', 'tipo' => 'string', 'grupo' => 'geral', 'descricao' => 'Nome do sistema', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'entidade', 'valor' => 'Comando Municipal de Viana', 'tipo' => 'string', 'grupo' => 'geral', 'descricao' => 'Entidade proprietária', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'prefixo_ocorrencia', 'valor' => 'OC-VNA', 'tipo' => 'string', 'grupo' => 'ocorrencias', 'descricao' => 'Prefixo do número de ocorrência', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'prefixo_detencao', 'valor' => 'DT-VNA', 'tipo' => 'string', 'grupo' => 'detencoes', 'descricao' => 'Prefixo do número de detenção', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'prefixo_investigacao', 'valor' => 'INV-VNA', 'tipo' => 'string', 'grupo' => 'investigacoes', 'descricao' => 'Prefixo do número de investigação', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'sessao_timeout', 'valor' => '30', 'tipo' => 'integer', 'grupo' => 'seguranca', 'descricao' => 'Timeout da sessão em minutos', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'backup_automatico', 'valor' => 'true', 'tipo' => 'boolean', 'grupo' => 'seguranca', 'descricao' => 'Backup automático activo', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Chamar TestDataSeeder
        $this->call(TestDataSeeder::class);
    }
}