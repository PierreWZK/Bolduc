<?php

namespace Tests\Feature;

use App\Mail\RappelListe;
use App\Models\Liste;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RappelsEtRemerciementsTest extends TestCase
{
    use RefreshDatabase;

    private User $camille;

    private Liste $liste;

    protected function setUp(): void
    {
        parent::setUp();

        $this->camille = User::factory()->create(['name' => 'Camille']);
        $this->liste = Liste::create(['title' => 'Noël chez Camille', 'event_type' => 'noel', 'event_date' => now()->addDays(7)->toDateString()]);
        $this->liste->proprietaires()->attach($this->camille);
    }

    public function test_le_rappel_part_a_j_moins_7_aux_invites_avec_un_email(): void
    {
        Mail::fake();
        $this->liste->invites()->create(['name' => 'Inès', 'email' => 'ines@exemple.test']);
        $this->liste->invites()->create(['name' => 'Hugo']);
        $this->liste->cadeaux()->create(['title' => 'Vase', 'quantity' => 1]);

        $this->artisan('bolduc:rappels')->assertSuccessful();

        Mail::assertSent(RappelListe::class, 1);
        Mail::assertSent(RappelListe::class, fn (RappelListe $m) => $m->hasTo('ines@exemple.test') && $m->libres === 1);

        // Lancer la commande deux fois le même jour n'envoie pas deux fois.
        $this->artisan('bolduc:rappels');
        Mail::assertSent(RappelListe::class, 1);
    }

    public function test_pas_de_rappel_en_dehors_de_j_moins_7(): void
    {
        Mail::fake();
        $this->liste->update(['event_date' => now()->addDays(8)->toDateString()]);
        $this->liste->invites()->create(['name' => 'Inès', 'email' => 'ines@exemple.test']);

        $this->artisan('bolduc:rappels');

        Mail::assertNothingSent();
    }

    public function test_merci_se_coche_apres_la_date(): void
    {
        $this->liste->update(['event_date' => now()->subDay()->toDateString()]);
        $cadeau = $this->liste->cadeaux()->create(['title' => 'Vase', 'quantity' => 1]);

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux/{$cadeau->id}/merci")
            ->assertRedirect();

        $this->assertTrue($cadeau->fresh()->thanked);
    }

    public function test_merci_refuse_avant_la_date(): void
    {
        $cadeau = $this->liste->cadeaux()->create(['title' => 'Vase', 'quantity' => 1]);

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux/{$cadeau->id}/merci")
            ->assertForbidden();

        $this->assertFalse($cadeau->fresh()->thanked);
    }

    public function test_apres_la_date_les_remerciements_montrent_qui_a_offert_quoi(): void
    {
        $this->liste->update(['event_date' => now()->subDay()->toDateString()]);
        $ines = $this->liste->invites()->create(['name' => 'Inès']);
        $hugo = $this->liste->invites()->create(['name' => 'Hugo']);

        $plaid = $this->liste->cadeaux()->create(['title' => 'Plaid en laine', 'quantity' => 1]);
        $plaid->reservations()->create(['guest_id' => $ines->id, 'quantity' => 1]);
        $this->liste->cadeaux()->create(['title' => 'Vase en grès', 'quantity' => 1, 'secret' => true, 'added_by' => $hugo->id]);

        $this->actingAs($this->camille)
            ->get('/mes-listes/'.$this->liste->id)
            ->assertOk()
            ->assertSee('Remerciements')
            ->assertSee('Offert par Inès')
            ->assertSee('Vase en grès')
            ->assertSee('Idée de Hugo, en secret')
            ->assertDontSee('Ajouter un cadeau');
    }
}
