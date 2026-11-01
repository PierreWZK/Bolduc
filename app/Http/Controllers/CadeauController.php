<?php

namespace App\Http\Controllers;

use App\Models\Cadeau;
use App\Models\Liste;
use App\Services\LectureDeLien;
use Illuminate\Http\Request;

class CadeauController extends Controller
{
    public function store(Request $request, Liste $liste, LectureDeLien $lecture)
    {
        abort_unless($liste->estProprietaire($request->user()), 404);

        $donnees = $request->validate([
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'title' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'note' => ['nullable', 'string', 'max:120'],
            'favorite' => ['nullable', 'boolean'],
        ]);

        $lu = filled($donnees['url'] ?? null)
            ? $lecture->lire($donnees['url'])
            : ['title' => null, 'image' => null, 'price' => null];

        $titre = $donnees['title'] ?? $lu['title'];
        if (! $titre) {
            return back()->withInput()->withErrors(['title' => 'Le lien n\'a pas livré de titre : écris-le à la main.']);
        }

        $liste->cadeaux()->create([
            'title' => $titre,
            'url' => $donnees['url'] ?? null,
            'image_url' => $lu['image'],
            'price' => $donnees['price'] ?? $lu['price'],
            'quantity' => $donnees['quantity'] ?? 1,
            'favorite' => $request->boolean('favorite'),
            'note' => $donnees['note'] ?? null,
        ]);

        return back()->with('statut', 'Cadeau ajouté à ta liste.');
    }

    public function destroy(Request $request, Liste $liste, Cadeau $cadeau)
    {
        abort_unless($liste->estProprietaire($request->user()), 404);
        abort_unless($cadeau->list_id === $liste->id, 404);

        $cadeau->delete();

        return back()->with('statut', 'Cadeau retiré.');
    }
}
