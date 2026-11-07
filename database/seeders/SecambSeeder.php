<?php

namespace Database\Seeders;

use App\Models\AssuntoRequerimento;
use App\Models\DocumentoAssunto;
use App\Models\Setor;
use App\Models\TipoProcesso;
use Illuminate\Database\Seeder;

/**
 * Seeder inicial para a SECAMB - Prefeitura Municipal de Seabra
 * Popula secretarias/setores, tipos de licenças e documentos exigidos.
 */
class SecambSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Tipos de Licenças (Catálogo de Referência) ─────────────────────
        $tipos = [
            ['sigla' => 'LP',   'nome' => 'Licença Prévia',                             'descricao' => 'Concedida na fase preliminar do planejamento do empreendimento.'],
            ['sigla' => 'LI',   'nome' => 'Licença de Instalação',                        'descricao' => 'Autoriza a instalação do empreendimento conforme projetos aprovados.'],
            ['sigla' => 'LO',   'nome' => 'Licença de Operação',                          'descricao' => 'Autoriza o início das atividades e funcionamento.'],
            ['sigla' => 'LU',   'nome' => 'Licença Unificada',                           'descricao' => 'Unifica duas ou mais etapas de licenciamento (LP, LI e/ou LO).'],
            ['sigla' => 'RLO',  'nome' => 'Renovação de Licença de Operação',             'descricao' => 'Renovação periódica da licença de operação vigente.'],
            ['sigla' => 'LA',   'nome' => 'Licença de Alteração',                         'descricao' => 'Modificação ou ampliação de empreendimento já licenciado.'],
            ['sigla' => 'LRA',  'nome' => 'Licença de Regularização Ambiental',            'descricao' => 'Para regularização de empreendimentos já em funcionamento.'],
            ['sigla' => 'AA',   'nome' => 'Autorização Ambiental',                        'descricao' => 'Autorização para atividades de curta duração ou caráter transitório.'],
            ['sigla' => 'RC',   'nome' => 'Revisão de Condicionantes',                    'descricao' => 'Reavaliação e adequação de condicionantes fixadas em licenças.'],
            ['sigla' => 'ALRS', 'nome' => 'Alteração de Razão Social',                    'descricao' => 'Atualização cadastral do titular do empreendimento.'],
            ['sigla' => 'TLA',  'nome' => 'Transferência de Licença Ambiental',           'descricao' => 'Transferência de titularidade de licença ambiental vigente.'],
            ['sigla' => 'PPV',  'nome' => 'Prorrogação de Prazo de Validade',             'descricao' => 'Extensão do prazo de validade de licença ou autorização.'],
            ['sigla' => 'DLA',  'nome' => 'Dispensa de Licenciamento',                   'descricao' => 'Declaração de dispensa para atividades de impacto insignificante.'],
        ];

        foreach ($tipos as $tipo) {
            TipoProcesso::updateOrCreate(['sigla' => $tipo['sigla']], [
                'nome'            => $tipo['nome'],
                'descricao'       => $tipo['descricao'],
                'legislacao_base' => 'Lei Municipal de Meio Ambiente de Seabra',
                'ativo'           => true,
            ]);
        }

        // ─── 2. Setor Principal - SECAMB ──────────────────────────────────────
        $secamb = Setor::updateOrCreate(
            ['setor_sigla' => 'SECAMB'],
            [
                'setor_nome' => 'Secretaria Municipal de Meio Ambiente',
                'email'      => 'meioambiente@seabra.ba.gov.br',
                'titulo'     => 'Requerimento de Licenciamento Ambiental',
                'ativo'      => true,
                'is_interno' => false,
            ]
        );

        $fiscal = Setor::updateOrCreate(
            ['setor_sigla' => 'FISCALIZACAO'],
            [
                'setor_nome' => 'Setor de Fiscalização e Monitoramento Ambiental',
                'email'      => 'fiscalizacao.secamb@seabra.ba.gov.br',
                'titulo'     => 'Vistorias e Pareceres Técnicos',
                'ativo'      => true,
                'is_interno' => true,
            ]
        );

        // ─── 3. Assuntos / Modalidades de Requerimento da SECAMB ──────────────
        $assuntosSecamb = [
            [
                'descricao'  => 'Licença Prévia (LP)',
                'observacao' => 'Exige estudo de viabilidade locacional e certidão de uso do solo emitida pelo município.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 1,
                'documentos' => [
                    ['nome' => 'Requerimento Padrão Assinado', 'obrigatorio' => true],
                    ['nome' => 'Certidão da Prefeitura de Uso e Ocupação do Solo', 'obrigatorio' => true],
                    ['nome' => 'Comprovante de Titularidade ou Posse do Imóvel', 'obrigatorio' => true],
                    ['nome' => 'Estudo / Diagnóstico Ambiental com ART/RRT', 'obrigatorio' => false],
                ],
            ],
            [
                'descricao'  => 'Licença de Instalação (LI)',
                'observacao' => 'Apresentar comprovação do cumprimento de condicionantes da Licença Prévia.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 2,
                'documentos' => [
                    ['nome' => 'Projeto Executivo das Obras com ART/RRT', 'obrigatorio' => true],
                    ['nome' => 'Plano de Controle Ambiental (PCA)', 'obrigatorio' => true],
                    ['nome' => 'Cópia da Licença Prévia e Condicionantes', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Licença de Operação (LO)',
                'observacao' => 'Apresentar comprovação de que as medidas de controle e sistemas de tratamento foram instalados.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 3,
                'documentos' => [
                    ['nome' => 'Relatório de Cumprimento das Condicionantes da LI', 'obrigatorio' => true],
                    ['nome' => 'Plano de Gerenciamento de Resíduos Sólidos (PGRS)', 'obrigatorio' => true],
                    ['nome' => 'Alvará do Corpo de Bombeiros / AVCB', 'obrigatorio' => false],
                ],
            ],
            [
                'descricao'  => 'Licença de Regularização Ambiental (LRA)',
                'observacao' => 'Para atividades já em funcionamento sem licença ambiental prévia.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 4,
                'documentos' => [
                    ['nome' => 'Relatório Ambiental de Conformidade (RAC)', 'obrigatorio' => true],
                    ['nome' => 'Comprovante de Inscrição e Situação Cadastral (CNPJ/CPF)', 'obrigatorio' => true],
                    ['nome' => 'Comprovante de Endereço do Empreendimento', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Autorização Ambiental / Supressão Vegetal',
                'observacao' => 'Necessária para intervenções pontuais, podas ou supressão em área urbana e rural.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 5,
                'documentos' => [
                    ['nome' => 'Inventário Florestal / Laudo de Vistoria', 'obrigatorio' => true],
                    ['nome' => 'Croqui de Acesso e Localização com Coordenadas UTM', 'obrigatorio' => true],
                    ['nome' => 'Comprovante de Propriedade do Imóvel', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Dispensa de Licenciamento Ambiental (DLA)',
                'observacao' => 'Para atividades com impacto local insignificante ou não passíveis de licenciamento formal.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 6,
                'documentos' => [
                    ['nome' => 'Formulário de Caracterização do Empreendimento', 'obrigatorio' => true],
                    ['nome' => 'Documento de Identificação (RG/CPF ou CNPJ)', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Licença Unificada (LU) - Mineração',
                'observacao' => 'Tipologia B4.1 - Extração de Minérios (Argilas, Caulinite, Diatomita, etc.). Conforme Resolução CONAMA 237/97, prazo de análise de 6 meses (ou 12 meses havendo EIA/RIMA). Apresentar estudos técnicos acompanhados de ART.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 7,
                'documentos' => [
                    ['nome' => '1. Requerimento Ambiental (modelo da SEMMA)', 'obrigatorio' => true],
                    ['nome' => '2. Análise Prévia (modelo da SEMMA)', 'obrigatorio' => true],
                    ['nome' => '3. Comprovante de Pagamento do DAM (Taxa Ambiental)', 'obrigatorio' => true],
                    ['nome' => '4. Comprovante de Propriedade, Justa Posse ou Contrato de Arrendamento', 'obrigatorio' => true],
                    ['nome' => '5. Contrato Social Autenticado', 'obrigatorio' => true],
                    ['nome' => '6. Fotocópias Autenticadas do RG e CPF dos Sócios', 'obrigatorio' => true],
                    ['nome' => '7. Comprovante de Endereço do(s) Proprietário(s)', 'obrigatorio' => true],
                    ['nome' => '8. Comprovante de Inscrição e Situação Cadastral no CNPJ', 'obrigatorio' => true],
                    ['nome' => '9. Inscrição Estadual', 'obrigatorio' => true],
                    ['nome' => '10. Inscrição Municipal', 'obrigatorio' => true],
                    ['nome' => '11. Certidão Negativa de Débitos da Fazenda Municipal', 'obrigatorio' => true],
                    ['nome' => '12. Estudo de Impacto de Vizinhança e Relatório (EIV/RIV) com ART', 'obrigatorio' => true],
                    ['nome' => '13. Autorização de Supressão de Vegetação (ASV), se couber', 'obrigatorio' => false],
                    ['nome' => '14. Programa de Recuperação de Áreas Degradadas (PRAD)', 'obrigatorio' => true],
                    ['nome' => '15. Outorga ou Dispensa de Outorga de Recursos Hídricos, se couber', 'obrigatorio' => false],
                    ['nome' => '16. Cadastro Técnico Federal (CTF / IBAMA)', 'obrigatorio' => true],
                    ['nome' => '17. Comprovante de Registro no CEAPD / INEMA (Lei 9.832/2005)', 'obrigatorio' => true],
                    ['nome' => '18. Cadastro Estadual Florestal de Imóveis Rurais (CEFIR / Rural)', 'obrigatorio' => false],
                    ['nome' => '19. Plano de Gerenciamento de Resíduos Sólidos (PGRS) com ART', 'obrigatorio' => true],
                    ['nome' => '20. Relatório de Caracterização do Empreendimento (RCE) com ART', 'obrigatorio' => true],
                    ['nome' => '21. Plano de Segurança e Trânsito Simplificado, se couber', 'obrigatorio' => false],
                    ['nome' => '22. Programa de Gerenciamento de Riscos (PGR) com ART', 'obrigatorio' => true],
                    ['nome' => '23. Plano/Relatório de Controle Ambiental (PCA/RCA)', 'obrigatorio' => true],
                    ['nome' => '24. Autorização / Requerimento junto à Agência Nacional de Mineração (ANM)', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Renovação de Licença de Operação (RLO)',
                'observacao' => 'Deve ser requerida com antecedência mínima de 120 dias da expiração do prazo de validade.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 8,
                'documentos' => [
                    ['nome' => 'Cópia da Licença de Operação Anterior', 'obrigatorio' => true],
                    ['nome' => 'Relatório Técnico de Cumprimento de Condicionantes com ART', 'obrigatorio' => true],
                    ['nome' => 'Comprovante de Pagamento do DAM', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Licença de Alteração (LA)',
                'observacao' => 'Para modificação de processos, ampliação de área ou alteração de capacidade produtiva.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 9,
                'documentos' => [
                    ['nome' => 'Memorial Descritivo das Alterações Propostas com ART', 'obrigatorio' => true],
                    ['nome' => 'Cópia da Licença Ambiental Vigente', 'obrigatorio' => true],
                    ['nome' => 'Comprovante de Pagamento do DAM', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Revisão de Condicionantes (RC)',
                'observacao' => 'Solicitação formal fundamentada para revisão, exclusão ou prorrogação de prazo de condicionante.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 10,
                'documentos' => [
                    ['nome' => 'Justificativa Técnica Fundamentada com ART', 'obrigatorio' => true],
                    ['nome' => 'Cópia da Licença e Folha de Condicionantes', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Transferência de Licença Ambiental (TLA)',
                'observacao' => 'Transferência de titularidade mantendo-se inalteradas as atividades e condições do licenciamento.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 11,
                'documentos' => [
                    ['nome' => 'Instrumento Jurídico de Transferência / Cessão', 'obrigatorio' => true],
                    ['nome' => 'Documentos de Identificação e CNPJ do Novo Titular', 'obrigatorio' => true],
                    ['nome' => 'Cópia da Licença Ambiental Vigente', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Prorrogação de Prazo de Validade (PPV)',
                'observacao' => 'Solicitação de prorrogação de prazo para início ou conclusão de obras/instalações.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 12,
                'documentos' => [
                    ['nome' => 'Justificativa Técnica e Cronograma Atualizado com ART', 'obrigatorio' => true],
                    ['nome' => 'Cópia da Licença Vigente', 'obrigatorio' => true],
                ],
            ],
        ];

        foreach ($assuntosSecamb as $item) {
            $assunto = AssuntoRequerimento::updateOrCreate(
                [
                    'setor_id'  => $secamb->id,
                    'descricao' => $item['descricao'],
                ],
                [
                    'observacao' => $item['observacao'],
                    'link_norma' => $item['link_norma'],
                    'ordem'      => $item['ordem'],
                    'ativo'      => true,
                ]
            );

            foreach ($item['documentos'] as $doc) {
                DocumentoAssunto::updateOrCreate(
                    [
                        'assunto_requerimento_id' => $assunto->id,
                        'nome'                    => $doc['nome'],
                    ],
                    [
                        'obrigatorio'   => $doc['obrigatorio'],
                        'tipos_aceitos' => 'pdf,png,jpg,jpeg',
                    ]
                );
            }
        }

        // ─── 4. Assuntos / Demandas Internas da Fiscalização ──────────────────
        $assuntosFiscalizacao = [
            [
                'descricao'  => 'Vistoria Técnica de Campo / In Loco',
                'observacao' => 'Realização de inspeção técnica presencial para verificação de impacto, limites do imóvel e conformidade ambiental.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 1,
                'documentos' => [
                    ['nome' => 'Relatório / Laudo Fotográfico de Vistoria', 'obrigatorio' => true],
                    ['nome' => 'Croqui / Coordenadas de Vistoria', 'obrigatorio' => false],
                ],
            ],
            [
                'descricao'  => 'Parecer Técnico de Fiscalização',
                'observacao' => 'Emissão de parecer técnico fundamentado para subsidiar a decisão da equipe de licenciamento.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 2,
                'documentos' => [
                    ['nome' => 'Parecer Técnico de Fiscalização Assinado', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Verificação de Cumprimento de Condicionantes',
                'observacao' => 'Inspeção e checagem de cumprimento das medidas mitigadoras e condicionantes de licenças anteriores.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 3,
                'documentos' => [
                    ['nome' => 'Checklist de Verificação de Condicionantes', 'obrigatorio' => true],
                ],
            ],
            [
                'descricao'  => 'Auto de Constatação / Notificação Ambiental',
                'observacao' => 'Registro de não conformidades ou necessidade de adequação técnica pelo empreendedor.',
                'link_norma' => 'https://seabra.ba.gov.br/legislacao-ambiental',
                'ordem'      => 4,
                'documentos' => [
                    ['nome' => 'Auto de Constatação / Notificação', 'obrigatorio' => true],
                ],
            ],
        ];

        foreach ($assuntosFiscalizacao as $item) {
            $assunto = AssuntoRequerimento::updateOrCreate(
                [
                    'setor_id'  => $fiscal->id,
                    'descricao' => $item['descricao'],
                ],
                [
                    'observacao' => $item['observacao'],
                    'link_norma' => $item['link_norma'],
                    'ordem'      => $item['ordem'],
                    'ativo'      => true,
                ]
            );

            foreach ($item['documentos'] as $doc) {
                DocumentoAssunto::updateOrCreate(
                    [
                        'assunto_requerimento_id' => $assunto->id,
                        'nome'                    => $doc['nome'],
                    ],
                    [
                        'obrigatorio'   => $doc['obrigatorio'],
                        'tipos_aceitos' => 'pdf,png,jpg,jpeg',
                    ]
                );
            }
        }
    }
}
