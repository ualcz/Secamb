<?php

namespace App\Mail;

use App\Models\Empreendimento;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TecnicoVinculadoEmpreendimentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $tecnico,
        public Empreendimento $empreendimento,
        public Usuario $vinculador,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Vínculo ao empreendimento {$this->empreendimento->nome} - SECAMB",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tecnico_vinculado_empreendimento',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
