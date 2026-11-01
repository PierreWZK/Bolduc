<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class InviteController extends Controller
{
    public function show(Request $request, string $token)
    {
        $liste = Liste::where('share_token', $token)->firstOrFail();

        // Le propriétaire ne doit jamais tomber sur la vue invité : elle montre les réservations.
        if ($request->user() && $liste->estProprietaire($request->user())) {
            return redirect()->route('listes.show', $liste)
                ->with('statut', 'C\'est ta liste : ici, tu ne vois pas les réservations.');
        }

        return view('invite.liste', [
            'liste' => $liste->load('proprietaires'),
            'invite' => $this->inviteCourant($request, $liste),
        ]);
    }

    public function prenom(Request $request, string $token)
    {
        $liste = Liste::where('share_token', $token)->firstOrFail();

        $donnees = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $invite = $liste->invites()->create($donnees);
        Cookie::queue(self::cookie($liste), $invite->token, 60 * 24 * 365);

        return redirect()->route('invite', $token);
    }

    private function inviteCourant(Request $request, Liste $liste): ?Invite
    {
        $jeton = $request->cookie(self::cookie($liste));

        return $jeton ? $liste->invites()->where('token', $jeton)->first() : null;
    }

    public static function cookie(Liste $liste): string
    {
        return 'invite_'.$liste->id;
    }
}
