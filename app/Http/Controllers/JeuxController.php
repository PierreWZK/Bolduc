<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class JeuxController extends Controller
{
    // Un visiteur = un cookie anonyme : une partie par jeu et par jour, et une série qui lui appartient.
    public function index(Request $request)
    {
        $visiteur = $request->cookie('jeux_visiteur') ?: Str::random(40);
        Cookie::queue('jeux_visiteur', $visiteur, 60 * 24 * 365);

        return view('jeux', compact('visiteur'));
    }
}
