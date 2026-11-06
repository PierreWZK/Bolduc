<?php

namespace Tests\Feature;

use App\Livewire\SoireeOrganisatrice;
use App\Models\SantaGroupe;
use App\Models\SantaMembre;
use App\Models\SoireeIndice;
use App\Models\User;
use App\Services\TirageSecretSanta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SoireeTest extends TestCase
{
    use RefreshDatabase;

    private User $organisatrice;

    private SantaGroupe $groupe;

    /** @var SantaMembre[] */
    private array $membres;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisatrice = User::factory()->create();
        $this->groupe = $this->organisatrice->santaGroupes()->create(['name' => 'Noël en famille']);
        $this->membres = collect(range(1, 4))
            ->map(fn ($i) => $this->groupe->membres()->create(['name' => "Personne $i"]))
            ->all();

        app(TirageSecretSanta::class)->tirer($this->groupe);
    }

    private function ecrireIndices(SantaMembre $membre): void
    {
        foreach ([...SoireeIndice::QUI, ...SoireeIndice::CADEAU] as $kind) {
            $membre->indices()->create(['kind' => $kind, 'value' => "$kind de {$membre->name}"]);
        }
    }

    private function jeu(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::actingAs($this->organisatrice)->test(SoireeOrganisatrice::class, ['groupe' => $this->groupe]);
    }

    public function test_l_organisatrice_ouvre_le_mode_soiree_et_voit_les_indices(): void
    {
        $this->ecrireIndices($this->membres[0]);

        $this->actingAs($this->organisatrice)
            ->get(route('santa.soiree', $this->groupe))
            ->assertOk()
            ->assertSee('Mode soirée')
            ->assertSee('indice 1 sur 3')
            ->assertSee('emoji de Personne 1');
    }

    public function test_un_autre_compte_n_a_pas_acces_au_mode_soiree(): void
    {
        $this->ecrireIndices($this->membres[0]);

        $this->actingAs(User::factory()->create())
            ->get(route('santa.soiree', $this->groupe))
            ->assertNotFound();
    }

    public function test_un_participant_sans_compte_est_renvoye_a_la_connexion(): void
    {
        $this->ecrireIndices($this->membres[0]);

        $this->withCookie('santa_'.$this->groupe->id, $this->membres[0]->token)
            ->get(route('santa.soiree', $this->groupe))
            ->assertRedirect(route('login'));
    }

    public function test_le_bouton_jouer_disparait_de_la_page_des_participants(): void
    {
        $this->withCookie('santa_'.$this->groupe->id, $this->membres[0]->token)
            ->get('/s/'.$this->groupe->share_token)
            ->assertOk()
            ->assertDontSee('Jouer')
            ->assertDontSee(route('santa.soiree', $this->groupe));

        // L'ancienne route participant n'existe plus.
        $this->get('/s/'.$this->groupe->share_token.'/soiree')->assertNotFound();
    }

    public function test_le_bouton_mode_soiree_apparait_sur_la_page_de_l_organisatrice_apres_le_tirage(): void
    {
        $this->actingAs($this->organisatrice)
            ->get('/secret-santa/'.$this->groupe->id)
            ->assertSee('Lancer le mode soirée');
    }

    public function test_sans_indices_le_jeu_attend_les_participants(): void
    {
        $this->jeu()->assertSet('membreId', null)->assertSee('Ils se remplissent depuis le lien');
    }

    public function test_les_indices_se_revelent_un_par_un(): void
    {
        $this->ecrireIndices($this->membres[0]);

        $this->jeu()
            ->assertSet('revele', 1)
            ->call('indiceSuivant')
            ->call('indiceSuivant')
            ->call('indiceSuivant')
            ->call('indiceSuivant')
            ->assertSet('revele', 3)
            ->assertSee('phrase de Personne 1');
    }

    public function test_une_mauvaise_reponse_ne_passe_pas_au_cadeau_puis_la_bonne_si(): void
    {
        $cible = $this->membres[0];
        $this->ecrireIndices($cible);
        $autre = $this->membres[1];

        $this->jeu()
            ->call('passerAuCadeau')
            ->assertSet('jeu', 1)
            ->call('deviner', $autre->id)
            ->assertSet('message', 'Raté, essaie encore.')
            ->assertSet('trouve', false)
            ->call('deviner', $cible->id)
            ->assertSet('trouve', true)
            ->call('passerAuCadeau')
            ->assertSet('jeu', 2)
            ->assertSet('revele', 1)
            ->assertSee('Quel cadeau')
            ->call('indiceSuivant')
            ->call('indiceSuivant')
            ->assertSee('cadeau2 de Personne 1');
    }

    public function test_une_autre_personne_tire_a_nouveau_au_hasard_parmi_celles_qui_ont_des_indices(): void
    {
        $this->ecrireIndices($this->membres[2]);

        $this->jeu()
            ->call('deviner', $this->membres[0]->id)
            ->call('autre')
            ->assertSet('membreId', $this->membres[2]->id)
            ->assertSet('revele', 1)
            ->assertSet('message', null);
    }

    public function test_l_organisatrice_sauve_les_indices_et_le_participant_revient_sur_sa_page(): void
    {
        $this->withCookie('santa_'.$this->groupe->id, $this->membres[0]->token)
            ->post(route('santa.indices.sauver', $this->groupe->share_token), [
                'emoji' => '🎁', 'surnom' => 'Le Grand', 'phrase' => 'Il rit fort.',
                'cadeau1' => 'Ça se boit', 'cadeau2' => 'Chaud', 'cadeau3' => 'Pour le matin',
            ])
            ->assertRedirect(route('santa.invite', $this->groupe->share_token));

        $this->assertCount(6, $this->membres[0]->indices()->get());
    }
}
