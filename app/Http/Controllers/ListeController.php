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

        return view('listes.index', [
            'listes' => $listes->whereNull('archived_at'),
            'archives' => $listes->whereNotNull('archived_at'),
        ]);
    }

    public function edit(Request $request, Liste $liste)
    {
        abort_unless($liste->estProprietaire($request->user()), 404);

        return view('listes.nouvelle', ['evenements' => Liste::EVENEMENTS, 'liste' => $liste]);
    }

    public function update(Request $request, Liste $liste)
    {
        abort_unless($liste->estProprietaire($request->user()), 404);

        // Pas de « après aujourd'hui » ici : on doit pouvoir corriger la date d'une fête déjà passée.
        $liste->update($request->validate([
            'title' => ['required', 'string', 'max:80'],
            'event_type' => ['required', Rule::in(array_keys(Liste::EVENEMENTS))],
            'event_date' => ['nullable', 'date'],
        ]));

        return redirect()->route('listes.show', $liste)->with('statut', 'La liste est à jour.');
    }

    // Archiver met la liste de côté ; le même bouton la ressort des archives.
    public function archiver(Request $request, Liste $liste)
    {
        abort_unless($liste->estProprietaire($request->user()), 404);

        $liste->forceFill(['archived_at' => $liste->archived_at ? null : now()])->save();

        return redirect()->route('listes')->with('statut', $liste->archived_at
            ? 'Liste archivée : elle t\'attend dans les archives.'
            : 'Liste ressortie des archives.');
    }

    public function destroy(Request $request, Liste $liste)
    {
        abort_unless($liste->estProprietaire($request->user()), 404);
        abort_unless($liste->archived_at, 403, 'Archive la liste avant de la supprimer.');

        $liste->delete();

        return redirect()->route('listes')->with('statut', 'Liste supprimée pour de bon.');
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

        // Avant la date, les idées secrètes restent cachées : c'est toute la surprise.
        // Après, le propriétaire découvre qui a offert quoi (remerciements).
        $passee = $liste->estPassee();

        return view('listes.show', [
            'passee' => $passee,
            'liste' => $liste->load([
                'proprietaires',
                'cadeaux' => function ($requete) use ($passee) {
                    if (! $passee) {
                        $requete->where('secret', false);
                    }
                },
                'cadeaux.reservations.invite',
                'cadeaux.contributions.invite',
                'cadeaux.ajouteePar',
            ]),
        ]);
    }
}
