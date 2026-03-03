<?php

namespace App\Mail;

use App\Models\Questionnaire;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuestionnaireInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Questionnaire $questionnaire,
        public string $questionnaireUrl
    ) {}

    public function envelope(): Envelope
    {
        $vendorName = $this->questionnaire->vendor->name;

        return new Envelope(
            subject: "Security Assessment Request — {$vendorName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.questionnaire-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
