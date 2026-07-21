<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialTemporalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public string $passwordTemporal,
        public int $horasVigencia,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Credenciales temporales - Sistema de Capacitaciones STB');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.usuarios.credencial_temporal');
    }

    public function attachments(): array
    {
        return [];
    }
}
