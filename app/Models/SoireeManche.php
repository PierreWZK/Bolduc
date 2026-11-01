<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoireeManche extends Model
{
    protected $table = 'soiree_manches';

    protected $fillable = ['member_id', 'game', 'revealed', 'solved', 'points', 'guess'];

    protected $casts = ['solved' => 'boolean'];

    // Trois indices, et chaque indice de plus fait perdre un point : 3, 2 puis 1.
    public function gagner(): void
    {
        $this->forceFill(['solved' => true, 'points' => 4 - $this->revealed])->save();
    }
}
