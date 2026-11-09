<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function __construct(private readonly StockRepositoryInterface $repository) {}

    public function paginateFor(User $user, ?string $search, ?string $status, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginateForUser($user->id, $search, $status, $perPage);
    }

    public function allFor(User $user): Collection
    {
        return $this->repository->allForUser($user->id);
    }

    public function find(int $id): ?Stock
    {
        return $this->repository->find($id);
    }

    public function create(User $user, array $data, ?UploadedFile $cover = null): Stock
    {
        $payload = $this->normalize($data) + ['user_id' => $user->id];

        if ($cover) {
            $payload['cover_image_path'] = $cover->store('stock-covers', 'public');
        }

        return $this->repository->create($payload);
    }

    public function update(Stock $stock, array $data, ?UploadedFile $cover = null): Stock
    {
        $payload = $this->normalize($data);

        if ($cover) {
            $this->deleteCover($stock);
            $payload['cover_image_path'] = $cover->store('stock-covers', 'public');
        }

        return $this->repository->update($stock, $payload);
    }

    public function delete(Stock $stock): void
    {
        $this->deleteCover($stock);
        $this->repository->delete($stock);
    }

    /** Tambah/kurangi stok; tidak boleh menghasilkan nilai negatif. Status disinkronkan oleh model. */
    public function adjustQuantity(Stock $stock, int $delta): Stock
    {
        $new = $stock->quantity + $delta;

        if ($new < 0) {
            throw ValidationException::withMessages(['adjust' => 'Jumlah stok tidak boleh kurang dari 0.']);
        }

        return $this->repository->update($stock, ['quantity' => $new]);
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    public function createMany(User $user, array $rows): int
    {
        DB::transaction(function () use ($user, $rows): void {
            foreach ($rows as $row) {
                $this->create($user, $row);
            }
        });

        return count($rows);
    }

    private function deleteCover(Stock $stock): void
    {
        if ($stock->cover_image_path) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }
    }

    private function normalize(array $d): array
    {
        return [
            'name' => trim((string) $d['name']),
            'sku' => filled($d['sku'] ?? null) ? trim((string) $d['sku']) : null,
            'description' => filled($d['description'] ?? null) ? (string) $d['description'] : null,
            'quantity' => (int) ($d['quantity'] ?? 0),
            'unit' => filled($d['unit'] ?? null) ? trim((string) $d['unit']) : 'pcs',
            'price' => filled($d['price'] ?? null) ? round((float) $d['price'], 2) : null,
            'min_stock' => filled($d['min_stock'] ?? null) ? (int) $d['min_stock'] : 5,
        ];
    }
}
