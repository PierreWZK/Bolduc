<?php

namespace Tests\Feature;

use App\Models\Liste;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdreCadeauxTest extends TestCase
{
    use RefreshDatabase;

    private Liste $liste;

    private User $camille;

    protected function setUp(): void
    {
        parent::setUp();

        $this->camille = User::factory()->create();
        $this->liste = Liste::create(['title' => 'Noël chez Camille', 'event_type' => 'noel']);
        $this->liste->proprietaires()->attach($this->camille);
    }

    private function titres(): array
    {
        return $this->liste->fresh()->cadeaux->pluck('title')->all();
    }

    public function test_monter_remonte_le_cadeau_d_un_cran(): void
    {
        $a = $this->liste->cadeaux()->create(['title' => 'A', 'quantity' => 1, 'position' => 1]);
        $this->liste->cadeaux()->create(['title' => 'B', 'quantity' => 1, 'position' => 2]);
        $c = $this->liste->cadeaux()->create(['title' => 'C', 'quantity' => 1, 'position' => 3]);

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux/{$c->id}/monter")
            ->assertRedirect();

        $this->assertSame(['A', 'C', 'B'], $this->titres());
    }

    public function test_descendre_descend_le_cadeau_d_un_cran(): void
    {
        $a = $this->liste->cadeaux()->create(['title' => 'A', 'quantity' => 1, 'position' => 1]);
        $this->liste->cadeaux()->create(['title' => 'B', 'quantity' => 1, 'position' => 2]);

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux/{$a->id}/descendre");

        $this->assertSame(['B', 'A'], $this->titres());
    }

    public function test_le_premier_ne_monte_pas_plus_haut(): void
    {
        $a = $this->liste->cadeaux()->create(['title' => 'A', 'quantity' => 1, 'position' => 1]);
        $this->liste->cadeaux()->create(['title' => 'B', 'quantity' => 1, 'position' => 2]);

        $this->actingAs($this->camille)
            ->post("/mes-listes/{$this->liste->id}/cadeaux/{$a->id}/monter");

        $this->assertSame(['A', 'B'], $this->titres());
    }

    public function test_un_autre_utilisateur_ne_peut_pas_reordonner(): void
    {
        $a = $this->liste->cadeaux()->create(['title' => 'A', 'quantity' => 1, 'position' => 1]);
        $this->liste->cadeaux()->create(['title' => 'B', 'quantity' => 1, 'position' => 2]);

        $this->actingAs(User::factory()->create())
            ->post("/mes-listes/{$this->liste->id}/cadeaux/{$a->id}/descendre")
            ->assertNotFound();

        $this->assertSame(['A', 'B'], $this->titres());
    }
}
