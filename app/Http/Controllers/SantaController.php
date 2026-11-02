<?php

namespace App\Http\Controllers;

use App\Mail\TirageFait;
use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use App\Services\TirageSecretSanta;
use App\Support\MotMagique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SantaController extends Controller
{
    // Côté organisateur : connecté. Il crée le groupe, partage le lien, exclut des couples, lance le tirage.
    public function index(Request $request)
    {
        $groupes = SantaGroupe::where('user_id', $request->user()->id)->latest()->get();

        return view('santa.index', compact('groupes'));
    }

    public function store(Request $request)
    {
        $donnees = $request->validate(['name' => ['required', 'string', 'max:80']]);

        $groupe = $request->user()->santaGroupes()->create($donnees);

        return redirect()->route('santa.show', $groupe);
    }

    public function show(Request $request, SantaGroupe $groupe)
    {
        abort_unless($groupe->user_id === $request->user()->id, 404);

        return view('santa.show', [
            'groupe' => $groupe->load('membres.exclus'),
        ]);
    }

    public function exclure(Request $request, SantaGroupe $groupe)
    {
        abort_unless($groupe->user_id === $request->user()->id, 404);
        abort_if($groupe->estTire(), 403, 'Le tirage a déjà eu lieu.');

        $donnees = $request->validate([
            'membre' => ['required', 'integer'],
            'exclu' => ['required', 'integer', 'different:membre'],
        ]);

        $ids = $groupe->membres()->pluck('id')->all();
        abort_unless(in_array((int) $donnees['membre'], $ids, true) && in_array((int) $donnees['exclu'], $ids, true), 404);

        // Les deux sens : un couple ne se tire pas, dans aucun sens.
        DB::table('santa_exclusions')->insertOrIgnore([
            ['member_id' => $donnees['membre'], 'excluded_id' => $donnees['exclu']],
            ['member_id' => $donnees['exclu'], 'excluded_id' => $donnees['membre']],
        ]);

        return back()->with('statut', 'Exclusion ajoutée.');
    }

    public function tirer(Request $request, SantaGroupe $groupe, TirageSecretSanta $tirage)
    {
        abort_unless($groupe->user_id === $request->user()->id, 404);

        try {
            $tirage->tirer($groupe);
        } catch (RuntimeException $erreur) {
            return back()->withErrors(['tirage' => $erreur->getMessage()]);
        }

        // Un mail qui échoue ne doit pas défaire le tirage : on le signale et on continue.
        foreach ($groupe->membres()->whereNotNull('email')->get() as $membre) {
            try {
                Mail::to($membre->email)->send(new TirageFait($groupe, $membre));
            } catch (Throwable $erreur) {
                report($erreur);
            }
        }

        return back()->with('statut', 'Tirage fait ! Chacun découvre sa cible avec son lien. Toi, tu ne sais rien.');
    }

    // Côté participant : sans compte. Il donne son prénom et ses envies, puis découvre sa cible après le tirage.
    public function invite(Request $request, string $token)
    {
        $groupe = SantaGroupe::where('share_token', $token)->firstOrFail();
        $membre = $this->membreDe($request, $groupe);

        if (! $membre) {
            return view('santa.invite', [
                'groupe' => $groupe,
                'membre' => null,
                'noms' => $groupe->membres()->orderBy('name')->pluck('name'),
            ]);
        }

        return view('santa.invite', [
            'groupe' => $groupe,
            'membre' => $membre,
            'cible' => $groupe->estTire() ? $membre->cible : null,
        ]);
    }

    public function rejoindre(Request $request, string $token)
    {
        $groupe = SantaGroupe::where('share_token', $token)->firstOrFail();
        abort_if($groupe->estTire(), 403, 'Le tirage a déjà eu lieu : il est trop tard pour rejoindre.');

        $donnees = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'magic_word' => ['required', 'string', 'min:3', 'max:40'],
            'wishes' => ['nullable', 'string', 'max:240'],
        ]);

        // Deux « Inès » dans le groupe, et plus personne ne sait qui retrouver.
        if (MotMagique::prenomPris($groupe->membres()->pluck('name'), $donnees['name'])) {
            return back()->withErrors(['name' => 'Ce prénom est déjà pris dans le groupe. Ajoute une initiale, ou retrouve-toi plus bas.'])
                ->withInput($request->except('magic_word'));
        }

        $membre = $groupe->membres()->create([
            ...$donnees,
            'name' => trim($donnees['name']),
            'magic_word' => MotMagique::normaliser($donnees['magic_word']),
        ]);
        Cookie::queue(self::cookie($groupe), $membre->token, 60 * 24 * 30);

        return redirect()->route('santa.invite', $token);
    }

    // Depuis un autre appareil : son prénom dans la liste, et son mot magique.
    public function retrouver(Request $request, string $token)
    {
        $groupe = SantaGroupe::where('share_token', $token)->firstOrFail();
        $donnees = $request->validate(['nom' => ['required', 'string'], 'mot_magique' => ['required', 'string']]);
        $membre = $groupe->membres()->where('name', $donnees['nom'])->first();

        if (! $membre || ! MotMagique::verifie($membre->magic_word, $donnees['mot_magique'])) {
            return back()->withErrors(['mot_magique' => 'Ce prénom et ce mot magique ne vont pas ensemble.'])->withInput($request->only('nom'));
        }

        Cookie::queue(self::cookie($groupe), $membre->token, 60 * 24 * 30);

        return redirect()->route('santa.invite', $token)->with('statut', 'Re-bonjour '.$membre->name.' !');
    }

    private function membreDe(Request $request, SantaGroupe $groupe): ?SantaMembre
    {
        $jeton = $request->cookie(self::cookie($groupe));

        return $jeton ? $groupe->membres()->where('token', $jeton)->with('cible')->first() : null;
    }

    public static function cookie(SantaGroupe $groupe): string
    {
        return 'santa_'.$groupe->id;
    }
}
