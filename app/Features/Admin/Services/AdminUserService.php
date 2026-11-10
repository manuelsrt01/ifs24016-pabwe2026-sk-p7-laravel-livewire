<?php

namespace App\Features\Admin\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Layanan baca-saja untuk admin. Tidak ada method ubah/hapus stok di sini. */
class AdminUserService
{
    public function __construct(private readonly StockRepositoryInterface $stocks) {}

    public function paginateUsers(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->withCount('stocks')
            ->when(filled($search), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function paginateStocksOf(User $user, ?string $search, ?string $status, int $perPage = 10): LengthAwarePaginator
    {
        return $this->stocks->paginateForUser($user->id, $search, $status, $perPage);
    }

    /** @return array{users: int, members: int, top_users: \Illuminate\Support\Collection<int, User>} */
    public function overview(): array
    {
        return [
            'users' => User::query()->count(),
            'members' => User::query()->where('role', User::ROLE_MEMBER)->count(),
            'top_users' => User::query()->withCount('stocks')->orderByDesc('stocks_count')->orderBy('id')->limit(5)->get(),
        ];
    }
}
