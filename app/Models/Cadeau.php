<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cadeau extends Model
{
    protected $table = 'gifts';

    protected $fillable = ['title', 'url', 'image_url', 'price', 'quantity', 'favorite', 'secret', 'added_by', 'note', 'thanked'];

    protected $casts = [
        'price' => 'decimal:2',
        'favorite' => 'boolean',
        'secret' => 'boolean',
        'thanked' => 'boolean',
    ];

    public function liste(): BelongsTo
    {
        return $this->belongsTo(Liste::class, 'list_id');
    }

    public function ajouteePar(): BelongsTo
    {
        return $this->belongsTo(Invite::class, 'added_by');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'gift_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'gift_id');
    }

    // Ces trois lectures supposent les relations chargées (vue invité).
    public function unitesPrises(): int
    {
        return (int) $this->reservations->sum('quantity');
    }

    public function montantCollecte(): float
    {
        return (float) $this->contributions->sum('amount');
    }

    // Un cadeau est complet réservé en entier, ou financé par les parts (un seul exemplaire, avec un prix).
    public function estComplet(): bool
    {
        if ($this->unitesPrises() >= $this->quantity) {
            return true;
        }

        return $this->price !== null && $this->quantity === 1 && $this->montantCollecte() >= (float) $this->price;
    }
}
