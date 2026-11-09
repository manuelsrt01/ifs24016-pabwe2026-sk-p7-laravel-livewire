<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_profile_ignores_role_and_other_fields(): void
    {
        $user = User::factory()->create();

        (new ProfileService)->updateProfile($user, ['name' => 'N', 'username' => 'u', 'email' => 'n@x.test', 'role' => 'admin']);

        $user->refresh();
        $this->assertSame('N', $user->name);
        $this->assertSame('member', $user->role);
    }

    public function test_update_password_hashes(): void
    {
        $user = User::factory()->create();

        (new ProfileService)->updatePassword($user, 'baru12345');

        $this->assertTrue(Hash::check('baru12345', $user->refresh()->password));
    }

    public function test_update_photo_deletes_previous(): void
    {
        Storage::fake('public');
        $service = new ProfileService;
        $user = User::factory()->create();

        $service->updatePhoto($user, UploadedFile::fake()->image('a.jpg'));
        $first = $user->refresh()->profile_photo_path;
        Storage::disk('public')->assertExists($first);

        $service->updatePhoto($user, UploadedFile::fake()->image('b.jpg'));
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($user->refresh()->profile_photo_path);
    }
}
