@php
    $chips = ['' => 'Semua'];
    foreach (\App\Features\Stock\Enums\StockStatus::cases() as $s) { $chips[$s->value] = $s->label(); }
@endphp
<div class="space-y-6">
    <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-ink"><x-icon name="arrow-left" size="h-4 w-4" /> Kembali ke pengguna</a>

    <x-page-header :title="'Stok milik '.$user->name" :subtitle="'@'.$user->username" />

    <div class="flex items-center gap-3 rounded-lg border border-signal-deep/40 bg-signal/20 px-4 py-3 text-sm font-medium">
        <x-icon name="lock" size="h-5 w-5" /> Mode baca saja. Admin dapat melihat stok ini, tetapi tidak dapat mengubah atau menghapusnya.
    </div>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="relative lg:w-80">
            <span class="pointer-events-none absolute inset-y-0 left-3.5 grid place-items-center text-slate-600"><x-icon name="search" size="h-4 w-4" /></span>
            <input type="search" aria-label="Pencarian" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau SKU" class="input pl-10">
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($chips as $value => $label)
                <button type="button" wire:key="chip-{{ $value === '' ? 'all' : $value }}" wire:click="$set('statusFilter', '{{ $value }}')"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $statusFilter === (string) $value ? 'border-ink bg-ink text-white' : 'border-line bg-white text-slate-700 hover:border-ink' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <section class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-line bg-concrete/60">
                    <tr><th class="th">Barang</th><th class="th">Jumlah</th><th class="th hidden sm:table-cell">Minimum</th><th class="th hidden md:table-cell">Harga</th><th class="th">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-line">
                @forelse ($stocks as $stock)
                    <tr wire:key="stock-{{ $stock->id }}" class="transition hover:bg-concrete/40">
                        <td class="px-4 py-3">
                            <span class="block max-w-64 truncate font-semibold">{{ $stock->name }}</span>
                            @if ($stock->sku) <span class="tag mt-1">{{ $stock->sku }}</span> @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-bold tabular-nums">{{ $stock->quantity }} <span class="font-medium text-slate-600">{{ $stock->unit }}</span></td>
                        <td class="hidden px-4 py-3 text-sm tabular-nums sm:table-cell">{{ $stock->min_stock }}</td>
                        <td class="hidden px-4 py-3 text-sm tabular-nums md:table-cell">{{ $stock->price === null ? '-' : 'Rp '.number_format((float) $stock->price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$stock->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-14 text-center"><p class="font-bold">Pengguna ini belum memiliki stok</p><p class="mt-1 text-sm text-slate-600">Stok akan muncul di sini setelah pengguna menambahkannya.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $stocks->links() }}
</div>
