<?php

namespace Tests\Feature;

use App\Models\Cadeau;
use App\Models\Idee;
use App\Models\Liste;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeesTest extends TestCase
{
    use RefreshDatabase;

    private Liste $liste;

    private Idee $vase;

    private Idee $casque;

    protected function setUp(): void
    {
        parent::setUp();

        $this->liste = Liste::create(['title' => 'Noël chez Camille', 'event_type' => 'noel']);
        $this->vase = Idee::create([
            'title' => 'Vase en grès', 'description' => 'Un vase.', 'category' => 'maison',
            'icon' => 'lampe', 'price' => 35, 'events' => ['noel', 'cremaillere'],
        ]);
        $this->casque = Idee::create([
            'title' => 'Casque audio', 'description' => 'Un casque.', 'category' => 'high-tech',
            'icon' => 'casque', 'price' => 129, 'events' => ['anniversaire'],
        ]);
    }

    public function test_le_catalogue_filtre_par_budget_et_par_evenement(): void
    {
        $this->get('/idees?budget=moins20')->assertDontSee('Vase en grès');
        $this->get('/idees?budget=de20a50')->assertSee('Vase en grès')->assertDontSee('Casque audio');
        $this->get('/idees?budget=plus50')->assertSee('Casque audio')->assertDontSee('Vase en grès');
        $this->get('/idees?evenement=anniversaire')->assertSee('Casque audio')->assertDontSee('Vase en grès');
        // Une valeur inconnue est ignorée, sans erreur.
        $this->get('/idees?budget=n-importe-quoi')->assertOk()->assertSee('Vase en grès');
    }

    public function test_un_proprietaire_ajoute_une_idee_a_sa_liste(): void
    {
        $camille = User::factory()->create();
        $this->liste->proprietaires()->attach($camille);

        $this->actingAs($camille)
            ->post('/idees/'.$this->vase->id.'/ajouter', ['liste' => $this->liste->id])
            ->assertRedirect('/mes-listes/'.$this->liste->id);

        $this->assertDatabaseHas('gifts', [
            'list_id' => $this->liste->id,
            'title' => 'Vase en grès',
            'secret' => false,
            'added_by' => null,
        ]);
    }

    public function test_on_ne_peut_pas_ajouter_une_idee_a_la_liste_d_un_autre(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/idees/'.$this->vase->id.'/ajouter', ['liste' => $this->liste->id])
            ->assertNotFound();

        $this->assertSame(0, Cadeau::count());
    }

    public function test_un_invite_ajoute_une_idee_en_secret(): void
    {
        $camille = User::factory()->create(['name' => 'Camille']);
        $this->liste->proprietaires()->attach($camille);
        $ines = $this->liste->invites()->create(['name' => 'Inès']);
        $cookie = 'invite_'.$this->liste->id;

        $this->withCookie($cookie, $ines->token)
            ->post('/l/'.$this->liste->share_token.'/idees/'.$this->vase->id)
            ->assertRedirect('/l/'.$this->liste->share_token);

        $this->assertDatabaseHas('gifts', [
            'list_id' => $this->liste->id,
            'title' => 'Vase en grès',
            'secret' => true,
            'added_by' => $ines->id,
        ]);

        // Camille ne voit pas l'idée secrète...
        $this->actingAs($camille)
            ->get('/mes-listes/'.$this->liste->id)
            ->assertOk()
            ->assertDontSee('Vase en grès');

        // ...mais les autres invités, si. Hugo n'est pas connecté : on retire la session de Camille.
        auth()->forgetUser();
        $hugo = $this->liste->invites()->create(['name' => 'Hugo']);
        $this->withCookie($cookie, $hugo->token)
            ->get('/l/'.$this->liste->share_token)
            ->assertSee('Vase en grès')
            ->assertSee('Idée secrète de Inès');
    }

    public function test_un_invite_sans_prenom_est_renvoye_vers_la_liste(): void
    {
        $this->get('/l/'.$this->liste->share_token.'/idees')
            ->assertRedirect('/l/'.$this->liste->share_token);
    }

    public function test_une_idee_ne_s_ajoute_pas_a_une_liste_passee(): void
    {
        $this->liste->update(['event_date' => now()->subDay()->toDateString()]);
        $ines = $this->liste->invites()->create(['name' => 'Inès']);

        $this->withCookie('invite_'.$this->liste->id, $ines->token)
            ->post('/l/'.$this->liste->share_token.'/idees/'.$this->vase->id)
            ->assertForbidden();

        $this->assertSame(0, Cadeau::count());
    }
}
