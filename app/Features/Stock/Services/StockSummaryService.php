<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/** Ringkasan angka untuk halaman dashboard (hanya membaca data). */
class StockSummaryService
{
    /** @return array<string, mixed> */
    public function forUser(User $user): array
    {
        return $this->summarize(fn () => Stock::query()->where('user_id', $user->id));
    }

    /** @return array<string, mixed> Ringkasan seluruh pengguna (untuk admin). */
    public function forAll(): array
    {
        return $this->summarize(fn () => Stock::query());
    }

    /**
     * @param  Closure(): Builder<Stock>  $query  Pabrik query baru agar tiap agregasi tidak saling memengaruhi.
     * @return array<string, mixed>
     */
    private function summarize(Closure $query): array
    {
        $totals = $query()->toBase()->selectRaw(
            'COUNT(*) as items, COALESCE(SUM(quantity), 0) as units, COALESCE(SUM(quantity * COALESCE(price, 0)), 0) as value'
        )->first();

        $byStatus = $query()->toBase()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $counts = [];
        foreach (StockStatus::cases() as $status) {
            $counts[$status->value] = (int) ($byStatus[$status->value] ?? 0);
        }

        return [
            'items' => (int) $totals->items,
            'units' => (int) $totals->units,
            'value' => (float) $totals->value,
            'status_counts' => $counts,
            'attention' => $query()->with('user')
                ->whereIn('status', [StockStatus::LowStock->value, StockStatus::OutOfStock->value])
                ->orderBy('quantity')->orderBy('id')->limit(5)->get(),
            'recent' => $query()->with('user')->latest('id')->limit(5)->get(),
        ];
    }
}
