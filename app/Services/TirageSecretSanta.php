<?php

namespace App\Services;

use App\Models\SantaGroupe;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Attribue à chaque participant une cible : ni soi-même, ni une personne exclue.
 * Appelé par SantaController::tirer.
 * shortcut: tirage par essais aléatoires (500 max), pas un algorithme exact : suffit pour quelques dizaines de participants.
 */
class TirageSecretSanta
{
    private const ESSAIS = 500;

    public function tirer(SantaGroupe $groupe): void
    {
        if ($groupe->estTire()) {
            throw new RuntimeException('Le tirage a déjà eu lieu.');
        }

        $membres = $groupe->membres()->get()->values();
        if ($membres->count() < 3) {
            throw new RuntimeException('Il faut au moins trois participants pour tirer.');
        }

        $ids = $membres->pluck('id')->all();
        $exclus = [];
        foreach (DB::table('santa_exclusions')->whereIn('member_id', $ids)->get() as $ligne) {
            $exclus[$ligne->member_id][] = $ligne->excluded_id;
        }

        for ($essai = 0; $essai < self::ESSAIS; $essai++) {
            $cibles = collect($ids)->shuffle()->values()->all();

            $valide = true;
            foreach ($ids as $i => $donneur) {
                if ($cibles[$i] === $donneur || in_array($cibles[$i], $exclus[$donneur] ?? [], true)) {
                    $valide = false;
                    break;
                }
            }

            if ($valide) {
                DB::transaction(function () use ($membres, $cibles, $groupe) {
                    foreach ($membres as $i => $membre) {
                        $membre->forceFill(['target_id' => $cibles[$i]])->save();
                    }
                    $groupe->forceFill(['drawn_at' => now()])->save();
                });

                return;
            }
        }

        throw new RuntimeException('Impossible de tirer avec ces exclusions : retire-en une.');
    }
}
