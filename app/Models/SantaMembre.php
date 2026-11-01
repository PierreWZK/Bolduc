<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
}
