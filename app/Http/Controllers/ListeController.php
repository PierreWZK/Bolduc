<?php

namespace App\Http\Controllers;

use App\Models\Liste;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ListeController extends Controller
{
    public function index(Request $request)
    {
        $listes = $request->user()->listes()->orderByDesc('lists.created_at')->get();

        return view('listes.index', compact('listes'));
    }

    public function create()
    {
        return view('listes.nouvelle', ['evenements' => Liste::EVENEMENTS]);
    }

    public function store(Request $request)
    {
        $request->merge(['coproprietaire' => Str::lower((string) $request->input('coproprietaire'))]);
        $donnees = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'event_type' => ['required', Rule::in(array_keys(Liste::EVENEMENTS))],
            'event_date' => ['nullable', 'date', 'after:today'],
            'coproprietaire' => ['nullable', 'email', 'max:255'],
        ]);

        $coproprietaire = null;
        if (filled($donnees['coproprietaire'])) {
            $coproprietaire = User::where('email', $donnees['coproprietaire'])->first();

            if (! $coproprietaire || $coproprietaire->is($request->user())) {
                return back()->withErrors(['coproprietaire' => 'Aucun autre compte à cet e-mail. Ton co-propriétaire doit d\'abord créer le sien.'])->withInput();
            }
        }

        $liste = Liste::create(Arr::only($donnees, ['title', 'event_type', 'event_date']));
        $liste->proprietaires()->attach(array_filter([$request->user()->id, $coproprietaire?->id]));

        return redirect()->route('listes.show', $liste)->with('statut', 'Ta liste est créée. Envoie-la quand tu veux.');
    }

    public function show(Request $request, Liste $liste)
    {
        // Une liste qui n'est pas à toi n'existe pas pour toi.
        abort_unless($liste->estProprietaire($request->user()), 404);

        // Les idées ajoutées en secret par un invité n'apparaissent pas ici : c'est toute la surprise.
        return view('listes.show', ['liste' => $liste->load([
            'proprietaires',
            'cadeaux' => fn ($requete) => $requete->where('secret', false),
        ])]);
    }
}
