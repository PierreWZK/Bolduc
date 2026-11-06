<?php

namespace App\Http\Controllers;

use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use Illuminate\Http\Request;

class SoireeController extends Controller
{
    // Le donneur écrit les indices de son cadeau et de lui-même, une fois le tirage fait.
    public function indices(Request $request, string $token)
    {
        [$groupe, $membre] = $this->joueur($request, $token);

        return view('soiree.indices', [
            'groupe' => $groupe,
            'cible' => $membre->cible,
            'valeurs' => $membre->indices->pluck('value', 'kind'),
        ]);
    }

    public function sauverIndices(Request $request, string $token)
    {
        [$groupe, $membre] = $this->joueur($request, $token);

        $donnees = $request->validate([
            'emoji' => ['required', 'string', 'max:20'],
            'surnom' => ['required', 'string', 'max:120'],
            'phrase' => ['required', 'string', 'max:120'],
            'cadeau1' => ['required', 'string', 'max:120'],
            'cadeau2' => ['required', 'string', 'max:120'],
            'cadeau3' => ['required', 'string', 'max:120'],
        ]);

        foreach ($donnees as $kind => $value) {
            $membre->indices()->updateOrCreate(['kind' => $kind], ['value' => $value]);
        }

        return redirect()->route('santa.invite', $token)->with('statut', 'Tes indices sont notés.');
    }

    // L'organisatrice seule : le jeu tourne sur son écran, les participants ne savent pas qui les tire.
    public function organisatrice(Request $request, SantaGroupe $groupe)
    {
        abort_unless($groupe->user_id === $request->user()->id, 404);
        abort_unless($groupe->estTire(), 403, 'Le tirage n\'a pas encore eu lieu.');

        return view('soiree.organisatrice', ['groupe' => $groupe]);
    }

    // Seul un participant du groupe, identifié par son cookie, peut jouer ou écrire.
    private function joueur(Request $request, string $token): array
    {
        $groupe = SantaGroupe::where('share_token', $token)->firstOrFail();
        abort_unless($groupe->estTire(), 403, 'Le tirage n\'a pas encore eu lieu.');

        $jeton = $request->cookie(SantaController::cookie($groupe));
        $membre = $jeton ? $groupe->membres()->where('token', $jeton)->with('cible')->first() : null;
        abort_unless($membre instanceof SantaMembre, 403, 'Rejoins le Secret Santa avant de jouer.');

        return [$groupe, $membre];
    }
}
