<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_le_sitemap_ne_liste_que_les_pages_publiques(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('accueil'), false)
            ->assertSee(route('idees'), false)
            ->assertDontSee('mes-listes', false)
            ->assertDontSee('secret-santa', false);
    }

    public function test_robots_txt_ferme_les_pages_privees_et_annonce_le_plan(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /mes-listes', $robots);
        $this->assertStringContainsString('Disallow: /l/', $robots);
        $this->assertStringContainsString('Disallow: /s/', $robots);
        $this->assertStringContainsString('Sitemap: https://bolduc.projectp.fr/sitemap.xml', $robots);
    }

    public function test_les_pages_publiques_portent_les_balises_de_partage(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:image"', false)
            ->assertSee('content="1200"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }

    public function test_les_pages_privees_ne_sont_pas_indexees(): void
    {
        $this->get('/connexion')
            ->assertOk()
            ->assertSee('name="robots" content="noindex"', false);
    }
}
