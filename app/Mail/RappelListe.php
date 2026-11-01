<?php

namespace App\Mail;

use App\Models\Invite;
use App\Models\Liste;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelListe extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Liste $liste,
        public Invite $invite,
        public int $libres,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Plus que sept jours : '.$this->liste->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rappel',
            with: ['lien' => route('invite', $this->liste->share_token)],
        );
    }
}
