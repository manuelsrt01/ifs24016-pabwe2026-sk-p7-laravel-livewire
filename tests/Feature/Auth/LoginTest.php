<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_required_element_ids(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('id="login-username"', false)
            ->assertSee('id="login-password"', false)
            ->assertSee('id="login-submit"', false);
    }

    public function test_member_logs_in_with_username_and_goes_to_stocks(): void
    {
        $user = User::factory()->create(['username' => 'budi', 'password' => 'secret123']);

        Livewire::test(Login::class)
            ->set('username', 'budi')->set('password', 'secret123')
            ->call('login')
            ->assertRedirect(route('stocks.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_logs_in_with_email_and_goes_to_admin_users(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com', 'password' => 'secret123']);

        Livewire::test(Login::class)
            ->set('username', 'boss@example.com')->set('password', 'secret123')
            ->call('login')
            ->assertRedirect(route('admin.users'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_shows_error(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'secret123']);

        Livewire::test(Login::class)
            ->set('username', 'budi')->set('password', 'salah')
            ->call('login')
            ->assertHasErrors(['username']);

        $this->assertGuest();
    }

    public function test_fields_are_required(): void
    {
        Livewire::test(Login::class)->call('login')->assertHasErrors(['username' => 'required', 'password' => 'required']);
    }

    public function test_logged_in_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect(route('stocks.index'));
        $this->actingAs(User::factory()->admin()->create())->get('/login')->assertRedirect(route('admin.users'));
    }
}
