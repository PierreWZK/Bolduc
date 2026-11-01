<?php

namespace App\Livewire;

use App\Models\DailyItem;
use App\Models\DailyPlay;
use App\Services\TirageDuJour;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Paquet mystère : un objet sous 5 couches de papier, chaque mauvaise réponse en arrache une. */
class PaquetMystere extends Component
{
    public const COUCHES = 5;

    #[Locked]
    public string $visiteur;

    public string $choix = '';

    public function mount(string $visiteur): void
    {
        $this->visiteur = $visiteur;
    }

    public function deviner(TirageDuJour $tirage): void
    {
        // La réponse se prend dans la liste : pas de faute de frappe à gérer.
        $saisi = DailyItem::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($this->choix))])->first();

        if (! $saisi) {
            $this->addError('choix', 'Choisis un objet dans la liste.');

            return;
        }

        $aujourdhui = CarbonImmutable::today();
        $objet = $tirage->objet('paquet-mystere', $aujourdhui);
        $partie = DailyPlay::firstOrCreate(
            ['visitor' => $this->visiteur, 'game' => 'paquet-mystere', 'played_on' => $aujourdhui],
            ['guesses' => []],
        );

        if ($partie->termine(self::COUCHES)) {
            return;
        }

        $partie->update([
            'guesses' => [...($partie->guesses ?? []), $saisi->id],
            'won' => $saisi->id === $objet->id,
        ]);
        $this->choix = '';
    }

    public function render(TirageDuJour $tirage)
    {
        $aujourdhui = CarbonImmutable::today();
        $objet = $tirage->objet('paquet-mystere', $aujourdhui);
        $partie = DailyPlay::where(['visitor' => $this->visiteur, 'game' => 'paquet-mystere', 'played_on' => $aujourdhui])->first();
        $mauvais = count($partie?->guesses ?? []) - ($partie?->won ? 1 : 0);
        $couches = self::COUCHES - $mauvais;
        $termine = $partie?->termine(self::COUCHES) ?? false;

        // Le partage : un carré rouge par mauvaise réponse, puis 🎁 si trouvé, ❌ sinon.
        $fin = $partie?->won ? '🎁' : '❌';
        $partage = "📦 Paquet mystère #{$tirage->numero($aujourdhui)} · trouvé avec $couches couches restantes "
            .str_repeat('🟥', $mauvais).$fin.' · bolduc.projectp.fr';

        return view('livewire.paquet-mystere', [
            'objet' => $objet,
            'objets' => DailyItem::orderBy('name')->pluck('name'),
            'couches' => $couches,
            'termine' => $termine,
            'gagne' => (bool) $partie?->won,
            'flou' => $termine ? 0 : $couches * 3,
            'serie' => DailyPlay::serie($this->visiteur, 'paquet-mystere', self::COUCHES, $aujourdhui),
            'partage' => $partage,
        ]);
    }
}
