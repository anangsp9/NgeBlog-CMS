<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function headerProvider(): array
    {
        return [
            'X-Content-Type-Options' => ['X-Content-Type-Options', 'nosniff'],
            'X-Frame-Options' => ['X-Frame-Options', 'SAMEORIGIN'],
            'Referrer-Policy' => ['Referrer-Policy', 'strict-origin-when-cross-origin'],
            'Cross-Origin-Opener-Policy' => ['Cross-Origin-Opener-Policy', 'same-origin'],
            'Permissions-Policy' => ['Permissions-Policy', 'camera=(), microphone=(), geolocation=()'],
        ];
    }

    #[DataProvider('headerProvider')]
    public function test_header_keamanan_dikirim(string $header, string $expected): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader($header, $expected);
    }

    public function test_strict_transport_security_tidak_dikirim_untuk_koneksi_http(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_berkas_sensitif_pada_apache_ditolak(): void
    {
        $this->assertFileExists(base_path('.docker/php/apache-app.conf'));

        $conf = file_get_contents(base_path('.docker/php/apache-app.conf'));

        $this->assertStringContainsString('Require all denied', $conf);
        $this->assertStringContainsString('Options -Indexes', $conf);
    }

    public function test_token_csrf_disertakan_pada_setiap_halaman_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_endpoint_kesehatan_tersedia(): void
    {
        $this->get('/up')->assertOk();
    }
}
