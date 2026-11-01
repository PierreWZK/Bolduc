<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cadeau extends Model
{
    protected $table = 'gifts';

    protected $fillable = ['title', 'url', 'image_url', 'price', 'quantity', 'favorite', 'note'];

    protected $casts = [
        'price' => 'decimal:2',
        'favorite' => 'boolean',
    ];

    public function liste(): BelongsTo
    {
        return $this->belongsTo(Liste::class, 'list_id');
    }
}
