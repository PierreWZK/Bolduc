<?php

namespace Tests\Feature;

use App\Mail\TirageFait;
use App\Models\SantaGroupe;
use App\Models\User;
use App\Services\TirageSecretSanta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
        $this->post('/s/'.$this->groupe->share_token, ['name' => 'Inès', 'email' => 'ines@exemple.test', 'magic_word' => 'Clémentine', 'wishes' => 'un livre'])
            ->assertRedirect('/s/'.$this->groupe->share_token)
            ->assertCookie('santa_'.$this->groupe->id);

        $this->assertDatabaseHas('santa_members', ['group_id' => $this->groupe->id, 'name' => 'Inès', 'email' => 'ines@exemple.test']);
        // Le mot magique n'est jamais stocké en clair.
        $this->assertDatabaseMissing('santa_members', ['magic_word' => 'clémentine']);
    }

    public function test_un_prenom_ne_se_prend_pas_deux_fois_meme_en_majuscules(): void
    {
        $this->groupe->membres()->create(['name' => 'Inès', 'magic_word' => 'sapin']);

        $this->post('/s/'.$this->groupe->share_token, ['name' => ' INÈS ', 'magic_word' => 'autre'])
            ->assertSessionHasErrors('name');
        $this->assertSame(1, $this->groupe->membres()->count());
    }

    public function test_on_se_retrouve_depuis_un_autre_appareil_avec_son_mot_magique(): void
    {
        $ines = $this->groupe->membres()->create(['name' => 'Inès', 'magic_word' => 'sapin']);
        $lien = '/s/'.$this->groupe->share_token;

        // La liste des prénoms s'affiche pour se retrouver.
        $this->get($lien)->assertSee('Retrouve-toi')->assertSee('Inès');

        $this->post($lien.'/retrouver', ['nom' => 'Inès', 'mot_magique' => 'Mauvais'])
            ->assertSessionHasErrors('mot_magique')
            ->assertCookieMissing('santa_'.$this->groupe->id);

        // Majuscules et espaces autour ne comptent pas.
        $this->post($lien.'/retrouver', ['nom' => 'Inès', 'mot_magique' => ' SAPIN '])
            ->assertRedirect($lien)
            ->assertCookie('santa_'.$this->groupe->id, $ines->token);
    }

    public function test_le_tirage_previent_par_mail_ceux_qui_ont_laisse_leur_adresse(): void
    {
        Mail::fake();
        [$a] = $this->participants(3);
        $a->forceFill(['email' => 'a@exemple.test'])->save();

        $this->actingAs($this->organisatrice)->post(route('santa.tirer', $this->groupe));

        Mail::assertSent(TirageFait::class, 1);
        Mail::assertSent(TirageFait::class, fn ($mail) => $mail->hasTo('a@exemple.test'));
    }

    public function test_on_ne_rejoint_pas_apres_le_tirage(): void
    {
        $this->participants(3);
        app(TirageSecretSanta::class)->tirer($this->groupe);

        $this->post('/s/'.$this->groupe->share_token, ['name' => 'Tard', 'magic_word' => 'trop tard'])->assertForbidden();
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
        $this->actingAs($intrus)->delete('/secret-santa/'.$this->groupe->id)->assertNotFound();
        $this->assertModelExists($this->groupe);
    }

    public function test_l_organisatrice_ajoute_renomme_et_retire_un_participant(): void
    {
        $base = '/secret-santa/'.$this->groupe->id.'/membres';
        $this->actingAs($this->organisatrice);

        $this->post($base, ['name' => 'Mamie'])->assertSessionHas('statut');
        $mamie = $this->groupe->membres()->firstOrFail();

        $this->post($base, ['name' => 'MAMIE'])->assertSessionHasErrors('participants');
        $this->assertSame(1, $this->groupe->membres()->count());

        $this->put($base.'/'.$mamie->id, ['name' => 'Mamie Jo'])->assertSessionHas('statut');
        $this->assertSame('Mamie Jo', $mamie->fresh()->name);

        $this->delete($base.'/'.$mamie->id)->assertSessionHas('statut');
        $this->assertModelMissing($mamie);
    }

    public function test_apres_le_tirage_on_renomme_mais_on_n_ajoute_ni_ne_retire(): void
    {
        [$a] = $this->participants(3);
        app(TirageSecretSanta::class)->tirer($this->groupe);
        $base = '/secret-santa/'.$this->groupe->id.'/membres';
        $this->actingAs($this->organisatrice);

        $this->post($base, ['name' => 'Tard'])->assertForbidden();
        $this->delete($base.'/'.$a->id)->assertForbidden();
        $this->put($base.'/'.$a->id, ['name' => 'Nouveau nom'])->assertSessionHas('statut');
        $this->assertSame('Nouveau nom', $a->fresh()->name);
    }

    public function test_une_place_ajoutee_par_l_organisatrice_est_reprise_par_la_personne(): void
    {
        $mamie = $this->groupe->membres()->create(['name' => 'Mamie']);

        $this->post('/s/'.$this->groupe->share_token, ['name' => 'mamie', 'magic_word' => 'tricot', 'wishes' => 'de la laine'])
            ->assertRedirect('/s/'.$this->groupe->share_token)
            ->assertCookie('santa_'.$this->groupe->id, $mamie->token);

        $this->assertSame(1, $this->groupe->membres()->count());
        $this->assertSame('de la laine', $mamie->fresh()->wishes);

        // Une fois reprise, la place a un mot magique : plus personne ne la reprend.
        $this->post('/s/'.$this->groupe->share_token, ['name' => 'Mamie', 'magic_word' => 'autre'])
            ->assertSessionHasErrors('name');
    }

    public function test_on_ne_touche_pas_au_participant_d_un_autre_groupe(): void
    {
        $autre = $this->organisatrice->santaGroupes()->create(['name' => 'Autre']);
        $etranger = $autre->membres()->create(['name' => 'Étranger']);

        $this->actingAs($this->organisatrice)
            ->delete('/secret-santa/'.$this->groupe->id.'/membres/'.$etranger->id)
            ->assertNotFound();
        $this->assertModelExists($etranger);
    }

    public function test_l_organisatrice_supprime_son_groupe_et_ses_participants(): void
    {
        [$a, $b] = $this->participants(4);
        $this->exclure($a, $b);
        app(TirageSecretSanta::class)->tirer($this->groupe);

        $this->actingAs($this->organisatrice)
            ->delete('/secret-santa/'.$this->groupe->id)
            ->assertRedirect('/secret-santa')
            ->assertSessionHas('statut');

        $this->assertModelMissing($this->groupe);
        $this->assertDatabaseCount('santa_members', 0);
        $this->assertDatabaseCount('santa_exclusions', 0);
        $this->get('/s/'.$this->groupe->share_token)->assertNotFound();
    }
}
