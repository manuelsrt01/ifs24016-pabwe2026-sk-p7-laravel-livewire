<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockSummaryService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_user_summarizes_only_own_stocks(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->create(['name' => 'A', 'quantity' => 10, 'price' => 100, 'min_stock' => 5]);
        Stock::factory()->for($user)->create(['name' => 'B', 'quantity' => 3, 'price' => 50, 'min_stock' => 5]);
        Stock::factory()->for($user)->create(['name' => 'C', 'quantity' => 0, 'price' => null, 'min_stock' => 5]);
        Stock::factory()->create(['quantity' => 999, 'price' => 999]);

        $summary = (new StockSummaryService)->forUser($user);

        $this->assertSame(3, $summary['items']);
        $this->assertSame(13, $summary['units']);
        $this->assertEqualsWithDelta(1150.0, $summary['value'], 0.001);
        $this->assertSame(['available' => 1, 'low_stock' => 1, 'out_of_stock' => 1], $summary['status_counts']);
        $this->assertSame(['C', 'B'], $summary['attention']->pluck('name')->all());
        $this->assertCount(3, $summary['recent']);
        $this->assertSame('C', $summary['recent']->first()->name);
    }

    public function test_for_user_without_stocks_returns_zeroes(): void
    {
        $summary = (new StockSummaryService)->forUser(User::factory()->create());

        $this->assertSame(0, $summary['items']);
        $this->assertSame(0, $summary['units']);
        $this->assertSame(0.0, $summary['value']);
        $this->assertSame(['available' => 0, 'low_stock' => 0, 'out_of_stock' => 0], $summary['status_counts']);
        $this->assertCount(0, $summary['attention']);
        $this->assertCount(0, $summary['recent']);
    }

    public function test_for_all_covers_every_user_and_limits_lists_to_five(): void
    {
        Stock::factory()->count(7)->create(['quantity' => 0]);
        Stock::factory()->count(2)->create(['quantity' => 50, 'min_stock' => 5]);

        $summary = (new StockSummaryService)->forAll();

        $this->assertSame(9, $summary['items']);
        $this->assertSame(7, $summary['status_counts']['out_of_stock']);
        $this->assertSame(2, $summary['status_counts']['available']);
        $this->assertCount(5, $summary['attention']);
        $this->assertCount(5, $summary['recent']);
        $this->assertTrue($summary['recent']->first()->relationLoaded('user'));
    }
}
