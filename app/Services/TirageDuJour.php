<?php

namespace App\Services;

use App\Models\DailyItem;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/** Le même objet pour tout le monde, un jour donné ; chaque jeu tire le sien. */
class TirageDuJour
{
    public const LANCEMENT = '2026-11-01';

    public function objet(string $jeu, CarbonInterface $jour): DailyItem
    {
        $objets = DailyItem::orderBy('id')->get();

        return $objets[abs(crc32($jeu.$jour->toDateString())) % $objets->count()];
    }

    // Numéro du jour depuis le lancement : 1 le premier jour, 2 le lendemain…
    public function numero(CarbonInterface $jour): int
    {
        return (int) CarbonImmutable::parse(self::LANCEMENT)->diffInDays($jour) + 1;
    }
}
