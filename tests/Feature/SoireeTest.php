<?php

namespace Tests\Feature;

use App\Livewire\Soiree;
use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use App\Models\SoireeIndice;
use App\Models\SoireeManche;
use App\Models\User;
use App\Services\TirageSecretSanta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SoireeTest extends TestCase
{
    use RefreshDatabase;

    private SantaGroupe $groupe;

    /** @var SantaMembre[] */
    private array $membres;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupe = User::factory()->create()->santaGroupes()->create(['name' => 'Noël en famille']);
        $this->membres = collect(range(1, 4))
            ->map(fn ($i) => $this->groupe->membres()->create(['name' => "Personne $i"]))
            ->all();

        app(TirageSecretSanta::class)->tirer($this->groupe);
    }

    // Recharge un participant : après le tirage, sa cible et son donneur sont à jour.
    private function membre(int $position): SantaMembre
    {
        return $this->membres[$position]->fresh();
    }

    private function ecrireIndices(SantaMembre $donneur): void
    {
        foreach ([...SoireeIndice::QUI, ...SoireeIndice::CADEAU] as $kind) {
            $donneur->indices()->create(['kind' => $kind, 'value' => "$kind de {$donneur->name}"]);
        }
    }

    private function jouer(SantaMembre $joueur): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(Soiree::class, ['groupe' => $this->groupe, 'membre' => $joueur]);
    }

    private function points(SantaMembre $joueur, string $jeu): int
    {
        return (int) SoireeManche::where(['member_id' => $joueur->id, 'game' => $jeu])->value('points');
    }

    public function test_le_donneur_ecrit_ses_six_indices(): void
    {
        $donneur = $this->membre(0);

        $this->withCookie('santa_'.$this->groupe->id, $donneur->token)
            ->post(route('santa.indices.sauver', $this->groupe->share_token), [
                'emoji' => '🎁', 'surnom' => 'Le Grand', 'phrase' => 'Il rit fort.',
                'cadeau1' => 'Ça se boit', 'cadeau2' => 'Chaud', 'cadeau3' => 'Pour le matin',
            ])
            ->assertRedirect(route('santa.soiree', $this->groupe->share_token));

        $this->assertCount(6, $donneur->indices()->get());
        $this->assertSame('🎁', $donneur->indices()->where('kind', 'emoji')->value('value'));
    }

    public function test_sans_cookie_on_ne_peut_ni_ecrire_ni_jouer(): void
    {
        $this->post(route('santa.indices.sauver', $this->groupe->share_token), [])->assertForbidden();
        $this->get(route('santa.soiree', $this->groupe->share_token))->assertForbidden();
    }

    public function test_les_indices_qui_se_revelent_dans_l_ordre(): void
    {
        $joueur = $this->membre(0);
        $this->ecrireIndices($joueur->donneur());

        $this->jouer($joueur)
            ->call('indiceSuivantQui')
            ->call('indiceSuivantQui')
            ->call('indiceSuivantQui')
            ->call('indiceSuivantQui')
            ->assertSee('emoji de')
            ->assertSee('phrase de');

        // Au quatrième clic on reste à trois indices révélés.
        $this->assertSame(3, SoireeManche::where(['member_id' => $joueur->id, 'game' => 'qui'])->value('revealed'));
    }

    public function test_deviner_son_donneur_sans_indice_rapporte_3_points(): void
    {
        $joueur = $this->membre(0);
        $this->ecrireIndices($joueur->donneur());

        $this->jouer($joueur)->call('devinerQui', $joueur->donneur()->id);

        $this->assertSame(3, $this->points($joueur, 'qui'));
    }

    public function test_chaque_indice_de_plus_fait_perdre_un_point(): void
    {
        $joueur = $this->membre(0);
        $this->ecrireIndices($joueur->donneur());

        $this->jouer($joueur)
            ->call('indiceSuivantQui')
            ->call('devinerQui', $joueur->donneur()->id);
        $this->assertSame(2, $this->points($joueur, 'qui'));

        $autre = $this->membre(1);
        $this->ecrireIndices($autre->donneur());
        $this->jouer($autre)
            ->call('indiceSuivantQui')
            ->call('indiceSuivantQui')
            ->call('devinerQui', $autre->donneur()->id);
        $this->assertSame(1, $this->points($autre, 'qui'));
    }

    public function test_une_mauvaise_reponse_ne_rapporte_rien_et_laisse_rejouer(): void
    {
        $joueur = $this->membre(0);
        $this->ecrireIndices($joueur->donneur());
        $mauvais = collect($this->membres)->first(fn ($m) => $m->id !== $joueur->id && $m->id !== $joueur->donneur()->id);

        $this->jouer($joueur)
            ->call('devinerQui', $mauvais->id)
            ->assertSet('message', 'Raté, ce n\'est pas la bonne personne. Essaie encore.')
            ->call('devinerQui', $joueur->donneur()->id);

        $this->assertSame(3, $this->points($joueur, 'qui'));
    }

    public function test_seul_le_donneur_lance_le_jeu_du_cadeau(): void
    {
        $donneur = $this->membre(0);
        $cible = $donneur->cible;
        $autre = collect($this->membres)->first(fn ($m) => $m->id !== $donneur->id && $m->id !== $cible->id);

        // Un autre participant ne peut pas lancer le jeu pour la cible de quelqu'un d'autre.
        $this->jouer($autre->fresh())->call('lancerCadeau', $cible->id);
        $this->assertDatabaseMissing('soiree_manches', ['member_id' => $cible->id, 'game' => 'cadeau']);

        $this->jouer($donneur)->call('lancerCadeau', $cible->id);
        $this->assertDatabaseHas('soiree_manches', ['member_id' => $cible->id, 'game' => 'cadeau']);
    }

    public function test_la_devinette_du_cadeau_validee_par_le_donneur_rapporte_des_points(): void
    {
        $donneur = $this->membre(0);
        $cible = $donneur->cible;
        $this->ecrireIndices($donneur);
        $this->jouer($donneur)->call('lancerCadeau', $cible->id);

        $this->jouer($cible)
            ->call('indiceSuivantCadeau')
            ->set('devinetteCadeau', 'un thé')
            ->call('devinerCadeau');

        $this->assertSame('un thé', SoireeManche::where(['member_id' => $cible->id, 'game' => 'cadeau'])->value('guess'));

        $this->jouer($donneur)->call('valider', true);

        // Deux indices révélés : deux points.
        $this->assertSame(2, $this->points($cible, 'cadeau'));
        $this->assertTrue((bool) SoireeManche::where(['member_id' => $cible->id, 'game' => 'cadeau'])->value('solved'));
    }

    public function test_un_refus_efface_la_devinette_sans_points(): void
    {
        $donneur = $this->membre(0);
        $cible = $donneur->cible;
        $this->ecrireIndices($donneur);
        $this->jouer($donneur)->call('lancerCadeau', $cible->id);
        $this->jouer($cible)->set('devinetteCadeau', 'un livre')->call('devinerCadeau');

        $this->jouer($donneur)->call('valider', false);

        $this->assertNull(SoireeManche::where(['member_id' => $cible->id, 'game' => 'cadeau'])->value('guess'));
        $this->assertSame(0, $this->points($cible, 'cadeau'));
    }
}
