<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_vitrine_s_affiche(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('qui garde', false)
            ->assertSee('Secret Santa')
            ->assertSee('Paquet mystère')
            ->assertSee('https://projectp.fr/', false);
    }

    public function test_les_pages_a_venir_repondent_sans_etre_indexees(): void
    {
        foreach (['/inscription', '/idees', '/secret-santa', '/jeux'] as $page) {
            $this->get($page)->assertOk()->assertSee('noindex', false);
        }
    }
}
