<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = ['guest_id', 'quantity'];

    public function invite(): BelongsTo
    {
        return $this->belongsTo(Invite::class, 'guest_id');
    }
}
