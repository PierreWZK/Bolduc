<?php

namespace App\Livewire;

use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use App\Models\SoireeIndice;
use App\Models\SoireeManche;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Les deux jeux du soir : deviner son donneur, puis deviner son cadeau. */
class Soiree extends Component
{
    #[Locked]
    public int $groupeId;

    #[Locked]
    public int $membreId;

    public string $devinetteCadeau = '';

    public ?string $message = null;

    public function mount(SantaGroupe $groupe, SantaMembre $membre): void
    {
        $this->groupeId = $groupe->id;
        $this->membreId = $membre->id;
    }

    // « Qui m'a gâté ? » : le joueur devine son donneur. Chaque indice de plus fait perdre un point.
    public function indiceSuivantQui(): void
    {
        $this->indiceSuivant($this->manche($this->membreId, 'qui'), SoireeIndice::QUI);
    }

    public function devinerQui(int $candidatId): void
    {
        $membre = $this->membre();
        $manche = $this->manche($membre->id, 'qui');

        if ($manche->solved) {
            return;
        }

        $candidat = $this->groupe()->membres()->whereKey($candidatId)->whereKeyNot($membre->id)->first();

        if (! $candidat) {
            return;
        }

        if ($candidat->id === $membre->donneur()?->id) {
            $manche->gagner();
            $this->message = 'Bravo, c\'est bien ton donneur !';
        } else {
            $this->message = 'Raté, ce n\'est pas la bonne personne. Essaie encore.';
        }
    }

    // « Devine ton cadeau » : le donneur lance le jeu pour sa cible, qui devine ce qu'elle reçoit.
    public function lancerCadeau(int $cibleId): void
    {
        $membre = $this->membre();

        if ((int) $membre->target_id !== $cibleId) {
            return;
        }

        $this->manche($cibleId, 'cadeau');
        $this->message = 'C\'est lancé : la personne peut deviner son cadeau.';
    }

    public function indiceSuivantCadeau(): void
    {
        $this->indiceSuivant($this->mancheCadeau(), SoireeIndice::CADEAU);
    }

    public function devinerCadeau(): void
    {
        $this->validate(['devinetteCadeau' => ['required', 'string', 'max:120']]);

        $manche = $this->mancheCadeau();

        if (! $manche || $manche->solved) {
            return;
        }

        $manche->update(['guess' => $this->devinetteCadeau]);
        $this->devinetteCadeau = '';
        $this->message = 'Ta devinette est envoyée au donneur.';
    }

    // Le donneur valide la devinette de sa cible, ou la refuse pour qu'elle réessaie.
    public function valider(bool $reussi): void
    {
        $manche = SoireeManche::where(['member_id' => $this->membre()->target_id, 'game' => 'cadeau'])->first();

        if (! $manche || $manche->solved) {
            return;
        }

        if ($reussi) {
            $manche->gagner();
            $this->message = 'Bravo, la devinette est juste !';
        } else {
            $manche->update(['guess' => null]);
            $this->message = 'Pas tout à fait : la personne peut réessayer.';
        }
    }

    private function indiceSuivant(?SoireeManche $manche, array $indices): void
    {
        if (! $manche || $manche->solved) {
            return;
        }

        $manche->update(['revealed' => min($manche->revealed + 1, count($indices))]);
    }

    private function manche(int $membreId, string $jeu): SoireeManche
    {
        return SoireeManche::firstOrCreate(['member_id' => $membreId, 'game' => $jeu], ['revealed' => 1]);
    }

    private function mancheCadeau(): ?SoireeManche
    {
        return SoireeManche::where(['member_id' => $this->membreId, 'game' => 'cadeau'])->first();
    }

    private function membre(): SantaMembre
    {
        return SantaMembre::whereKey($this->membreId)->where('group_id', $this->groupeId)->firstOrFail();
    }

    private function groupe(): SantaGroupe
    {
        return SantaGroupe::findOrFail($this->groupeId);
    }

    public function render()
    {
        $groupe = $this->groupe()->load('membres');
        $membre = $this->membre();
        $donneur = $membre->donneur();
        $indices = $donneur?->indices->keyBy('kind') ?? collect();
        $cible = $membre->cible;

        $mancheQui = $this->manche($membre->id, 'qui');
        $mancheCadeau = $this->mancheCadeau();
        $mancheCible = $cible ? SoireeManche::where(['member_id' => $cible->id, 'game' => 'cadeau'])->first() : null;

        $points = SoireeManche::whereIn('member_id', $groupe->membres->pluck('id'))
            ->selectRaw('member_id, sum(points) as total')
            ->groupBy('member_id')
            ->pluck('total', 'member_id');

        return view('livewire.soiree', [
            'donneur' => $donneur,
            'cible' => $cible,
            'mancheQui' => $mancheQui,
            'mancheCadeau' => $mancheCadeau,
            'mancheCible' => $mancheCible,
            'cluesQui' => $this->indices($indices, SoireeIndice::QUI, $mancheQui),
            'cluesCadeau' => $this->indices($indices, SoireeIndice::CADEAU, $mancheCadeau),
            'candidats' => $groupe->membres->where('id', '!=', $membre->id),
            'classement' => $groupe->membres
                ->map(fn ($joueur) => ['nom' => $joueur->name, 'points' => (int) ($points[$joueur->id] ?? 0)])
                ->sortByDesc('points')
                ->values(),
        ]);
    }

    // Les indices déjà révélés, dans l'ordre ; un indice pas encore écrit s'affiche vide.
    private function indices(Collection $indices, array $kinds, ?SoireeManche $manche): Collection
    {
        return collect($kinds)->take($manche?->revealed ?? 0)->map(fn ($kind) => $indices->get($kind)?->value);
    }
}
