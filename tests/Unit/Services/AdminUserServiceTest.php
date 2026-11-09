<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_users_with_counts_and_search(): void
    {
        $service = app(AdminUserService::class);
        $a = User::factory()->create(['name' => 'Aaa', 'username' => 'aaa', 'email' => 'aaa@x.test']);
        User::factory()->create(['name' => 'Bbb', 'username' => 'bbb', 'email' => 'bbb@x.test']);
        Stock::factory()->for($a)->count(2)->create();

        $all = $service->paginateUsers(null);
        $this->assertSame(2, $all->total());
        $this->assertSame(2, $all->items()[0]->stocks_count);

        $this->assertSame(1, $service->paginateUsers('bbb')->total());
        $this->assertSame(1, $service->paginateUsers('aaa@x.test')->total());
    }

    public function test_paginate_stocks_of_user(): void
    {
        $service = app(AdminUserService::class);
        $user = User::factory()->create();
        Stock::factory()->for($user)->create(['name' => 'Satu', 'quantity' => 0]);
        Stock::factory()->for($user)->create(['name' => 'Dua', 'quantity' => 99]);
        Stock::factory()->create();

        $this->assertSame(2, $service->paginateStocksOf($user, null, null)->total());
        $this->assertSame(1, $service->paginateStocksOf($user, 'Satu', null)->total());
        $this->assertSame(1, $service->paginateStocksOf($user, null, 'out_of_stock')->total());
    }
}
