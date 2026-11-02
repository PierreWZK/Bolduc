<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Le petit mot choisi par un participant pour se retrouver depuis un autre appareil. */
final class MotMagique
{
    // Ni majuscules ni espaces autour : plus simple à retaper sur un téléphone.
    public static function normaliser(string $mot): string
    {
        return Str::lower(trim($mot));
    }

    public static function verifie(?string $hache, string $mot): bool
    {
        return $hache !== null && Hash::check(self::normaliser($mot), $hache);
    }

    // Comparaison en PHP : SQLite ne met pas en minuscules les lettres accentuées (Inès, INÈS).
    public static function prenomPris(Collection $noms, string $nom): bool
    {
        $nom = mb_strtolower(trim($nom));

        return $noms->contains(fn (string $existant) => mb_strtolower($existant) === $nom);
    }
}
