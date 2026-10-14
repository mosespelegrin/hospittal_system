<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_seo_metadata_and_handles_an_empty_database(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('og:image', false)
            ->assertSee('twitter:card', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('Our departments are being set up');
    }

    public function test_booking_page_shows_an_empty_state_without_departments(): void
    {
        $this->get('/consultation')->assertOk()->assertSee("Online booking isn't available yet", false);
    }

    public function test_security_headers_and_nonce_csp_are_sent(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringNotContainsString("'unsafe-inline'", $csp);
        preg_match("/script-src[^;]*'nonce-([^']+)'/", $csp, $m);
        $this->assertNotEmpty($m[1] ?? null);
        $response->assertSee('nonce="'.$m[1].'"', false);
    }

    public function test_robots_and_sitemap_keep_private_pages_out(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Disallow: /consultation/success/', $robots);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('patient.register'), false)
            ->assertDontSee('/admin')
            ->assertDontSee('success');
    }

    public function test_privacy_page_and_health_endpoint_work(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy');
        $this->get('/health')->assertOk()->assertJson(['status' => 'ok']);
        $this->get('/up')->assertOk();
    }

    public function test_unknown_pages_render_the_branded_404_without_leaking_details(): void
    {
        $this->get('/does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertDontSee('vendor/')
            ->assertSee('noindex', false);
    }

    public function test_breeze_routes_are_gone_and_login_redirects_to_the_admin_panel(): void
    {
        $this->get('/login')->assertRedirect('/admin/login');
        $this->get('/register')->assertNotFound();
        $this->get('/dashboard')->assertNotFound();
    }

    public function test_success_page_is_not_indexable_or_cacheable(): void
    {
        $department = Department::create(['name' => 'GM', 'status' => true]);
        Doctor::create([
            'department_id' => $department->id, 'name' => 'Dr. A', 'email' => 'a@x.test', 'phone' => '0700000000',
            'license_number' => 'L1', 'specialization' => 'GP', 'status' => true,
        ]);

        $location = $this->post(route('patient.store'), [
            'full_name' => 'Jane Doe', 'date_of_birth' => '1990-01-01', 'gender' => 'female', 'phone' => '0711223344',
            'department_id' => $department->id, 'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00', 'chief_complaint' => 'Headache', 'consent' => '1',
        ])->headers->get('Location');

        $this->get($location)
            ->assertOk()
            ->assertSee('noindex', false)
            ->assertHeader('Cache-Control');
    }
}
