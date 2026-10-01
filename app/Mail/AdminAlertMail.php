<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminAlertMail extends Mailable
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Velto] '.$this->title);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.admin-alert');
    }
}
