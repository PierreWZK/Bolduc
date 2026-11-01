<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SantaMembre extends Model
{
    protected $table = 'santa_members';

    protected $fillable = ['name', 'wishes'];

    protected static function booted(): void
    {
        static::creating(fn (self $membre) => $membre->token ??= Str::random(40));
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(SantaGroupe::class, 'group_id');
    }

    public function cible(): BelongsTo
    {
        return $this->belongsTo(self::class, 'target_id');
    }

    public function exclus(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'santa_exclusions', 'member_id', 'excluded_id');
    }

    // Celui qui m'offre un cadeau : la personne dont je suis la cible.
    public function donneur(): ?self
    {
        return self::where('target_id', $this->id)->first();
    }

    public function indices(): HasMany
    {
        return $this->hasMany(SoireeIndice::class, 'member_id');
    }
}
