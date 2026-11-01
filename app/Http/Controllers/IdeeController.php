<?php

namespace App\Http\Controllers;

use App\Models\Idee;
use App\Models\Invite;
use App\Models\Liste;
use Illuminate\Http\Request;

class IdeeController extends Controller
{
    // Accessible sans compte ; avec un jeton de liste, l'invité ajoute en secret à cette liste.
    public function index(Request $request, ?string $token = null)
    {
        $liste = $invite = null;

        if ($token) {
            $liste = Liste::where('share_token', $token)->firstOrFail();
            $invite = $this->inviteDe($request, $liste);

            if (! $invite) {
                return redirect()->route('invite', $token)->with('statut', 'Dis-nous ton prénom, puis choisis tes idées.');
            }
        }

        $evenement = array_key_exists($request->query('evenement'), Liste::EVENEMENTS) ? $request->query('evenement') : null;
        $budget = array_key_exists($request->query('budget'), Idee::BUDGETS) ? $request->query('budget') : null;

        $idees = Idee::query()
            ->when($evenement, fn ($requete) => $requete->evenement($evenement))
            ->when($budget, fn ($requete) => $requete->budget($budget))
            ->orderBy('price')
            ->get();

        return view('idees.index', [
            'idees' => $idees,
            'liste' => $liste,
            'invite' => $invite,
            'evenement' => $evenement,
            'budget' => $budget,
            'evenements' => Liste::EVENEMENTS,
            'budgets' => Idee::BUDGETS,
            'mesListes' => $request->user()?->listes()->orderBy('lists.title')->get() ?? collect(),
        ]);
    }

    // Le propriétaire ajoute l'idée à une de ses listes.
    public function ajouter(Request $request, Idee $idee)
    {
        $donnees = $request->validate(['liste' => ['required', 'integer', 'exists:lists,id']]);
        $liste = Liste::findOrFail($donnees['liste']);
        abort_unless($liste->estProprietaire($request->user()), 404);

        $liste->cadeaux()->create($this->attributs($idee));

        return redirect()->route('listes.show', $liste)->with('statut', 'Idée ajoutée à ta liste.');
    }

    // Un invité ajoute l'idée en secret à la liste qu'il consulte.
    public function ajouterEnSecret(Request $request, string $token, Idee $idee)
    {
        $liste = Liste::where('share_token', $token)->firstOrFail();
        $invite = $this->inviteDe($request, $liste);

        abort_unless($invite, 403);
        abort_if($liste->estPassee(), 403, 'La liste est fermée.');

        // array_merge et non « + » : les clés de droite doivent l'emporter sur celles de attributs().
        $liste->cadeaux()->create(array_merge($this->attributs($idee), ['secret' => true, 'added_by' => $invite->id]));

        return redirect()->route('invite', $token)
            ->with('statut', 'Idée ajoutée en secret : '.$liste->proprietaires->pluck('name')->join(' et ').' ne la verra pas.');
    }

    // Une idée devient un cadeau ordinaire : même fiche, même réservation.
    private function attributs(Idee $idee): array
    {
        return [
            'title' => $idee->title,
            'price' => $idee->price,
            'quantity' => 1,
            'favorite' => false,
            'shared' => false,
            'secret' => false,
        ];
    }

    private function inviteDe(Request $request, Liste $liste): ?Invite
    {
        $jeton = $request->cookie(InviteController::cookie($liste));

        return $jeton ? $liste->invites()->where('token', $jeton)->first() : null;
    }
}
