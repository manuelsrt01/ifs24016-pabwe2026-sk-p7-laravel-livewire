<?php

namespace Tests\Feature;

use App\Features\Stock\Livewire\Dashboard;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_member_sees_only_own_figures(): void
    {
        $member = User::factory()->create(['name' => 'Siti Aminah']);
        Stock::factory()->for($member)->create(['name' => 'Kabel LAN', 'quantity' => 2, 'min_stock' => 5, 'price' => 10000]);
        Stock::factory()->for($member)->create(['name' => 'Router', 'quantity' => 40, 'min_stock' => 5, 'price' => 100000]);
        Stock::factory()->create(['name' => 'Barang Orang Lain', 'quantity' => 0]);

        $this->actingAs($member)->get('/dashboard')
            ->assertOk()
            ->assertSee('Siti')
            ->assertSee('Kabel LAN')
            ->assertSee('Router')
            ->assertDontSee('Barang Orang Lain');
    }

    public function test_member_without_stocks_sees_empty_state(): void
    {
        Livewire::actingAs(User::factory()->create())->test(Dashboard::class)
            ->assertOk()
            ->assertSee('Belum ada barang');
    }

    public function test_member_with_all_items_healthy_sees_no_alerts(): void
    {
        $member = User::factory()->create();
        Stock::factory()->for($member)->create(['quantity' => 80, 'min_stock' => 5]);

        Livewire::actingAs($member)->test(Dashboard::class)
            ->assertOk()
            ->assertSee('Semua barang masih aman');
    }

    public function test_admin_sees_system_wide_overview(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Kepala Gudang']);
        $budi = User::factory()->create(['name' => 'Budi Santoso']);
        Stock::factory()->for($budi)->create(['name' => 'Barang Budi', 'quantity' => 0]);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Barang Budi')
            ->assertSee('Mode baca saja');
    }
}
