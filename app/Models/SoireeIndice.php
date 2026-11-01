<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoireeIndice extends Model
{
    protected $table = 'soiree_indices';

    public $timestamps = false;

    protected $fillable = ['member_id', 'kind', 'value'];

    // Ordre d'affichage : les trois indices du jeu « Qui m'a gâté ? », puis ceux du cadeau.
    public const QUI = ['emoji', 'surnom', 'phrase'];

    public const CADEAU = ['cadeau1', 'cadeau2', 'cadeau3'];
}
