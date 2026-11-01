<?php

namespace Tests\Feature;

use App\Livewire\ListeInvite;
use App\Models\Cadeau;
use App\Models\Contribution;
use App\Models\Invite;
use App\Models\Liste;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvitesTest extends TestCase
{
    use RefreshDatabase;

    private User $camille;

    private Liste $liste;

    private Cadeau $plaid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->camille = User::factory()->create(['name' => 'Camille']);
        $this->liste = Liste::create([
            'title' => 'Noël chez Camille',
            'event_type' => 'noel',
            'event_date' => now()->addDays(20)->toDateString(),
        ]);
        $this->liste->proprietaires()->attach($this->camille);
        $this->plaid = $this->liste->cadeaux()->create(['title' => 'Plaid en laine', 'price' => 59, 'quantity' => 1]);
    }

    private function invite(string $nom): Invite
    {
        return $this->liste->invites()->create(['name' => $nom]);
    }

    private function vue(Invite $invite)
    {
        return Livewire::test(ListeInvite::class, ['liste' => $this->liste, 'invite' => $invite]);
    }

    public function test_un_lien_inconnu_repond_404(): void
    {
        $this->get('/l/inconnu')->assertNotFound();
    }

    public function test_un_invite_donne_son_prenom_puis_voit_la_liste(): void
    {
        $lien = '/l/'.$this->liste->share_token;
        $cookie = 'invite_'.$this->liste->id;

        $this->get($lien)->assertOk()->assertSee('Ton prénom')->assertDontSee('Plaid en laine');

        $reponse = $this->post($lien.'/prenom', ['name' => 'Inès'])->assertRedirect($lien);
        $invite = Invite::firstOrFail();
        $reponse->assertCookie($cookie, $invite->token);

        $this->withCookie($cookie, $invite->token)->get($lien)
            ->assertSee('Bonjour Inès')
            ->assertSee('Plaid en laine');
    }

    public function test_le_proprietaire_qui_ouvre_le_lien_reste_sur_sa_vue(): void
    {
        $this->actingAs($this->camille)
            ->get('/l/'.$this->liste->share_token)
            ->assertRedirect('/mes-listes/'.$this->liste->id);
    }

    public function test_deux_invites_ne_reservent_pas_le_meme_cadeau(): void
    {
        $this->vue($this->invite('Inès'))->call('reserver', $this->plaid->id);
        $this->vue($this->invite('Hugo'))
            ->call('reserver', $this->plaid->id)
            ->assertSet('message', 'Trop tard, quelqu\'un vient de le réserver.');

        $this->assertSame(1, Reservation::count());
    }

    public function test_un_cadeau_en_quantite_se_reserve_a_l_unite(): void
    {
        $verres = $this->liste->cadeaux()->create(['title' => 'Verres à vin', 'quantity' => 3]);

        $this->vue($ines = $this->invite('Inès'))
            ->call('reserver', $verres->id)
            ->call('reserver', $verres->id);
        $this->vue($this->invite('Hugo'))->call('reserver', $verres->id);
        $this->vue($this->invite('Tom'))
            ->call('reserver', $verres->id)
            ->assertSet('message', 'Trop tard, quelqu\'un vient de le réserver.');

        $this->assertSame(3, (int) $verres->reservations()->sum('quantity'));
        $this->assertSame(2, $verres->reservations()->where('guest_id', $ines->id)->value('quantity'));
    }

    public function test_les_parts_ne_depassent_jamais_le_prix(): void
    {
        $casque = $this->liste->cadeaux()->create(['title' => 'Casque audio', 'price' => 120, 'quantity' => 1, 'shared' => true]);

        $this->vue($this->invite('Inès'))
            ->set("parts.{$casque->id}", '80')
            ->call('participer', $casque->id)
            ->assertHasNoErrors();

        $hugo = $this->vue($this->invite('Hugo'));
        $hugo->set("parts.{$casque->id}", '50')->call('participer', $casque->id)->assertHasErrors("parts.{$casque->id}");
        $hugo->set("parts.{$casque->id}", '40')->call('participer', $casque->id)->assertHasNoErrors();

        $this->assertSame('120.00', number_format((float) Contribution::sum('amount'), 2, '.', ''));
        // Un cadeau à plusieurs ne se réserve pas d'un bloc.
        $this->vue($this->invite('Tom'))->call('reserver', $casque->id);
        $this->assertSame(0, Reservation::count());
    }

    public function test_annuler_libere_le_cadeau(): void
    {
        $vue = $this->vue($this->invite('Inès'));
        $vue->call('reserver', $this->plaid->id)->call('annuler', $this->plaid->id);

        $this->assertSame(0, Reservation::count());
        $this->vue($this->invite('Hugo'))->call('reserver', $this->plaid->id);
        $this->assertSame(1, Reservation::count());
    }

    public function test_apres_la_date_la_liste_est_fermee(): void
    {
        $this->liste->update(['event_date' => now()->subDay()->toDateString()]);

        $this->vue($this->invite('Inès'))
            ->call('reserver', $this->plaid->id)
            ->assertSet('message', 'La date est passée : la liste est fermée.');

        $this->assertSame(0, Reservation::count());
    }

    public function test_un_invite_ne_touche_pas_au_cadeau_d_une_autre_liste(): void
    {
        $autre = Liste::create(['title' => 'Autre', 'event_type' => 'autre']);
        $cadeau = $autre->cadeaux()->create(['title' => 'Vase', 'quantity' => 1]);

        $this->vue($this->invite('Inès'))->call('reserver', $cadeau->id)->assertStatus(404);
        $this->assertSame(0, Reservation::count());
    }

    public function test_le_proprietaire_ne_voit_aucune_reservation(): void
    {
        $ines = $this->invite('Inès');
        $this->plaid->reservations()->create(['guest_id' => $ines->id, 'quantity' => 1]);
        $casque = $this->liste->cadeaux()->create(['title' => 'Casque', 'price' => 120, 'quantity' => 1, 'shared' => true]);
        $casque->contributions()->create(['guest_id' => $ines->id, 'amount' => 80]);

        $this->actingAs($this->camille)
            ->get('/mes-listes/'.$this->liste->id)
            ->assertOk()
            ->assertSee('Plaid en laine')
            ->assertDontSee('Inès')
            ->assertDontSee('Réservé')
            ->assertDontSee('80,00');
    }
}
