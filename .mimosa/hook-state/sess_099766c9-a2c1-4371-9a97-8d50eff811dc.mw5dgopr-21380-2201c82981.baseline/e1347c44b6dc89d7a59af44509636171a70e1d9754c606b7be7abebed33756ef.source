<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Shared implementation for simple transactional emails: title, body lines,
 * optional CTA. Every billing email type uses this via a thin subclass so
 * Mail::fake assertions can target a concrete class per template.
 */
abstract class TransactionalMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $lines  HTML-allowed body lines
     */
    public function __construct(
        public readonly string $subjectLine,
        public readonly string $title,
        public readonly array $lines,
        public readonly ?string $actionUrl = null,
        public readonly ?string $actionLabel = null,
        public readonly ?string $footer = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.transactional', with: [
            'subject' => $this->subjectLine,
            'title' => $this->title,
            'lines' => $this->lines,
            'actionUrl' => $this->actionUrl,
            'actionLabel' => $this->actionLabel,
            'footer' => $this->footer,
        ]);
    }
}
