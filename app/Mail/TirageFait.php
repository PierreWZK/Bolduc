<?php

namespace App\Mail;

use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Prévient un participant que le tirage est fait ; le mail ne dit jamais qui est la cible.
class TirageFait extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SantaGroupe $groupe,
        public SantaMembre $membre,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Le tirage est fait : '.$this->groupe->name);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tirage',
            with: [
                'lien' => route('santa.invite', $this->groupe->share_token),
                'indices' => route('santa.indices', $this->groupe->share_token),
            ],
        );
    }
}
