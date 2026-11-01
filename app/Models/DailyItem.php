<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'price', 'image', 'credit'];

    protected $casts = ['price' => 'decimal:2'];
}
