<?php

namespace App\Mail;

use App\Models\Requerimento;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail enviado ao cidadão ao protocolar um processo de licenciamento.
 * Também usado pela tramitação interna (SECAMB).
 */
class InformacoesAlunoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Usuario         $aluno           Cidadão requerente
     * @param string          $setorNome        Nome da secretaria/setor da prefeitura
     * @param string|null     $mensagem         Mensagem do cidadão ou observação do técnico
     * @param array           $arquivos         Arquivos UploadedFile ou paths a anexar
     * @param string|null     $objeto           Tipo/objeto do processo (ex: Licença de Operação)
     * @param string|null     $setorChave       ID ou sigla do setor (uso interno)
     * @param Requerimento|null $requerimento   Processo vinculado (para thread de e-mail)
     * @param array|null      $pdfRequerimento  ['nome' => ..., 'conteudo' => ...] do PDF gerado
     */
    public function __construct(
        public Usuario      $aluno,
        public string       $setorNome,
        public ?string      $mensagem        = null,
        public array        $arquivos        = [],
        public ?string      $objeto          = null,
        public ?string      $setorChave      = null,
        public ?Requerimento $requerimento   = null,
        public ?array       $pdfRequerimento = null
    ) {}

    public function envelope(): Envelope
    {
        $assunto = $this->requerimento
            ? $this->requerimento->emailThreadSubject()
            : ('Protocolo SECAMB - ' . ($this->objeto ?? 'Licenciamento Ambiental') . ' - ' . $this->aluno->nome);

        // Reply-To aponta para o e-mail do cidadão para que o setor possa responder diretamente
        $replyTo = $this->aluno->email
            ? [new Address($this->aluno->email, $this->aluno->nome)]
            : [];

        return new Envelope(
            subject: $assunto,
            replyTo: $replyTo ?: null,
        );
    }

    public function headers(): Headers
    {
        return new Headers(messageId: $this->requerimento?->email_message_id);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.informacoes_aluno',
        );
    }

    public function attachments(): array
    {
        $anexos = [];

        if ($this->pdfRequerimento !== null) {
            $anexos[] = Attachment::fromData(
                fn () => $this->pdfRequerimento['conteudo'],
                $this->pdfRequerimento['nome']
            )->withMime('application/pdf');
        }

        foreach ($this->arquivos as $arquivo) {
            if ($arquivo instanceof \Illuminate\Http\UploadedFile) {
                $anexos[] = Attachment::fromPath($arquivo->getRealPath())
                    ->as($arquivo->getClientOriginalName())
                    ->withMime($arquivo->getMimeType());
            } elseif (is_string($arquivo) && file_exists($arquivo)) {
                $anexos[] = Attachment::fromPath($arquivo);
            }
        }

        return $anexos;
    }
}
