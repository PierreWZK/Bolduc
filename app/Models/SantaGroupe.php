<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SantaGroupe extends Model
{
    protected $table = 'santa_groups';

    protected $fillable = ['name'];

    protected $casts = ['drawn_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn (self $groupe) => $groupe->share_token ??= Str::random(40));
    }

    public function organisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function membres(): HasMany
    {
        return $this->hasMany(SantaMembre::class, 'group_id')->orderBy('id');
    }

    public function estTire(): bool
    {
        return $this->drawn_at !== null;
    }
}
