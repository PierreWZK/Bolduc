<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Liste extends Model
{
    protected $table = 'lists';

    protected $fillable = ['title', 'event_type', 'event_date'];

    protected $casts = ['event_date' => 'date', 'archived_at' => 'datetime'];

    public const EVENEMENTS = [
        'noel' => 'Noël',
        'anniversaire' => 'Anniversaire',
        'mariage' => 'Mariage',
        'cremaillere' => 'Crémaillère',
        'naissance' => 'Naissance',
        'autre' => 'Autre',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $liste) => $liste->share_token ??= Str::random(40));
    }

    public function proprietaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_owners', 'list_id', 'user_id');
    }

    public function cadeaux(): HasMany
    {
        return $this->hasMany(Cadeau::class, 'list_id')->orderBy('id');
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class, 'list_id');
    }

    public function estProprietaire(User $user): bool
    {
        return $this->proprietaires()->whereKey($user->id)->exists();
    }

    public function libelleEvenement(): string
    {
        return self::EVENEMENTS[$this->event_type] ?? 'Autre';
    }

    public function estPassee(): bool
    {
        $jours = $this->joursAvant();

        return $jours !== null && $jours < 0;
    }

    // Jours restants avant l'événement ; négatif s'il est passé, null sans date.
    public function joursAvant(): ?int
    {
        return $this->event_date
            ? (int) now()->startOfDay()->diffInDays($this->event_date, false)
            : null;
    }
}
