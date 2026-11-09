<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee('Daftar Akun');
    }

    public function test_user_registers_as_member_with_hashed_password_and_is_logged_in(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Siti')->set('username', 'siti')->set('email', 'siti@example.com')
            ->set('password', 'password123')->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('stocks.index'));

        $user = User::where('username', 'siti')->firstOrFail();
        $this->assertSame('member', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotSame('password123', $user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_validation_errors(): void
    {
        User::factory()->create(['username' => 'dupe', 'email' => 'dupe@example.com']);

        Livewire::test(Register::class)
            ->set('name', '')->set('username', 'dupe')->set('email', 'dupe@example.com')
            ->set('password', 'short')->set('password_confirmation', 'beda')
            ->call('register')
            ->assertHasErrors(['name', 'username', 'email', 'password']);
    }
}
