<?php

namespace Tests\Feature;

use App\Models\Liste;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListesTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_liste_se_cree_avec_un_lien_secret(): void
    {
        $camille = User::factory()->create();

        $this->actingAs($camille)->post('/mes-listes', [
            'title' => 'Noël chez Camille',
            'event_type' => 'noel',
            'event_date' => now()->addDays(20)->toDateString(),
        ])->assertRedirect();

        $liste = Liste::firstOrFail();
        $this->assertSame('Noël chez Camille', $liste->title);
        $this->get('/mes-listes')->assertOk()->assertSee('Noël chez Camille');
        $this->assertSame(40, strlen($liste->share_token));
        $this->assertTrue($liste->estProprietaire($camille));
    }

    public function test_un_co_proprietaire_voit_et_gere_la_liste(): void
    {
        $camille = User::factory()->create();
        $hugo = User::factory()->create(['email' => 'hugo@exemple.test']);

        $this->actingAs($camille)->post('/mes-listes', [
            'title' => 'Mariage',
            'event_type' => 'mariage',
            'coproprietaire' => 'HUGO@exemple.test',
        ])->assertRedirect();

        $liste = Liste::firstOrFail();
        $this->assertSame(2, $liste->proprietaires()->count());
        $this->actingAs($hugo)->get('/mes-listes/'.$liste->id)->assertOk()->assertSee('Mariage');
    }

    public function test_un_co_proprietaire_sans_compte_est_refuse(): void
    {
        $camille = User::factory()->create();

        $this->actingAs($camille)->post('/mes-listes', [
            'title' => 'Mariage',
            'event_type' => 'mariage',
            'coproprietaire' => 'inconnu@exemple.test',
        ])->assertSessionHasErrors('coproprietaire');

        $this->assertSame(0, Liste::count());
    }

    public function test_une_liste_d_un_autre_repond_404(): void
    {
        $liste = Liste::create(['title' => 'Secrète', 'event_type' => 'autre']);
        $liste->proprietaires()->attach(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->get('/mes-listes/'.$liste->id)
            ->assertNotFound();
    }

    public function test_une_date_passee_est_refusee(): void
    {
        $this->actingAs(User::factory()->create())->post('/mes-listes', [
            'title' => 'Hier',
            'event_type' => 'noel',
            'event_date' => now()->subDay()->toDateString(),
        ])->assertSessionHasErrors('event_date');
    }

    public function test_le_compte_a_rebours(): void
    {
        $this->assertSame(12, (new Liste(['event_date' => now()->addDays(12)->toDateString()]))->joursAvant());
        $this->assertNull((new Liste())->joursAvant());
    }

    private function listeDe(User $user): Liste
    {
        $liste = Liste::create(['title' => 'Noël', 'event_type' => 'noel']);
        $liste->proprietaires()->attach($user);

        return $liste;
    }

    public function test_on_modifie_les_reglages_de_sa_liste(): void
    {
        $camille = User::factory()->create();
        $liste = $this->listeDe($camille);

        $this->actingAs($camille)->get(route('listes.edit', $liste))->assertOk()->assertSee('Noël');
        $this->put(route('listes.update', $liste), [
            'title' => 'Noël en famille',
            'event_type' => 'anniversaire',
            'event_date' => '2026-12-24',
        ])->assertRedirect(route('listes.show', $liste));

        $liste->refresh();
        $this->assertSame('Noël en famille', $liste->title);
        $this->assertSame('anniversaire', $liste->event_type);
        $this->assertSame('2026-12-24', $liste->event_date->toDateString());
    }

    public function test_seul_un_proprietaire_modifie_ou_archive(): void
    {
        $liste = $this->listeDe(User::factory()->create());
        $intrus = User::factory()->create();

        $this->actingAs($intrus)->get(route('listes.edit', $liste))->assertNotFound();
        $this->put(route('listes.update', $liste), ['title' => 'Piratée', 'event_type' => 'noel'])->assertNotFound();
        $this->post(route('listes.archiver', $liste))->assertNotFound();
        $this->assertSame('Noël', $liste->fresh()->title);
    }

    public function test_une_liste_archivee_passe_dans_les_archives_et_peut_revenir(): void
    {
        $camille = User::factory()->create();
        $liste = $this->listeDe($camille);

        $this->actingAs($camille)->post(route('listes.archiver', $liste))->assertRedirect(route('listes'));
        $this->assertNotNull($liste->fresh()->archived_at);
        $this->get(route('listes'))->assertSee('Archives')->assertSee('Ressortir');

        $this->post(route('listes.archiver', $liste));
        $this->assertNull($liste->fresh()->archived_at);
    }

    public function test_on_ne_supprime_qu_une_liste_archivee(): void
    {
        $camille = User::factory()->create();
        $liste = $this->listeDe($camille);
        $liste->cadeaux()->create(['title' => 'Plaid']);

        $this->actingAs($camille)->delete(route('listes.destroy', $liste))->assertForbidden();
        $this->assertModelExists($liste);

        $liste->forceFill(['archived_at' => now()])->save();
        $this->delete(route('listes.destroy', $liste))->assertRedirect(route('listes'));

        // Les cadeaux partent avec la liste.
        $this->assertModelMissing($liste);
        $this->assertDatabaseCount('gifts', 0);
    }
}
