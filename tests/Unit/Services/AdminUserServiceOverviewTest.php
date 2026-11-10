<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_counts_users_and_ranks_top_users(): void
    {
        User::factory()->admin()->create();
        $few = User::factory()->create();
        $many = User::factory()->create();
        Stock::factory()->for($few)->count(1)->create();
        Stock::factory()->for($many)->count(3)->create();

        $overview = app(AdminUserService::class)->overview();

        $this->assertSame(3, $overview['users']);
        $this->assertSame(2, $overview['members']);
        $this->assertSame($many->id, $overview['top_users']->first()->id);
        $this->assertSame(3, $overview['top_users']->first()->stocks_count);
        $this->assertLessThanOrEqual(5, $overview['top_users']->count());
    }
}
