<?php

namespace App\Services;

use App\Mail\InformacoesAlunoMail;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;

/**
 * Serviço de resolução de destinatários e envio de e-mails de requerimento.
 * SECAMB - Prefeitura Municipal de Seabra.
 */
class RequerimentoEmailService
{
    /**
     * Resolve os destinatários para o e-mail de protocolo do processo.
     *
     * Regra:
     *   - Se o setor tem e-mail: setor recebe no "Para", cidadão em "CC".
     *   - Se só o cidadão tem e-mail: cidadão recebe no "Para".
     *   - Se nenhum tem e-mail: retorna null (e-mail não será enviado).
     *
     * @param Setor       $setor         Secretaria/Setor da Prefeitura
     * @param Usuario     $cidadao       Cidadão requerente
     * @param string|null $emailAdicional E-mail extra fornecido no formulário (opcional)
     * @return array{para: array, cc: array}|null
     */
    public function resolverDestinatarios(Setor $setor, Usuario $cidadao, ?string $emailAdicional = null): ?array
    {
        $emailsSetor = $setor->email;
        $emailsSetor = is_array($emailsSetor) ? $emailsSetor : [$emailsSetor];
        $emailsSetor = array_values(array_unique(array_filter(array_map('trim', $emailsSetor))));

        $emailsCidadao = array_filter([
            $cidadao->email,
            $emailAdicional,
        ]);
        $emailsCidadao = array_values(array_unique(array_filter(array_map('trim', $emailsCidadao))));
        // Remove e-mails do cidadão que já estão no setor (evita duplicidade)
        $emailsCidadao = array_values(array_diff($emailsCidadao, $emailsSetor));

        if (empty($emailsSetor) && empty($emailsCidadao)) {
            return null;
        }

        return [
            'para' => !empty($emailsSetor) ? $emailsSetor : $emailsCidadao,
            'cc'   => !empty($emailsSetor) ? $emailsCidadao : [],
        ];
    }

    /**
     * Envia o e-mail para os destinatários resolvidos.
     */
    public function enviar(InformacoesAlunoMail $mailable, array $destinatarios): void
    {
        $mailer = Mail::to($destinatarios['para']);

        if (!empty($destinatarios['cc'])) {
            $mailer->cc($destinatarios['cc']);
        }

        $mailer->send($mailable);
    }
}