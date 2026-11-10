<?php

namespace Tests\Feature;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach (['/dashboard', '/stocks', '/profile', '/admin/users'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_logout_ends_session(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_member_cannot_open_admin_pages(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get("/admin/users/{$member->id}/stocks")->assertForbidden();
    }

    public function test_admin_cannot_open_member_stock_pages(): void
    {
        $stock = Stock::factory()->create();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/stocks')->assertForbidden();
        $this->actingAs($admin)->get("/stocks/{$stock->id}")->assertForbidden();
    }

    public function test_member_cannot_open_other_members_stock(): void
    {
        $stock = Stock::factory()->create();

        $this->actingAs(User::factory()->create())->get("/stocks/{$stock->id}")->assertForbidden();
    }

    public function test_member_can_open_own_stock_and_list(): void
    {
        $member = User::factory()->create();
        $stock = Stock::factory()->for($member)->create();

        $this->actingAs($member)->get('/stocks')->assertOk();
        $this->actingAs($member)->get("/stocks/{$stock->id}")->assertOk()->assertSee($stock->name);
    }

    public function test_admin_pages_and_profile_are_accessible_to_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get("/admin/users/{$member->id}/stocks")->assertOk();
        $this->actingAs($admin)->get('/profile')->assertOk();
    }
}
