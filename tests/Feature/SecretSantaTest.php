<?php

namespace Tests\Feature;

use App\Models\SantaGroupe;
use App\Models\User;
use App\Services\TirageSecretSanta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class SecretSantaTest extends TestCase
{
    use RefreshDatabase;

    private User $organisatrice;

    private SantaGroupe $groupe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisatrice = User::factory()->create(['name' => 'Camille']);
        $this->groupe = $this->organisatrice->santaGroupes()->create(['name' => 'Noël en famille']);
    }

    private function participants(int $nombre): array
    {
        return collect(range(1, $nombre))
            ->map(fn ($i) => $this->groupe->membres()->create(['name' => "Personne $i"]))
            ->all();
    }

    private function exclure($a, $b): void
    {
        DB::table('santa_exclusions')->insert([
            ['member_id' => $a->id, 'excluded_id' => $b->id],
            ['member_id' => $b->id, 'excluded_id' => $a->id],
        ]);
    }

    public function test_le_tirage_ne_donne_ni_soi_meme_ni_un_exclu(): void
    {
        [$a, $b] = $this->participants(6);
        $this->exclure($a, $b);

        app(TirageSecretSanta::class)->tirer($this->groupe);

        $membres = $this->groupe->membres()->get();
        foreach ($membres as $membre) {
            $this->assertNotSame($membre->id, $membre->target_id);
        }

        // Chacun offre à une seule personne, et personne ne reçoit deux fois.
        $this->assertCount(6, array_unique($membres->pluck('target_id')->all()));

        // A et B ne se tirent pas, dans aucun sens.
        $this->assertNotSame($b->id, $a->fresh()->target_id);
        $this->assertNotSame($a->id, $b->fresh()->target_id);
        $this->assertTrue($this->groupe->fresh()->estTire());
    }

    public function test_des_exclusions_impossibles_sont_refusees(): void
    {
        [$a, $b, $c] = $this->participants(3);
        $this->exclure($a, $b);
        $this->exclure($b, $c);
        $this->exclure($a, $c);

        $this->expectException(RuntimeException::class);
        app(TirageSecretSanta::class)->tirer($this->groupe);
    }

    public function test_l_organisatrice_ne_voit_aucune_cible(): void
    {
        $this->participants(3);
        app(TirageSecretSanta::class)->tirer($this->groupe);

        $this->actingAs($this->organisatrice)
            ->get('/secret-santa/'.$this->groupe->id)
            ->assertOk()
            ->assertSee('Tu ne vois pas qui tire qui')
            ->assertDontSee('Tu offres à');
    }

    public function test_un_participant_decouvre_sa_cible_apres_le_tirage(): void
    {
        [$ines] = $this->participants(3);
        app(TirageSecretSanta::class)->tirer($this->groupe);

        $cible = $ines->fresh()->cible;

        $this->withCookie('santa_'.$this->groupe->id, $ines->token)
            ->get('/s/'.$this->groupe->share_token)
            ->assertOk()
            ->assertSee('Tu offres à')
            ->assertSee($cible->name);
    }

    public function test_avant_le_tirage_pas_de_cible(): void
    {
        [$ines] = $this->participants(3);

        $this->withCookie('santa_'.$this->groupe->id, $ines->token)
            ->get('/s/'.$this->groupe->share_token)
            ->assertOk()
            ->assertSee('Tu es dans la boucle')
            ->assertDontSee('Tu offres à');
    }

    public function test_un_participant_rejoint_avec_son_prenom(): void
    {
        $this->post('/s/'.$this->groupe->share_token, ['name' => 'Inès', 'wishes' => 'un livre'])
            ->assertRedirect('/s/'.$this->groupe->share_token)
            ->assertCookie('santa_'.$this->groupe->id);

        $this->assertDatabaseHas('santa_members', ['group_id' => $this->groupe->id, 'name' => 'Inès']);
    }

    public function test_on_ne_rejoint_pas_apres_le_tirage(): void
    {
        $this->participants(3);
        app(TirageSecretSanta::class)->tirer($this->groupe);

        $this->post('/s/'.$this->groupe->share_token, ['name' => 'Tard'])->assertForbidden();
        $this->assertDatabaseMissing('santa_members', ['name' => 'Tard']);
    }

    public function test_l_organisatrice_exclut_puis_tire_depuis_la_page(): void
    {
        // Avec 3 personnes et une exclusion, aucun tirage n'est possible : on prend 4.
        [$a, $b] = $this->participants(4);

        $this->actingAs($this->organisatrice)
            ->post('/secret-santa/'.$this->groupe->id.'/exclusions', ['membre' => $a->id, 'exclu' => $b->id])
            ->assertSessionHas('statut');
        $this->assertDatabaseHas('santa_exclusions', ['member_id' => $b->id, 'excluded_id' => $a->id]);

        $this->actingAs($this->organisatrice)
            ->post('/secret-santa/'.$this->groupe->id.'/tirage')
            ->assertSessionHas('statut');
        $this->assertTrue($this->groupe->fresh()->estTire());

        // Déjà tiré : on ne retire pas.
        $this->actingAs($this->organisatrice)
            ->post('/secret-santa/'.$this->groupe->id.'/tirage')
            ->assertSessionHasErrors('tirage');
    }

    public function test_un_autre_compte_ne_gere_pas_le_groupe(): void
    {
        $intrus = User::factory()->create();

        $this->actingAs($intrus)->get('/secret-santa/'.$this->groupe->id)->assertNotFound();
        $this->actingAs($intrus)->post('/secret-santa/'.$this->groupe->id.'/tirage')->assertNotFound();
    }
}
