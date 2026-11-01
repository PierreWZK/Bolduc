<?php

namespace App\Console\Commands;

use App\Mail\RappelListe;
use App\Models\Liste;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnvoyerRappels extends Command
{
    protected $signature = 'bolduc:rappels';

    protected $description = 'Envoie le rappel J-7 aux invités qui ont laissé leur e-mail';

    public function handle(): int
    {
        $envoyes = 0;

        Liste::query()
            ->whereDate('event_date', now()->addDays(7)->toDateString())
            ->whereNull('reminder_sent_at')
            ->with(['cadeaux.reservations', 'cadeaux.contributions', 'invites'])
            ->each(function (Liste $liste) use (&$envoyes) {
                // Un cadeau est libre tant qu'il n'est ni réservé ni entièrement financé.
                $libres = $liste->cadeaux->reject->estComplet()->count();

                $liste->invites->whereNotNull('email')->each(function ($invite) use ($liste, $libres, &$envoyes) {
                    Mail::to($invite->email)->send(new RappelListe($liste, $invite, $libres));
                    $envoyes++;
                });

                $liste->forceFill(['reminder_sent_at' => now()])->save();
            });

        $this->info("$envoyes rappel(s) envoyé(s).");

        return self::SUCCESS;
    }
}
