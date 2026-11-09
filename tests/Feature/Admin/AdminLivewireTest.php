<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_shows_users_with_stock_counts_and_search(): void
    {
        $admin = User::factory()->admin()->create();
        $budi = User::factory()->create(['name' => 'Budi Santoso']);
        User::factory()->create(['name' => 'Citra Lestari']);
        Stock::factory()->for($budi)->count(3)->create();

        Livewire::actingAs($admin)->test(UserList::class)
            ->assertSee('Budi Santoso')->assertSee('Citra Lestari')
            ->set('search', 'budi')->assertSee('Budi Santoso')->assertDontSee('Citra Lestari');
    }

    public function test_user_stock_list_is_read_only_and_filtered(): void
    {
        $admin = User::factory()->admin()->create();
        $budi = User::factory()->create();
        Stock::factory()->for($budi)->create(['name' => 'Barang Budi', 'quantity' => 0]);
        Stock::factory()->for($budi)->create(['name' => 'Barang Penuh', 'quantity' => 99]);
        Stock::factory()->create(['name' => 'Barang Orang Lain']);

        $c = Livewire::actingAs($admin)->test(AdminUserStockList::class, ['user' => $budi])
            ->assertSee('Barang Budi')->assertSee('Barang Penuh')->assertDontSee('Barang Orang Lain')
            ->assertSee('Mode baca saja')
            ->set('statusFilter', 'out_of_stock')->assertSee('Barang Budi')->assertDontSee('Barang Penuh')
            ->set('statusFilter', '')->set('search', 'Penuh')->assertSee('Barang Penuh')->assertDontSee('Barang Budi');

        // Tidak ada aksi ubah/hapus pada komponen admin.
        $this->assertFalse(method_exists(AdminUserStockList::class, 'delete'));
        $this->assertFalse(method_exists(AdminUserStockList::class, 'save'));
        $this->assertFalse(method_exists(AdminUserStockList::class, 'adjust'));
    }

    public function test_admin_has_no_write_access_to_stock_via_policy(): void
    {
        $admin = User::factory()->admin()->create();
        $stock = Stock::factory()->create();

        $this->assertTrue($admin->can('view', $stock));
        $this->assertFalse($admin->can('update', $stock));
        $this->assertFalse($admin->can('delete', $stock));
    }

    public function test_member_cannot_mount_admin_components_via_routes(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
    }
}
