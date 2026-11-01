<?php

namespace Tests\Feature;

use App\Livewire\JusteUnPrix;
use App\Livewire\PaquetMystere;
use App\Models\DailyItem;
use App\Models\DailyPlay;
use App\Services\TirageDuJour;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\DailyItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JeuxDuJourTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-05 10:00:00');
        $this->seed(DailyItemSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function objetDuJour(string $jeu): DailyItem
    {
        return app(TirageDuJour::class)->objet($jeu, CarbonImmutable::today());
    }

    public function test_le_meme_objet_pour_tout_le_monde_un_jour_donne(): void
    {
        $premier = app(TirageDuJour::class)->objet('juste-prix', CarbonImmutable::today());
        $second = app(TirageDuJour::class)->objet('juste-prix', CarbonImmutable::today());

        $this->assertSame($premier->id, $second->id);
    }

    public function test_le_juste_prix_s_arrete_a_six_essais(): void
    {
        for ($i = 0; $i < 8; $i++) {
            Livewire::test(JusteUnPrix::class, ['visiteur' => 'alice'])
                ->set('saisie', '0.01')
                ->call('proposer');
        }

        $partie = DailyPlay::where(['visitor' => 'alice', 'game' => 'juste-prix'])->first();
        $this->assertCount(6, $partie->guesses);
        $this->assertFalse($partie->won);
    }

    public function test_le_juste_prix_se_gagne_a_cinq_pour_cent_pres(): void
    {
        $prix = (float) $this->objetDuJour('juste-prix')->price;

        Livewire::test(JusteUnPrix::class, ['visiteur' => 'alice'])
            ->set('saisie', (string) round($prix * 1.04, 2))
            ->call('proposer');

        $this->assertTrue(DailyPlay::where(['visitor' => 'alice', 'game' => 'juste-prix'])->value('won'));
    }

    public function test_une_partie_par_jour_et_par_visiteur(): void
    {
        Livewire::test(JusteUnPrix::class, ['visiteur' => 'alice'])
            ->set('saisie', '1')->call('proposer');

        // Une autre personne a sa propre partie, le même jour.
        Livewire::test(JusteUnPrix::class, ['visiteur' => 'bob'])
            ->set('saisie', '1')->call('proposer');

        $this->assertSame(2, DailyPlay::where('game', 'juste-prix')->count());
    }

    public function test_le_paquet_mystere_perd_une_couche_par_mauvaise_reponse(): void
    {
        $mauvais = DailyItem::where('id', '!=', $this->objetDuJour('paquet-mystere')->id)->value('name');

        Livewire::test(PaquetMystere::class, ['visiteur' => 'alice'])
            ->set('choix', $mauvais)->call('deviner')
            ->set('choix', $mauvais)->call('deviner')
            ->assertSee('Il reste 3 couche(s) de papier.');
    }

    public function test_le_paquet_mystere_se_trouve_avec_le_nom_exact_de_la_liste(): void
    {
        $bonne = $this->objetDuJour('paquet-mystere');

        Livewire::test(PaquetMystere::class, ['visiteur' => 'alice'])
            ->set('choix', mb_strtolower($bonne->name))->call('deviner')
            ->assertSee('Trouvé avec 5 couche(s) restante(s) !');

        $this->assertTrue(DailyPlay::where(['visitor' => 'alice', 'game' => 'paquet-mystere'])->value('won'));
    }

    public function test_le_paquet_mystere_refuse_un_nom_hors_liste(): void
    {
        Livewire::test(PaquetMystere::class, ['visiteur' => 'alice'])
            ->set('choix', 'Licorne')->call('deviner')
            ->assertHasErrors(['choix']);

        $this->assertDatabaseCount('daily_plays', 0);
    }

    public function test_la_serie_compte_les_jours_consecutifs_termines(): void
    {
        $aujourdhui = CarbonImmutable::today();

        foreach ([1, 2] as $joursAvant) {
            DailyPlay::create([
                'visitor' => 'alice', 'game' => 'juste-prix',
                'played_on' => $aujourdhui->subDays($joursAvant),
                'guesses' => [1], 'won' => true,
            ]);
        }

        // Hier et avant-hier comptent, même si aujourd'hui n'est pas encore joué.
        $this->assertSame(2, DailyPlay::serie('alice', 'juste-prix', 6, $aujourdhui));
    }

    public function test_la_page_jeux_donne_un_cookie_visiteur(): void
    {
        $this->get('/jeux')
            ->assertOk()
            ->assertCookie('jeux_visiteur');
    }
}
