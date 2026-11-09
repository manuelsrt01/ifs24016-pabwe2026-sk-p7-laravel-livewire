<?php

namespace Tests\Unit\Repositories;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StockRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new StockRepository;
    }

    public function test_paginate_is_scoped_to_user_with_search_and_status(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->create(['name' => 'Alpha', 'sku' => 'A-1', 'quantity' => 50]);
        Stock::factory()->for($user)->create(['name' => 'Beta', 'sku' => 'B-1', 'quantity' => 0]);
        Stock::factory()->create(['name' => 'Alpha Orang Lain']);

        $this->assertSame(2, $this->repo->paginateForUser($user->id, null, null)->total());
        $this->assertSame(1, $this->repo->paginateForUser($user->id, 'alp', null)->total());
        $this->assertSame(1, $this->repo->paginateForUser($user->id, 'B-1', null)->total());
        $this->assertSame(1, $this->repo->paginateForUser($user->id, null, 'out_of_stock')->total());
        $this->assertSame(0, $this->repo->paginateForUser($user->id, 'Alpha', 'out_of_stock')->total());
    }

    public function test_pagination_page_size(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->count(12)->create();

        $page = $this->repo->paginateForUser($user->id, null, null, 5);
        $this->assertCount(5, $page->items());
        $this->assertSame(3, $page->lastPage());
    }

    public function test_all_find_create_update_delete(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->count(2)->create();
        Stock::factory()->create();

        $this->assertCount(2, $this->repo->allForUser($user->id));

        $created = $this->repo->create(['user_id' => $user->id, 'name' => 'Baru', 'quantity' => 1, 'unit' => 'pcs', 'min_stock' => 5]);
        $this->assertSame($created->id, $this->repo->find($created->id)->id);
        $this->assertNull($this->repo->find(999999));

        $this->assertSame('Ubah', $this->repo->update($created, ['name' => 'Ubah'])->name);

        $this->repo->delete($created);
        $this->assertNull($this->repo->find($created->id));
    }
}
