<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = app(StockService::class);
        $this->user = User::factory()->create();
    }

    public function test_create_applies_defaults_and_status(): void
    {
        $stock = $this->service->create($this->user, ['name' => '  Pena  ', 'quantity' => '0']);

        $this->assertSame('Pena', $stock->name);
        $this->assertSame('pcs', $stock->unit);
        $this->assertSame(5, $stock->min_stock);
        $this->assertNull($stock->sku);
        $this->assertNull($stock->price);
        $this->assertSame('out_of_stock', $stock->status->value);
        $this->assertSame($this->user->id, $stock->user_id);
    }

    public function test_create_and_update_with_cover(): void
    {
        $stock = $this->service->create($this->user, ['name' => 'A', 'quantity' => 10, 'price' => '5.555'], UploadedFile::fake()->image('a.jpg'));
        $first = $stock->cover_image_path;
        Storage::disk('public')->assertExists($first);
        $this->assertSame('5.56', $stock->price);

        $this->service->update($stock, ['name' => 'B', 'quantity' => 1, 'sku' => ' S '], UploadedFile::fake()->image('b.jpg'));

        $stock->refresh();
        $this->assertSame('S', $stock->sku);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($stock->cover_image_path);
    }

    public function test_update_without_cover_keeps_existing_cover(): void
    {
        $stock = $this->service->create($this->user, ['name' => 'A', 'quantity' => 10], UploadedFile::fake()->image('a.jpg'));
        $path = $stock->cover_image_path;

        $this->service->update($stock, ['name' => 'A2', 'quantity' => 10]);

        $this->assertSame($path, $stock->refresh()->cover_image_path);
    }

    public function test_delete_without_cover(): void
    {
        $stock = Stock::factory()->for($this->user)->create(['cover_image_path' => null]);

        $this->service->delete($stock);

        $this->assertModelMissing($stock);
    }

    public function test_adjust_quantity(): void
    {
        $stock = Stock::factory()->for($this->user)->create(['quantity' => 3, 'min_stock' => 5]);

        $this->assertSame(10, $this->service->adjustQuantity($stock, 7)->quantity);
        $this->assertSame('available', $stock->status->value);

        $this->expectException(ValidationException::class);
        $this->service->adjustQuantity($stock, -11);
    }

    public function test_create_many_and_queries(): void
    {
        $count = $this->service->createMany($this->user, [
            ['name' => 'A', 'quantity' => 1],
            ['name' => 'B', 'quantity' => 100],
        ]);

        $this->assertSame(2, $count);
        $this->assertCount(2, $this->service->allFor($this->user));
        $this->assertSame(2, $this->service->paginateFor($this->user, null, null)->total());
        $this->assertNotNull($this->service->find(Stock::first()->id));
    }

    public function test_create_many_is_atomic(): void
    {
        try {
            $this->service->createMany($this->user, [['name' => 'OK', 'quantity' => 1], ['quantity' => 1]]);
        } catch (\Throwable) {
            // baris kedua tanpa name memicu error
        }

        $this->assertDatabaseCount('stocks', 0);
    }
}
