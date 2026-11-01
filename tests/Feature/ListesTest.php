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
}
