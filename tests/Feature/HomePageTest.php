<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_loads_only_local_frontend_assets(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Stage 1 ready')
            ->assertSee('/vendor/bootstrap/css/bootstrap.min.css', false)
            ->assertSee('/vendor/bootstrap/js/bootstrap.bundle.min.js', false)
            ->assertSee('/app.css', false)
            ->assertSee('/app.js', false)
            ->assertDontSee('https://cdn', false)
            ->assertDontSee('@vite', false);
    }
}
