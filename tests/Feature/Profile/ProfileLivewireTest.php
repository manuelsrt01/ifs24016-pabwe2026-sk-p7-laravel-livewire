<?php

namespace Tests\Feature\Profile;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_current_data(): void
    {
        $user = User::factory()->create(['name' => 'Andi']);

        Livewire::actingAs($user)->test(Profile::class)->assertSet('name', 'Andi')->assertSet('username', $user->username);
    }

    public function test_update_profile_cannot_change_role(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('name', 'Baru')->set('username', 'baru')->set('email', 'baru@example.com')
            ->call('saveProfile')->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Baru', $user->name);
        $this->assertSame('member', $user->role);
    }

    public function test_profile_validation_rejects_duplicates(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('username', $other->username)->set('email', $other->email)
            ->call('saveProfile')->assertHasErrors(['username', 'email']);
    }

    public function test_change_password(): void
    {
        $user = User::factory()->create(['password' => 'lama12345']);

        Livewire::actingAs($user)->test(Profile::class)
            ->set('current_password', 'lama12345')->set('password', 'baru12345')->set('password_confirmation', 'baru12345')
            ->call('savePassword')->assertHasNoErrors()->assertSet('password', '');

        $this->assertTrue(Hash::check('baru12345', $user->refresh()->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'lama12345']);

        Livewire::actingAs($user)->test(Profile::class)
            ->set('current_password', 'salah')->set('password', 'baru12345')->set('password_confirmation', 'baru12345')
            ->call('savePassword')->assertHasErrors(['current_password']);
    }

    public function test_upload_photo_replaces_old_one(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/old.jpg', 'x');
        $user = User::factory()->create(['profile_photo_path' => 'profile-photos/old.jpg']);

        Livewire::actingAs($user)->test(Profile::class)
            ->set('photo', UploadedFile::fake()->image('me.jpg'))
            ->call('savePhoto')->assertHasNoErrors();

        Storage::disk('public')->assertMissing('profile-photos/old.jpg');
        Storage::disk('public')->assertExists($user->refresh()->profile_photo_path);
    }

    public function test_photo_validation(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())->test(Profile::class)
            ->call('savePhoto')->assertHasErrors(['photo' => 'required']);
    }
}
