<?php

namespace App\Http\Controllers;

use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use App\Services\TirageSecretSanta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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

        return back()->with('statut', 'Tirage fait ! Chacun découvre sa cible avec son lien. Toi, tu ne sais rien.');
    }

    // Côté participant : sans compte. Il donne son prénom et ses envies, puis découvre sa cible après le tirage.
    public function invite(Request $request, string $token)
    {
        $groupe = SantaGroupe::where('share_token', $token)->firstOrFail();
        $membre = $this->membreDe($request, $groupe);

        if (! $membre) {
            return view('santa.invite', ['groupe' => $groupe, 'membre' => null]);
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
            'wishes' => ['nullable', 'string', 'max:240'],
        ]);

        $membre = $groupe->membres()->create($donnees);
        Cookie::queue(self::cookie($groupe), $membre->token, 60 * 24 * 30);

        return redirect()->route('santa.invite', $token);
    }

    private function membreDe(Request $request, SantaGroupe $groupe): ?SantaMembre
    {
        $jeton = $request->cookie(self::cookie($groupe));

        return $jeton ? $groupe->membres()->where('token', $jeton)->with('cible')->first() : null;
    }

    private static function cookie(SantaGroupe $groupe): string
    {
        return 'santa_'.$groupe->id;
    }
}
