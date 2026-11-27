<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class CaracterizacaoEmpreendimento
{
    private const CAMPOS_PENDENCIA = [
        'notificacao_pendencia',
        'embargo_pendencia',
        'advertencia_pendencia',
        'apreensao_pendencia',
        'multa_pendencia',
        'interdicao_pendencia',
    ];

    public static function rules(): array
    {
        return [
            'bacia_hidrografica' => 'nullable|string|max:255',
            'recurso_hidrico' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'unidade_conservacao_resposta' => 'required|in:sim,nao',
            'unidade_conservacao_nome' => 'nullable|string|max:255|required_if:unidade_conservacao_resposta,sim',
            'licenca_anterior' => 'required|in:sim,nao',
            'localizacao_licenca' => 'nullable|in:municipio,estado|required_if:licenca_anterior,sim',
            'licenca_anterior_tipo' => 'nullable|string|max:255|required_if:localizacao_licenca,municipio',
            'licenca_anterior_processo' => 'nullable|string|max:255|required_if:localizacao_licenca,municipio',
            'resolucao_cepram' => 'nullable|string|max:255|required_if:localizacao_licenca,estado',
            'resolucao_cepram_validade' => 'nullable|date|required_if:localizacao_licenca,estado',
            'portaria_inema' => 'nullable|string|max:255|required_if:localizacao_licenca,estado',
            'portaria_inema_validade' => 'nullable|date|required_if:localizacao_licenca,estado',
            'processo_semma' => 'required|in:sim,nao',
            'processo_semma_numero' => 'nullable|string|max:25|required_if:processo_semma,sim',
            'processo_semma_tipo' => 'nullable|string|max:255|required_if:processo_semma,sim',
            'pendencia_ambiental' => 'required|in:sim,nao',
            'notificacao_pendencia' => 'nullable|string|max:50',
            'embargo_pendencia' => 'nullable|string|max:50',
            'advertencia_pendencia' => 'nullable|string|max:50',
            'apreensao_pendencia' => 'nullable|string|max:50',
            'multa_pendencia' => 'nullable|string|max:50',
            'interdicao_pendencia' => 'nullable|string|max:50',
        ];
    }

    public static function mensagens(): array
    {
        return [
            'unidade_conservacao_resposta.required' => 'Informe se o empreendimento está situado em Unidade de Conservação.',
            'licenca_anterior.required' => 'Informe se o empreendimento possui licença ou autorização anterior.',
            'processo_semma.required' => 'Informe se existe outro processo em tramitação na SEMMA.',
            'pendencia_ambiental.required' => 'Informe se há pendências ambientais.',
        ];
    }

    public static function persistir(array $dadosValidados): array
    {
        if (
            ($dadosValidados['pendencia_ambiental'] ?? null) === 'sim'
            && collect(self::CAMPOS_PENDENCIA)->every(fn ($campo) => blank($dadosValidados[$campo] ?? null))
        ) {
            throw ValidationException::withMessages([
                'notificacao_pendencia' => 'Informe ao menos uma pendência ambiental.',
            ]);
        }

        $campos = array_keys(self::rules());
        $caracterizacao = array_replace(
            array_fill_keys($campos, null),
            array_intersect_key($dadosValidados, array_flip($campos))
        );

        if ($caracterizacao['unidade_conservacao_resposta'] === 'nao') {
            $caracterizacao['unidade_conservacao_nome'] = null;
        }

        if ($caracterizacao['licenca_anterior'] === 'nao') {
            foreach ([
                'localizacao_licenca',
                'licenca_anterior_tipo',
                'licenca_anterior_processo',
                'resolucao_cepram',
                'resolucao_cepram_validade',
                'portaria_inema',
                'portaria_inema_validade',
            ] as $campo) {
                $caracterizacao[$campo] = null;
            }
        } elseif ($caracterizacao['localizacao_licenca'] === 'municipio') {
            foreach (['resolucao_cepram', 'resolucao_cepram_validade', 'portaria_inema', 'portaria_inema_validade'] as $campo) {
                $caracterizacao[$campo] = null;
            }
        } else {
            $caracterizacao['licenca_anterior_tipo'] = null;
            $caracterizacao['licenca_anterior_processo'] = null;
        }

        if ($caracterizacao['processo_semma'] === 'nao') {
            $caracterizacao['processo_semma_numero'] = null;
            $caracterizacao['processo_semma_tipo'] = null;
        }

        if ($caracterizacao['pendencia_ambiental'] === 'nao') {
            foreach (self::CAMPOS_PENDENCIA as $campo) {
                $caracterizacao[$campo] = null;
            }
        }

        return $caracterizacao;
    }
}
