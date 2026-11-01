<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Idee extends Model
{
    protected $table = 'ideas';

    protected $fillable = ['title', 'description', 'category', 'icon', 'price', 'events'];

    protected $casts = [
        'price' => 'decimal:2',
        'events' => 'array',
    ];

    // Filtre budget de /idees : moins de 20 €, de 20 à 50 €, plus de 50 €.
    public const BUDGETS = [
        'moins20' => 'Moins de 20 €',
        'de20a50' => 'De 20 à 50 €',
        'plus50' => 'Plus de 50 €',
    ];

    public function scopeBudget($requete, string $budget)
    {
        return match ($budget) {
            'moins20' => $requete->where('price', '<', 20),
            'de20a50' => $requete->whereBetween('price', [20, 50]),
            'plus50' => $requete->where('price', '>', 50),
            default => $requete,
        };
    }

    public function scopeEvenement($requete, string $evenement)
    {
        return $requete->whereJsonContains('events', $evenement);
    }
}
