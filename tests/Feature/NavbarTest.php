<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test ini lahir dari bug nyata: menu navbar dibungkus kelas `invisible` tetapi
 * tanpa pasangan `lg:visible`, sehingga di layar lebar seluruh navigasi dan link
 * login tetap visibility:hidden. Markup lengkap, tes kehadiran teks tetap lulus,
 * tapi halaman tampak kosong. Maka yang diperiksa di sini adalah pasangan kelasnya.
 */
class NavbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_publik_menampilkan_navigasi_dan_login(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $response->assertSee(route('articles.index'));
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
    }

    public function test_menu_punya_override_keterlihatan_untuk_desktop(): void
    {
        $classes = $this->mainMenuClasses();

        $this->assertStringContainsString('invisible', $classes, 'Menu harus tersembunyi di layar sempit.');
        $this->assertStringContainsString(
            'lg:visible',
            $classes,
            'Menu disembunyikan dengan invisible tetapi tidak punya lg:visible: link login dan navigasi akan tetap tidak terlihat di desktop.'
        );
    }

    public function test_kelas_keterlihatan_menu_ter_generate_untuk_desktop(): void
    {
        $manifest = public_path('build/manifest.json');

        if (! file_exists($manifest)) {
            $this->markTestSkipped('Manifest build Vite tidak ditemukan; jalankan npm run build dulu.');
        }

        $manifestData = json_decode(file_get_contents($manifest), true);
        $entry = $manifestData['resources/css/app.css'] ?? null;
        $cssPath = public_path('build/'.($entry['file'] ?? ''));

        if (! file_exists($cssPath)) {
            $this->markTestSkipped('Berkas CSS hasil build tidak ditemukan; jalankan npm run build dulu.');
        }

        $css = file_get_contents($cssPath);

        $this->assertStringContainsString(
            '.invisible{visibility:hidden}',
            $css,
            'Aturan yang menyembunyikan menu tidak ada - periksa kembali kelas layout.'
        );

        $override = '.lg\:visible{visibility:visible}';
        $position = strpos($css, $override);

        $this->assertNotFalse(
            $position,
            'Override .lg\:visible tidak ada di hasil build: menu tetap tersembunyi di desktop.'
        );

        // Cari blok @media lg yang membungkus rule itu. Harus berada sebelum posisi
        // rule tanpa ada '}' di antaranya; sekali bertemu '}', kita sudah melewati
        // tutup blok dan rule ternyata berada di luar media query.
        $prefix = substr($css, 0, $position);
        $mediaStart = strrpos($prefix, '@media(min-width:64rem){');

        $this->assertNotFalse($mediaStart, 'Rule lg:visible tidak berada dalam media query desktop.');

        $this->assertStringNotContainsString(
            '}',
            substr($css, $mediaStart, $position - $mediaStart),
            'Rule lg:visible berada di luar blok @media desktop sehingga tidak aktif di layar lebar.'
        );
    }

    public function test_navbar_login_untuk_pengunjung_dan_tanpa_login_untuk_user(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'));

        $visitor = User::factory()->create(['role' => 'visitor']);

        $this->actingAs($visitor)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(route('logout'));

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(route('admin.dashboard'));
    }

    public function test_sidebar_admin_menampilkan_seluruh_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $response->assertSee(route('admin.articles.index'));
        $response->assertSee(route('admin.categories.index'));
        $response->assertSee(route('admin.comments.index'));
        $response->assertSee(route('admin.users.index'));

        // Sidebar admin memakai transform, bukan visibility: pastikan tidak ada
        // kelas invisible yang lolos ke sana tanpa pasangannya.
        preg_match('/<aside[^>]*class="([^"]*)"/', $response->getContent(), $matches);

        $this->assertNotEmpty($matches, 'Tidak menemukan sidebar admin.');
        $this->assertStringNotContainsString(
            'invisible',
            $matches[1],
            'Sidebar admin memakai transform untuk masuk/keluar; kelas invisible akan membuatnya hilang sama sekali di desktop.'
        );
    }

    public function test_toggle_menu_publik_punya_peer_sejajar(): void
    {
        $this->assertPeerRelationsHold(
            $this->get(route('home'))->assertOk()->getContent(),
            'navbar publik'
        );
    }

    public function test_toggle_menu_admin_punya_peer_sejajar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertPeerRelationsHold(
            $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent(),
            'sidebar admin'
        );
    }

    /**
     * Varian peer-checked Tailwind dikompilasi menjadi `:is(.peer:checked ~ *)`,
     * yaitu hanya berlaku pada saudara kandung yang mengikuti elemen .peer.
     * Elemen peer-checked yang diindukkan di tempat lain tidak akan pernah
     * bereaksi, dan bug ini tidak terlihat dari tes kehadiran teks biasa.
     */
    private function assertPeerRelationsHold(string $html, string $context): void
    {
        $previousState = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        $peerChecked = [];
        $seenIds = [];
        $duplicateIds = [];

        foreach (iterator_to_array($dom->getElementsByTagName('*')) as $element) {
            $class = $element->getAttribute('class');

            if (str_contains($class, 'peer-checked:')) {
                $peerChecked[] = $element;
            }

            $id = $element->getAttribute('id');

            if ($id === '') {
                continue;
            }

            if (isset($seenIds[$id])) {
                $duplicateIds[] = $id;
            }

            $seenIds[$id] = true;
        }

        $this->assertSame(
            [],
            array_values(array_unique($duplicateIds)),
            "$context memuat id duplikat; label for= dan getElementById akan menunjuk ke elemen yang salah."
        );

        $this->assertNotEmpty($peerChecked, "Tidak ada elemen peer-checked di $context; periksa kembali toggle-nya.");

        foreach ($peerChecked as $element) {
            $label = $element->tagName.($element->getAttribute('id') ? '#'.$element->getAttribute('id') : '');

            $hasPeer = false;
            $sibling = $element->previousSibling;

            while ($sibling !== null) {
                if ($sibling->nodeType === XML_ELEMENT_NODE
                    && preg_match('/(?:^|\s)peer(?:\s|$)/', $sibling->getAttribute('class'))) {
                    $hasPeer = true;

                    break;
                }

                $sibling = $sibling->previousSibling;
            }

            $this->assertTrue(
                $hasPeer,
                "$label di $context memakai peer-checked tetapi tidak punya .peer sebagai saudara kandung sebelumnya; selector Tailwind tidak akan cocok dan toggle-nya mati."
            );
        }
    }

    /**
     * Ambil daftar kelas pada pembungkus menu navigasi utama.
     */
    private function mainMenuClasses(): string
    {
        $content = $this->get(route('home'))->assertOk()->getContent();

        $found = preg_match('/id="main-menu"[^>]*\sclass="([^"]*)"/', $content, $matches);

        $this->assertSame(1, $found, 'Tidak menemukan pembungkus menu navigasi utama (id="main-menu").');

        return $matches[1];
    }
}
