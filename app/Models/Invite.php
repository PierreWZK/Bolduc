<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invite extends Model
{
    protected $table = 'guests';

    protected $fillable = ['name', 'email'];

    protected static function booted(): void
    {
        static::creating(fn (self $invite) => $invite->token ??= Str::random(40));
    }

    public function liste(): BelongsTo
    {
        return $this->belongsTo(Liste::class, 'list_id');
    }
}
