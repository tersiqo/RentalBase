<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GlobalErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        Route::get('/_test_abort_403', function () {
            abort(403, 'Akses khusus administrator. Anda tidak memiliki izin melihat halaman ini.');
        });
    }

    public function test_404_not_found_view_renders_clean_error_layout_and_headline(): void
    {
        $response = $this->get('/non-existent-url-path-test-404');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('Kembali ke Beranda');
    }

    public function test_404_view_prevents_technical_leaks_and_includes_safe_navigation_cta(): void
    {
        $response = $this->get('/another-invalid-route-404');

        $response->assertStatus(404);
        $response->assertDontSee('SQLSTATE');
        $response->assertDontSee('vendor/');
        $response->assertDontSee('Stack trace');
        $response->assertSee('href="/"', false);
    }

    public function test_403_forbidden_view_renders_when_access_is_unauthorized(): void
    {
        $response = $this->get('/_test_abort_403');

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Akses Ditolak');
        $response->assertSee('Akses khusus administrator');
    }

    public function test_403_view_renders_role_authorization_guidance_and_dashboard_cta(): void
    {
        $response = $this->get('/_test_abort_403');

        $response->assertStatus(403);
        $response->assertSee('Kembali ke Dashboard');
        $response->assertSee('RentalBase');
    }

    public function test_error_templates_are_fully_responsive_with_mobile_viewport_and_tailwind_styling(): void
    {
        $view403 = view('errors.403', ['exception' => new \Exception('Akses ditolak')])->render();
        $view404 = view('errors.404')->render();

        // 1. Tag meta viewport untuk responsive mobile
        $this->assertStringContainsString('name="viewport"', $view403);
        $this->assertStringContainsString('width=device-width, initial-scale=1.0', $view403);
        $this->assertStringContainsString('name="viewport"', $view404);
        $this->assertStringContainsString('width=device-width, initial-scale=1.0', $view404);

        // 2. Utility classes Tailwind CSS
        $this->assertStringContainsString('max-w-lg', $view403);
        $this->assertStringContainsString('rounded-3xl', $view403);
        $this->assertStringContainsString('bg-slate-50', $view403);
        $this->assertStringContainsString('max-w-lg', $view404);
        $this->assertStringContainsString('rounded-3xl', $view404);
        $this->assertStringContainsString('bg-slate-50', $view404);
    }
}
