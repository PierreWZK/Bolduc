<?php

namespace Tests\Feature;

use App\Models\Invite;
use App\Models\Liste;
use App\Services\AssistantIdees;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantIdeesTest extends TestCase
{
    use RefreshDatabase;

    private const QUESTION = 'Une idée pour ma sœur qui aime lire ?';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'cle-de-test', 'services.gemini.quota_jour' => 200]);
    }

    // Réponse JSON de Gemini, dans le format qu'il renvoie réellement.
    private function gemini(array $ia): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($ia)]]]]],
            ]),
        ]);
    }

    public function test_une_demande_d_idee_cadeau_recoit_la_reponse_de_gemini(): void
    {
        $this->gemini(['hors_sujet' => false, 'reponse' => 'Un carnet en cuir, avec un marque-page.']);

        $this->post('/idees/assistant', ['question' => self::QUESTION])
            ->assertSessionHas('assistant', 'Un carnet en cuir, avec un marque-page.');
    }

    public function test_une_demande_hors_sujet_recoit_le_refus_fixe(): void
    {
        $this->gemini(['hors_sujet' => true, 'reponse' => 'Voici une recette de tarte.']);

        $this->post('/idees/assistant', ['question' => 'Donne-moi une recette de tarte'])
            ->assertSessionHas('assistant', AssistantIdees::REFUS);
    }

    public function test_une_reponse_illisible_donne_le_refus_sans_contenu_inattendu(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'pas du json']]]]]])]);

        $this->post('/idees/assistant', ['question' => self::QUESTION])
            ->assertSessionHas('assistant', 'L\'assistant est indisponible pour le moment. Réessaie plus tard.');
    }

    public function test_sans_cle_gemini_on_n_appelle_personne(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->post('/idees/assistant', ['question' => self::QUESTION])
            ->assertSessionHas('assistant', 'L\'assistant est indisponible pour le moment. Réessaie plus tard.');
        Http::assertNothingSent();
    }

    public function test_le_plafond_journalier_coupe_les_appels(): void
    {
        config(['services.gemini.quota_jour' => 1]);
        Cache::put('gemini-appels:'.now()->toDateString(), 1, now()->endOfDay());
        Http::fake();

        $this->post('/idees/assistant', ['question' => self::QUESTION])
            ->assertSessionHas('assistant', 'L\'assistant est indisponible pour le moment. Réessaie plus tard.');
        Http::assertNothingSent();
    }

    public function test_le_champ_piege_rempli_ignore_la_requete(): void
    {
        Http::fake();

        $this->post('/idees/assistant', ['question' => self::QUESTION, 'site_web' => 'http://spam.test'])
            ->assertRedirect()
            ->assertSessionMissing('assistant');
        Http::assertNothingSent();
    }

    public function test_l_assistant_est_limite_par_debit(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/idees/assistant', ['question' => self::QUESTION]);
        }

        $this->post('/idees/assistant', ['question' => self::QUESTION])->assertStatus(429);
    }

    public function test_le_champ_piege_du_prenom_invite_ignore_l_inscription(): void
    {
        $liste = Liste::create(['title' => 'Noël chez Camille', 'event_type' => 'noel']);

        $this->post('/l/'.$liste->share_token.'/prenom', [
            'name' => 'Robot', 'magic_word' => 'abracadabra', 'site_web' => 'http://spam.test',
        ]);

        $this->assertSame(0, Invite::count());
    }
}
