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
            ['sigla' => 'LP',  'nome' => 'Licença Prévia',                  'descricao' => 'Concedida na fase preliminar do planejamento do empreendimento.'],
            ['sigla' => 'LI',  'nome' => 'Licença de Instalação',             'descricao' => 'Autoriza a instalação do empreendimento conforme projetos aprovados.'],
            ['sigla' => 'LO',  'nome' => 'Licença de Operação',               'descricao' => 'Autoriza o início das atividades e funcionamento.'],
            ['sigla' => 'LU',  'nome' => 'Licença Unificada',                'descricao' => 'Unifica duas ou mais etapas de licenciamento.'],
            ['sigla' => 'LRA', 'nome' => 'Licença de Regularização Ambiental', 'descricao' => 'Para regularização de empreendimentos já em funcionamento.'],
            ['sigla' => 'AA',  'nome' => 'Autorização Ambiental',             'descricao' => 'Autorização para atividades de curta duração ou caráter transitório.'],
            ['sigla' => 'DLA', 'nome' => 'Dispensa de Licenciamento',        'descricao' => 'Declaração de dispensa para atividades de impacto insignificante.'],
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
