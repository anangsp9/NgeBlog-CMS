<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_diakses(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_yang_login_diarahkan_ke_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_pengunjung_yang_login_diarahkan_ke_beranda(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);

        $this->post(route('login.store'), [
            'email' => $visitor->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($visitor);
    }

    public function test_kredensial_salah_ditolak(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'kata-sandi-salah',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_percobaan_login_dibatasi_maksimal_lima_kali(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'kata-sandi-salah',
            ]);
        }

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_pengguna_baru_daftar_dengan_peran_pengunjung(): void
    {
        Event::fake([Registered::class]);

        $this->post(route('register.store'), [
            'name' => 'Budi Pembaca',
            'email' => 'budi@example.com',
            'password' => 'rahasia-kuat-123',
            'password_confirmation' => 'rahasia-kuat-123',
        ])->assertRedirect(route('home'));

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame('visitor', $user->role);
        $this->assertAuthenticatedAs($user);

        Event::assertDispatched(Registered::class);
    }

    public function test_pendaftaran_menolak_email_yang_sudah_terpakai(): void
    {
        $existing = User::factory()->create();

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => ' orang Duplikat',
                'email' => $existing->email,
                'password' => 'rahasia-kuat-123',
                'password_confirmation' => 'rahasia-kuat-123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_tidak_bisa_membuka_halaman_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('login'))->assertRedirect();
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect();

        $this->assertGuest();
    }
}
