<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class UiKitPageTest extends TestCase
{
    public function test_ui_kit_renders_production_components_and_local_assets(): void
    {
        $response = $this->get('/ui-kit');

        $response
            ->assertOk()
            ->assertSee('Базовая дизайн-система')
            ->assertSee('ui-shell', false)
            ->assertSee('ui-table', false)
            ->assertSee('sample-modal', false)
            ->assertSee('/vendor/lucide/lucide.min.js', false)
            ->assertDontSee('fonts.googleapis.com', false)
            ->assertDontSee('unpkg.com', false)
            ->assertDontSee('cdn.jsdelivr.net', false)
            ->assertDontSee('@vite', false);

        $this->assertFileExists(public_path('vendor/montserrat/Montserrat-VariableFont_wght.ttf'));
        $this->assertFileExists(public_path('vendor/montserrat/OFL.txt'));
        $this->assertFileExists(public_path('vendor/lucide/lucide.min.js'));
        $this->assertFileExists(public_path('vendor/lucide/LICENSE'));
        $this->assertStringContainsString(
            '/vendor/montserrat/Montserrat-VariableFont_wght.ttf',
            (string) file_get_contents(public_path('app.css')),
        );
    }

    public function test_ui_kit_is_not_available_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');

        $this->get('/ui-kit')->assertNotFound();
    }
}
