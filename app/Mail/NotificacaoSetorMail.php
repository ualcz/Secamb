<?php

namespace App\Mail;

use App\Models\Requerimento;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notificação simples ao setor avisando que um novo requerimento foi protocolado.
 * Sem anexos, sem cópia do requerimento.
 */
class NotificacaoSetorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario       $cidadao,
        public Setor         $setor,
        public string        $objeto,
        public Requerimento  $requerimento,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Novo Requerimento [{$this->requerimento->numero_protocolo}] - SECAMB Seabra",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacao_setor',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
