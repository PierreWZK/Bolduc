<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class DailyPlay extends Model
{
    protected $fillable = ['visitor', 'game', 'played_on', 'guesses', 'won'];

    protected $casts = ['played_on' => 'date', 'guesses' => 'array', 'won' => 'boolean'];

    // Une partie est finie quand elle est gagnée ou quand tous les essais sont passés.
    public function termine(int $essaisMax): bool
    {
        return $this->won || count($this->guesses ?? []) >= $essaisMax;
    }

    // Jours consécutifs, jusqu'à aujourd'hui ou hier, où la partie a été terminée.
    public static function serie(string $visiteur, string $jeu, int $essaisMax, CarbonInterface $aujourdhui): int
    {
        $jours = static::where(['visitor' => $visiteur, 'game' => $jeu])
            ->orderByDesc('played_on')
            ->get()
            ->filter(fn (self $partie) => $partie->termine($essaisMax))
            ->map(fn (self $partie) => $partie->played_on->toDateString())
            ->flip();

        $jour = $aujourdhui->copy();
        if (! $jours->has($jour->toDateString())) {
            $jour = $jour->subDay();
        }

        $serie = 0;
        while ($jours->has($jour->toDateString())) {
            $serie++;
            $jour = $jour->subDay();
        }

        return $serie;
    }
}
