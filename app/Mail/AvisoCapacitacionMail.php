<?php

namespace App\Mail;

use App\Models\AvisoCorreo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AvisoCapacitacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AvisoCorreo $aviso
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->aviso->asunto
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.avisos.capacitacion',
            with: [
                'aviso' => $this->aviso,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}