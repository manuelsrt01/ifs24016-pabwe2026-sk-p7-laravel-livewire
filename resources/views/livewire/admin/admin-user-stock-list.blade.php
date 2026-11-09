<div class="space-y-4">
    <a href="{{ route('admin.users') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke pengguna</a>
    <div>
        <h1 class="text-2xl font-bold">Stok milik {{ $user->name }}</h1>
        <p class="text-sm text-slate-500">{{ '@'.$user->username }} · Mode baca saja</p>
    </div>

    <div class="flex flex-wrap gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / SKU…"
               class="w-64 rounded border border-slate-300 px-3 py-2 text-sm">
        <select wire:model.live="statusFilter" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach (\App\Features\Stock\Enums\StockStatus::cases() as $status)
                <option value="{{ $status->value }}" wire:key="opt-{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr><th class="px-4 py-3">Barang</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Min</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Status</th></tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($stocks as $stock)
                <tr wire:key="stock-{{ $stock->id }}">
                    <td class="px-4 py-3 font-medium">{{ $stock->name }}</td>
                    <td class="px-4 py-3">{{ $stock->sku ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $stock->quantity }} {{ $stock->unit }}</td>
                    <td class="px-4 py-3">{{ $stock->min_stock }}</td>
                    <td class="px-4 py-3">{{ $stock->price === null ? '-' : 'Rp '.number_format((float) $stock->price, 2, ',', '.') }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-medium {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Pengguna ini belum memiliki stok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $stocks->links() }}
</div>
