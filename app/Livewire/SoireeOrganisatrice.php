<?php

namespace App\Livewire;

use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use App\Models\SoireeIndice;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Mode soirée côté organisatrice : les indices d'une personne tirée au hasard, à deviner. */
class SoireeOrganisatrice extends Component
{
    #[Locked]
    public int $groupeId;

    #[Locked]
    public ?int $membreId = null;

    // 1 : « Qui m'a offert le cadeau ? » (emoji, surnom, phrase). 2 : « Quel cadeau ? » (les trois indices du cadeau).
    public int $jeu = 1;

    public int $revele = 1;

    public bool $trouve = false;

    public ?string $message = null;

    public function mount(SantaGroupe $groupe): void
    {
        $this->groupeId = $groupe->id;
        $this->autre();
    }

    // Une autre personne, tirée au hasard parmi celles qui ont écrit leurs indices.
    public function autre(): void
    {
        $this->membreId = $this->groupe()->membres()->whereHas('indices')->inRandomOrder()->value('id');
        $this->jeu = 1;
        $this->revele = 1;
        $this->trouve = false;
        $this->message = null;
    }

    public function indiceSuivant(): void
    {
        $this->revele = min($this->revele + 1, count($this->kinds()));
    }

    public function deviner(int $candidatId): void
    {
        $membre = $this->membre();

        if (! $membre || $this->trouve) {
            return;
        }

        if ($candidatId === $membre->id) {
            $this->trouve = true;
            $this->message = 'Bravo, c\'est bien '.$membre->name.' !';
        } else {
            $this->message = 'Raté, essaie encore.';
        }
    }

    public function passerAuCadeau(): void
    {
        if (! $this->trouve || $this->jeu !== 1) {
            return;
        }

        $this->jeu = 2;
        $this->revele = 1;
        $this->trouve = false;
        $this->message = null;
    }

    private function kinds(): array
    {
        return $this->jeu === 1 ? SoireeIndice::QUI : SoireeIndice::CADEAU;
    }

    private function groupe(): SantaGroupe
    {
        $groupe = SantaGroupe::findOrFail($this->groupeId);
        abort_unless($groupe->user_id === auth()->id(), 404);

        return $groupe;
    }

    private function membre(): ?SantaMembre
    {
        return $this->membreId
            ? $this->groupe()->membres()->whereKey($this->membreId)->with('indices')->first()
            : null;
    }

    public function render()
    {
        $membre = $this->membre();
        $valeurs = $membre?->indices->pluck('value', 'kind') ?? collect();
        $kinds = $this->kinds();

        return view('livewire.soiree-organisatrice', [
            'membre' => $membre,
            'indices' => collect($kinds)->take($this->revele)->map(fn ($kind) => $valeurs->get($kind)),
            'total' => count($kinds),
            'candidats' => $this->groupe()->membres,
        ]);
    }
}
