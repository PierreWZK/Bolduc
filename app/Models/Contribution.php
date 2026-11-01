<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    protected $fillable = ['guest_id', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function invite(): BelongsTo
    {
        return $this->belongsTo(Invite::class, 'guest_id');
    }
}
