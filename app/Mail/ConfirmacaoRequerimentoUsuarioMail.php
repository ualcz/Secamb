<?php

namespace App\Mail;

use App\Models\Requerimento;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notificação simples ao cidadão confirmando o protocolo do requerimento.
 * Sem anexos.
 */
class ConfirmacaoRequerimentoUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario       $aluno,
        public string        $setorNome,
        public ?string       $objeto       = null,
        public ?Requerimento $requerimento = null,
    ) {}

    public function envelope(): Envelope
    {
        $protocolo = $this->requerimento?->numero_protocolo ?? 'Novo';
        return new Envelope(
            subject: "Requerimento Protocolado [{$protocolo}] - SECAMB Seabra",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmacao_usuario',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
