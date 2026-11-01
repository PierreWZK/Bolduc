<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cadeau extends Model
{
    protected $table = 'gifts';

    protected $fillable = ['title', 'url', 'image_url', 'price', 'quantity', 'favorite', 'shared', 'secret', 'added_by', 'note', 'thanked'];

    protected $casts = [
        'price' => 'decimal:2',
        'favorite' => 'boolean',
        'shared' => 'boolean',
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

    public function estComplet(): bool
    {
        return $this->shared
            ? $this->price !== null && $this->montantCollecte() >= (float) $this->price
            : $this->unitesPrises() >= $this->quantity;
    }
}
