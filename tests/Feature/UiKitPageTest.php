<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
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
            ->assertSee('1–20 из 124')
            ->assertSee('61–80 из 124')
            ->assertSee('data-ui-password-toggle', false)
            ->assertSee('readonly', false)
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

    public function test_pagination_renders_real_first_and_middle_page_states(): void
    {
        $firstPage = new LengthAwarePaginator(
            range(1, 10),
            95,
            10,
            1,
            ['path' => '/people'],
        );
        $middlePage = new LengthAwarePaginator(
            range(41, 50),
            95,
            10,
            5,
            ['path' => '/people'],
        );

        $firstPageHtml = Blade::render(
            '<x-ui.pagination :paginator="$paginator" />',
            ['paginator' => $firstPage],
        );
        $middlePageHtml = Blade::render(
            '<x-ui.pagination :paginator="$paginator" />',
            ['paginator' => $middlePage],
        );

        $this->assertStringContainsString('1–10 из 95', $firstPageHtml);
        $this->assertStringContainsString('aria-disabled="true" aria-label="Предыдущая страница"', $firstPageHtml);
        $this->assertStringContainsString('href="/people?page=2"', $firstPageHtml);
        $this->assertStringContainsString('aria-current="page">1</span>', $firstPageHtml);

        $this->assertStringContainsString('41–50 из 95', $middlePageHtml);
        $this->assertStringContainsString('href="/people?page=4" rel="prev"', $middlePageHtml);
        $this->assertStringContainsString('href="/people?page=6" rel="next"', $middlePageHtml);
        $this->assertStringContainsString('aria-current="page">5</span>', $middlePageHtml);
        $this->assertStringContainsString('ui-pagination__ellipsis', $middlePageHtml);
    }
}
