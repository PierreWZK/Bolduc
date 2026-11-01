<?php

namespace App\Livewire;

use App\Models\Cadeau;
use App\Models\Invite;
use App\Models\Liste;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** La liste vue par un invité : réserver, participer, annuler, sans rechargement. */
class ListeInvite extends Component
{
    #[Locked]
    public int $listeId;

    #[Locked]
    public int $inviteId;

    /** Montant saisi pour chaque cadeau à plusieurs, indexé par id de cadeau. */
    public array $parts = [];

    public ?string $message = null;

    public function mount(Liste $liste, Invite $invite): void
    {
        $this->listeId = $liste->id;
        $this->inviteId = $invite->id;
    }

    public function reserver(int $cadeauId): void
    {
        if ($this->fermee()) {
            return;
        }

        // Verrou sur le cadeau : deux invités qui cliquent en même temps ne réservent pas la même unité.
        DB::transaction(function () use ($cadeauId) {
            $cadeau = $this->cadeau($cadeauId, verrou: true);

            if ($cadeau->shared) {
                return;
            }

            if ($cadeau->reservations()->sum('quantity') >= $cadeau->quantity) {
                $this->message = 'Trop tard, quelqu\'un vient de le réserver.';

                return;
            }

            $reservation = $cadeau->reservations()->firstOrNew(['guest_id' => $this->inviteId]);
            $reservation->quantity = ($reservation->quantity ?? 0) + 1;
            $reservation->save();

            $this->message = 'C\'est noté : tu t\'en occupes.';
        });
    }

    public function participer(int $cadeauId): void
    {
        if ($this->fermee()) {
            return;
        }

        $this->resetErrorBag("parts.$cadeauId");
        $montant = round((float) str_replace(',', '.', (string) ($this->parts[$cadeauId] ?? '')), 2);

        DB::transaction(function () use ($cadeauId, $montant) {
            $cadeau = $this->cadeau($cadeauId, verrou: true);

            if (! $cadeau->shared || $cadeau->price === null) {
                return;
            }

            $autres = (float) $cadeau->contributions()->where('guest_id', '!=', $this->inviteId)->sum('amount');
            $reste = round((float) $cadeau->price - $autres, 2);

            if ($reste < 1) {
                $this->addError("parts.$cadeauId", 'Ce cadeau est déjà entièrement financé.');

                return;
            }

            if ($montant < 1 || $montant > $reste) {
                $this->addError("parts.$cadeauId", 'Entre 1 € et '.number_format($reste, 2, ',', ' ').' €.');

                return;
            }

            $cadeau->contributions()->updateOrCreate(['guest_id' => $this->inviteId], ['amount' => $montant]);
            unset($this->parts[$cadeauId]);
            $this->message = 'Merci, ta part est notée.';
        });
    }

    public function annuler(int $cadeauId): void
    {
        if ($this->fermee()) {
            return;
        }

        $cadeau = $this->cadeau($cadeauId);
        $cadeau->reservations()->where('guest_id', $this->inviteId)->delete();
        $cadeau->contributions()->where('guest_id', $this->inviteId)->delete();

        $this->message = 'C\'est annulé : le cadeau est de nouveau libre.';
    }

    private function cadeau(int $id, bool $verrou = false): Cadeau
    {
        $requete = Cadeau::whereKey($id)->where('list_id', $this->listeId);

        return ($verrou ? $requete->lockForUpdate() : $requete)->firstOrFail();
    }

    private function fermee(): bool
    {
        if (! Liste::findOrFail($this->listeId)->estPassee()) {
            return false;
        }

        $this->message = 'La date est passée : la liste est fermée.';

        return true;
    }

    public function render()
    {
        return view('livewire.liste-invite', [
            'liste' => Liste::with(['cadeaux.reservations.invite', 'cadeaux.contributions.invite'])->findOrFail($this->listeId),
        ]);
    }
}
