<?php

namespace App\Features\Stock\Repositories;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StockRepository implements StockRepositoryInterface
{
    public function paginateForUser(int $userId, ?string $search, ?string $status, int $perPage = 10): LengthAwarePaginator
    {
        return Stock::query()
            ->where('user_id', $userId)
            ->when(filled($search), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")))
            ->when(filled($status), fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate($perPage);
    }

    public function allForUser(int $userId): Collection
    {
        return Stock::query()->where('user_id', $userId)->orderBy('id')->get();
    }

    public function find(int $id): ?Stock
    {
        return Stock::query()->find($id);
    }

    public function create(array $data): Stock
    {
        return Stock::create($data);
    }

    public function update(Stock $stock, array $data): Stock
    {
        $stock->update($data);

        return $stock;
    }

    public function delete(Stock $stock): void
    {
        $stock->delete();
    }
}
