<?php

namespace App\Livewire;

use App\Models\DailyPlay;
use App\Services\TirageDuJour;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Le Juste Prix : 6 essais, « c'est plus » ou « c'est moins », gagné à 5 % près. */
class JusteUnPrix extends Component
{
    public const ESSAIS = 6;

    public const TOLERANCE = 0.05;

    #[Locked]
    public string $visiteur;

    public string $saisie = '';

    public function mount(string $visiteur): void
    {
        $this->visiteur = $visiteur;
    }

    public function proposer(TirageDuJour $tirage): void
    {
        $this->saisie = str_replace(',', '.', trim($this->saisie));
        $this->validate(['saisie' => ['required', 'numeric', 'min:0', 'max:100000']]);

        $aujourdhui = CarbonImmutable::today();
        $prix = (float) $tirage->objet('juste-prix', $aujourdhui)->price;
        $partie = DailyPlay::firstOrCreate(
            ['visitor' => $this->visiteur, 'game' => 'juste-prix', 'played_on' => $aujourdhui],
            ['guesses' => []],
        );

        if ($partie->termine(self::ESSAIS)) {
            return;
        }

        $essai = round((float) $this->saisie, 2);
        $partie->update([
            'guesses' => [...($partie->guesses ?? []), $essai],
            'won' => abs($essai - $prix) <= $prix * self::TOLERANCE,
        ]);
        $this->saisie = '';
    }

    public function render(TirageDuJour $tirage)
    {
        $aujourdhui = CarbonImmutable::today();
        $objet = $tirage->objet('juste-prix', $aujourdhui);
        $prix = (float) $objet->price;
        $partie = DailyPlay::where(['visitor' => $this->visiteur, 'game' => 'juste-prix', 'played_on' => $aujourdhui])->first();
        $essais = $partie?->guesses ?? [];
        $termine = $partie?->termine(self::ESSAIS) ?? false;

        // Le partage : une flèche par essai (⬆️ = le prix est plus haut), ✅ pour le bon.
        $fleches = collect($essais)->map(fn ($essai) => match (true) {
            abs($essai - $prix) <= $prix * self::TOLERANCE => '✅',
            $essai < $prix => '⬆️',
            default => '⬇️',
        })->implode('');
        $score = $partie?->won ? count($essais).'/'.self::ESSAIS : 'X/'.self::ESSAIS;

        return view('livewire.juste-un-prix', [
            'objet' => $objet,
            'prix' => $prix,
            'essais' => $essais,
            'termine' => $termine,
            'gagne' => (bool) $partie?->won,
            'serie' => DailyPlay::serie($this->visiteur, 'juste-prix', self::ESSAIS, $aujourdhui),
            'partage' => "🏷️ Le Juste Prix #{$tirage->numero($aujourdhui)} · $score $fleches · bolduc.projectp.fr",
        ]);
    }
}
