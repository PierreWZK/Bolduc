<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\Liste;
use App\Support\MotMagique;
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
            'noms' => $liste->invites()->orderBy('name')->pluck('name'),
        ]);
    }

    public function prenom(Request $request, string $token)
    {
        $liste = Liste::where('share_token', $token)->firstOrFail();

        $donnees = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'magic_word' => ['required', 'string', 'min:3', 'max:40'],
        ]);

        if (MotMagique::prenomPris($liste->invites()->pluck('name'), $donnees['name'])) {
            return back()->withErrors(['name' => 'Ce prénom est déjà pris sur cette liste. Ajoute une initiale, ou retrouve-toi plus bas.'])
                ->withInput($request->except('magic_word'));
        }

        $invite = $liste->invites()->create([
            ...$donnees,
            'name' => trim($donnees['name']),
            'magic_word' => MotMagique::normaliser($donnees['magic_word']),
            'user_id' => $request->user()?->id,
        ]);
        Cookie::queue(self::cookie($liste), $invite->token, 60 * 24 * 365);

        return redirect()->route('invite', $token);
    }

    // Depuis un autre appareil : son prénom dans la liste, et son mot magique.
    public function retrouver(Request $request, string $token)
    {
        $liste = Liste::where('share_token', $token)->firstOrFail();
        $donnees = $request->validate(['nom' => ['required', 'string'], 'mot_magique' => ['required', 'string']]);
        $invite = $liste->invites()->where('name', $donnees['nom'])->first();

        if (! $invite || ! MotMagique::verifie($invite->magic_word, $donnees['mot_magique'])) {
            return back()->withErrors(['mot_magique' => 'Ce prénom et ce mot magique ne vont pas ensemble.'])->withInput($request->only('nom'));
        }

        $this->rattacher($request, $invite);
        Cookie::queue(self::cookie($liste), $invite->token, 60 * 24 * 365);

        return redirect()->route('invite', $token)->with('statut', 'Re-bonjour '.$invite->name.' !');
    }

    private function inviteCourant(Request $request, Liste $liste): ?Invite
    {
        $jeton = $request->cookie(self::cookie($liste));
        $invite = $jeton ? $liste->invites()->where('token', $jeton)->first() : null;

        // Connecté et déjà invité depuis un autre appareil : on le reconnaît sans mot magique.
        if (! $invite && $request->user()) {
            $invite = $liste->invites()->where('user_id', $request->user()->id)->first();
            if ($invite) {
                Cookie::queue(self::cookie($liste), $invite->token, 60 * 24 * 365);
            }
        }

        if ($invite) {
            $this->rattacher($request, $invite);
        }

        return $invite;
    }

    // L'invité connecté retrouve cette liste dans ses « Listes des autres ».
    private function rattacher(Request $request, Invite $invite): void
    {
        if ($request->user() && ! $invite->user_id) {
            $invite->forceFill(['user_id' => $request->user()->id])->save();
        }
    }

    public static function cookie(Liste $liste): string
    {
        return 'invite_'.$liste->id;
    }
}
