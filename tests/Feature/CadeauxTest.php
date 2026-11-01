<?php

namespace Tests\Feature;

use App\Models\Cadeau;
use App\Models\Liste;
use App\Models\User;
use App\Services\LectureDeLien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class CadeauxTest extends TestCase
{
    use RefreshDatabase;

    private const PAGE_BOUTIQUE = '<html><head><title>Boutique</title>'
        .'<meta property="og:title" content="Plaid en laine">'
        .'<meta property="og:image" content="https://boutique.test/plaid.jpg">'
        .'<meta property="product:price:amount" content="59,90">'
        .'</head><body></body></html>';

    private User $camille;

    private Liste $liste;

    protected function setUp(): void
    {
        parent::setUp();

        $this->camille = User::factory()->create();
        $this->liste = Liste::create(['title' => 'Noël chez Camille', 'event_type' => 'noel']);
        $this->liste->proprietaires()->attach($this->camille);
    }

    // Le DNS n'est pas dispo en test : on fournit l'adresse publique de la boutique.
    private function boutiqueEnLigne(): void
    {
        $this->mock(LectureDeLien::class, function (MockInterface $m) {
            $m->makePartial()->shouldAllowMockingProtectedMethods()
                ->shouldReceive('ipDe')->andReturn('93.184.216.34');
        });
    }

    public function test_un_lien_de_boutique_remplit_titre_image_et_prix(): void
    {
        $this->boutiqueEnLigne();
        Http::fake(['https://boutique.test/*' => Http::response(self::PAGE_BOUTIQUE, 200)]);

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux", ['url' => 'https://boutique.test/plaid'])
            ->assertSessionHas('statut');

        $cadeau = Cadeau::firstOrFail();
        $this->assertSame('Plaid en laine', $cadeau->title);
        $this->assertSame('https://boutique.test/plaid.jpg', $cadeau->image_url);
        $this->assertSame('59.90', $cadeau->price);
        $this->assertSame(1, $cadeau->quantity);
    }

    public function test_un_lien_interne_n_est_jamais_suivi(): void
    {
        Http::fake();

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux", ['url' => 'http://127.0.0.1/admin'])
            ->assertSessionHasErrors('title');

        Http::assertNothingSent();
        $this->assertSame(0, Cadeau::count());
    }

    public function test_sans_titre_lisible_il_faut_l_ecrire(): void
    {
        $this->boutiqueEnLigne();
        Http::fake(['https://boutique.test/*' => Http::response('<html></html>', 200)]);

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux", ['url' => 'https://boutique.test/vide'])
            ->assertSessionHasErrors('title');

        $this->assertSame(0, Cadeau::count());
    }

    public function test_un_cadeau_saisi_a_la_main_garde_ses_valeurs(): void
    {
        $this->actingAs($this->camille)->post("/mes-listes/{$this->liste->id}/cadeaux", [
            'title' => 'Six verres à vin',
            'price' => '42',
            'quantity' => '6',
            'note' => 'En cristal',
            'favorite' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('gifts', [
            'list_id' => $this->liste->id,
            'title' => 'Six verres à vin',
            'quantity' => 6,
            'favorite' => true,
        ]);
    }

    public function test_seul_un_proprietaire_ajoute_ou_retire(): void
    {
        $intrus = User::factory()->create();
        $this->actingAs($intrus)
            ->post("/mes-listes/{$this->liste->id}/cadeaux", ['title' => 'Piège'])
            ->assertNotFound();

        $cadeau = $this->liste->cadeaux()->create(['title' => 'Bougie', 'quantity' => 1]);

        $this->actingAs($intrus)
            ->delete("/mes-listes/{$this->liste->id}/cadeaux/{$cadeau->id}")
            ->assertNotFound();
        $this->assertDatabaseHas('gifts', ['id' => $cadeau->id]);

        $this->actingAs($this->camille)
            ->delete("/mes-listes/{$this->liste->id}/cadeaux/{$cadeau->id}")
            ->assertSessionHas('statut');
        $this->assertDatabaseMissing('gifts', ['id' => $cadeau->id]);
    }

    public function test_un_cadeau_ne_se_retire_pas_depuis_une_autre_liste(): void
    {
        $autre = Liste::create(['title' => 'Crémaillère', 'event_type' => 'cremaillere']);
        $autre->proprietaires()->attach($this->camille);
        $cadeau = $autre->cadeaux()->create(['title' => 'Monstera', 'quantity' => 1]);

        $this->actingAs($this->camille)
            ->delete("/mes-listes/{$this->liste->id}/cadeaux/{$cadeau->id}")
            ->assertNotFound();
        $this->assertDatabaseHas('gifts', ['id' => $cadeau->id]);
    }
}
