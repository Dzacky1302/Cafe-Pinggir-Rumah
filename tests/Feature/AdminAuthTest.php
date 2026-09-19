<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Autentikasi & autorisasi area admin.
 *
 * Setiap test memakai REMOTE_ADDR berbeda supaya pembatas laju (throttle)
 * berbasis IP tidak saling mempengaruhi antar test.
 */
class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_redirect_guests_to_the_login_page(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.products.index'))->assertRedirect(route('admin.login'));
    }

    public function test_a_customer_account_cannot_open_the_admin_dashboard(): void
    {
        // Factory default menghasilkan role 'customer' (lihat add_role_to_users_table).
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_an_admin_can_open_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_an_admin_can_log_in(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'rahasia123']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->post(route('admin.login.submit'), [
                'email' => $admin->email,
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_customer_cannot_log_in_through_the_admin_form(): void
    {
        $customer = User::factory()->create(['password' => 'rahasia123']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->post(route('admin.login.submit'), [
                'email' => $customer->email,
                'password' => 'rahasia123',
            ])
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_repeated_failed_logins_are_rate_limited(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'rahasia123']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
                ->post(route('admin.login.submit'), [
                    'email' => $admin->email,
                    'password' => 'password-salah',
                ]);
        }

        // Percobaan ke-6 melewati batas 5/menit (throttle:5,1 di routes/web.php).
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
            ->post(route('admin.login.submit'), [
                'email' => $admin->email,
                'password' => 'rahasia123',
            ])
            ->assertStatus(429);

        $this->assertGuest();
    }
}
