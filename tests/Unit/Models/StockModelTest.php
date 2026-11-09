<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_is_synced_on_create_and_update(): void
    {
        $stock = Stock::factory()->create(['quantity' => 0, 'min_stock' => 5, 'status' => 'available']);
        $this->assertSame(StockStatus::OutOfStock, $stock->status);

        $stock->update(['quantity' => 5]);
        $this->assertSame(StockStatus::LowStock, $stock->status);

        $stock->update(['quantity' => 6]);
        $this->assertSame(StockStatus::Available, $stock->status);

        $stock->update(['min_stock' => 10]);
        $this->assertSame(StockStatus::LowStock, $stock->status);
    }

    public function test_belongs_to_user_and_cascade_delete(): void
    {
        $stock = Stock::factory()->create();

        $this->assertInstanceOf(User::class, $stock->user);

        $stock->user->delete();
        $this->assertModelMissing($stock);
    }

    public function test_cover_url(): void
    {
        Storage::fake('public');

        $this->assertNull(Stock::factory()->make(['cover_image_path' => null])->coverUrl());
        $this->assertStringContainsString('stock-covers/a.jpg', Stock::factory()->make(['cover_image_path' => 'stock-covers/a.jpg'])->coverUrl());
    }

    public function test_description_html_renders_markdown_and_strips_html(): void
    {
        $stock = Stock::factory()->make(['description' => "**halo**\n\n<script>x</script>"]);

        $html = $stock->descriptionHtml();
        $this->assertStringContainsString('<strong>halo</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertSame('', Stock::factory()->make(['description' => null])->descriptionHtml());
    }
}
