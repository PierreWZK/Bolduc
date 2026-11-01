<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ComptesTest extends TestCase
{
    use RefreshDatabase;

    public function test_inscription_cree_le_compte_et_connecte(): void
    {
        $this->post('/inscription', [
            'name' => 'Camille',
            'email' => 'Camille@Exemple.fr',
            'password' => 'unpaquet123',
            'password_confirmation' => 'unpaquet123',
        ])->assertRedirect('/mes-listes');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'camille@exemple.fr']);
        $this->get('/mes-listes')->assertOk()->assertSee('Bonjour Camille');
    }

    public function test_inscription_refuse_un_email_deja_pris(): void
    {
        User::factory()->create(['email' => 'camille@exemple.fr']);

        $this->post('/inscription', [
            'name' => 'Camille',
            'email' => 'CAMILLE@exemple.fr',
            'password' => 'unpaquet123',
            'password_confirmation' => 'unpaquet123',
        ])->assertSessionHasErrors(['email' => 'Un compte existe déjà avec cet e-mail. Connecte-toi plutôt.']);

        $this->assertGuest();
    }

    public function test_les_erreurs_sont_en_francais(): void
    {
        $this->post('/inscription', ['password' => 'court', 'password_confirmation' => 'autre'])
            ->assertSessionHasErrors([
                'name' => 'Ce champ est obligatoire.',
                'password' => 'Les deux saisies ne correspondent pas.',
            ]);
    }

    public function test_connexion_et_deconnexion(): void
    {
        $user = User::factory()->create(['email' => 'hugo@exemple.fr', 'password' => 'unpaquet123']);

        $this->post('/connexion', ['email' => 'hugo@exemple.fr', 'password' => 'mauvais'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/connexion', ['email' => 'Hugo@exemple.fr', 'password' => 'unpaquet123'])
            ->assertRedirect('/mes-listes');
        $this->assertAuthenticatedAs($user);

        $this->post('/deconnexion')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_mes_listes_demande_d_etre_connecte(): void
    {
        $this->get('/mes-listes')->assertRedirect('/connexion');
    }

    public function test_mot_de_passe_oublie_puis_reinitialise(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'ines@exemple.fr']);

        $this->post('/mot-de-passe-oublie', ['email' => 'ines@exemple.fr'])->assertSessionHas('statut');
        // Un e-mail inconnu reçoit la même réponse, sans mail envoyé.
        $this->post('/mot-de-passe-oublie', ['email' => 'inconnu@exemple.fr'])->assertSessionHas('statut');

        $jeton = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use (&$jeton) {
            $jeton = $n->token;

            return true;
        });
        Notification::assertSentTimes(ResetPassword::class, 1);

        $this->post('/reinitialiser', [
            'token' => $jeton,
            'email' => 'ines@exemple.fr',
            'password' => 'nouveau-paquet',
            'password_confirmation' => 'nouveau-paquet',
        ])->assertRedirect('/connexion');

        $this->assertTrue(Hash::check('nouveau-paquet', $user->fresh()->password));
    }
}
