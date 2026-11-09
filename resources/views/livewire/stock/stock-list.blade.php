<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Stok Barang</h1>
        <div class="flex flex-wrap gap-2 text-sm">
            <button wire:click="create" class="rounded bg-slate-900 px-3 py-2 text-white">+ Tambah</button>
            <button wire:click="openAi" class="rounded bg-purple-600 px-3 py-2 text-white">✨ Generate AI</button>
            <button wire:click="export" class="rounded bg-green-600 px-3 py-2 text-white">Export Excel</button>
            <button wire:click="downloadTemplate" class="rounded bg-slate-200 px-3 py-2">Template Excel</button>
        </div>
    </div>

    @if ($notice) <div class="rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ $notice }}</div> @endif
    @error('adjust') <div class="rounded bg-red-100 px-4 py-2 text-sm text-red-800">{{ $message }}</div> @enderror

    <div class="rounded-lg bg-white p-4 shadow-sm">
        <form wire:submit="import" class="flex flex-wrap items-center gap-3 text-sm">
            <span class="font-medium">Import Excel:</span>
            <input type="file" wire:model="importFile" accept=".xlsx,.xls">
            <button type="submit" class="rounded bg-blue-600 px-3 py-1.5 text-white">Import</button>
            @error('importFile') <span class="text-red-600">{{ $message }}</span> @enderror
        </form>
        @if ($importErrors)
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach ($importErrors as $error) <li wire:key="ierr-{{ $loop->index }}">{{ $error }}</li> @endforeach
            </ul>
        @endif
    </div>

    <div class="flex flex-wrap gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / SKU…"
               class="w-64 rounded border border-slate-300 px-3 py-2 text-sm">
        <select wire:model.live="statusFilter" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" wire:key="opt-{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr><th class="px-4 py-3">Barang</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($stocks as $stock)
                <tr wire:key="stock-{{ $stock->id }}">
                    <td class="px-4 py-3">
                        <a href="{{ route('stocks.show', $stock) }}" class="font-medium text-blue-700 hover:underline">{{ $stock->name }}</a>
                    </td>
                    <td class="px-4 py-3">{{ $stock->sku ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button wire:click="adjust({{ $stock->id }}, -1)" class="h-7 w-7 rounded bg-slate-200">−</button>
                            <span class="min-w-[4rem] text-center">{{ $stock->quantity }} {{ $stock->unit }}</span>
                            <button wire:click="adjust({{ $stock->id }}, 1)" class="h-7 w-7 rounded bg-slate-200">+</button>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $stock->price === null ? '-' : 'Rp '.number_format((float) $stock->price, 2, ',', '.') }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-medium {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span></td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="edit({{ $stock->id }})" class="text-blue-600 hover:underline">Edit</button>
                        <button wire:click="delete({{ $stock->id }})" wire:confirm="Hapus stok ini?" class="text-red-600 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada stok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $stocks->links() }}

    {{-- Modal create/edit --}}
    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-bold">{{ $editingId ? 'Edit Stok' : 'Tambah Stok' }}</h2>
                <form wire:submit="save">
                    @include('livewire.stock.partials.fields')
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" wire:click="closeForm" class="rounded bg-slate-200 px-4 py-2">Batal</button>
                        <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal AI --}}
    @if ($showAi)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-full w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-bold">Generate Stok dengan AI</h2>
                <form wire:submit="generateAi" class="flex flex-wrap gap-3">
                    <input type="text" wire:model="aiPrompt" placeholder="Contoh: perlengkapan kantor untuk startup 10 orang"
                           class="min-w-[16rem] flex-1 rounded border border-slate-300 px-3 py-2 text-sm">
                    <input type="number" min="1" max="10" wire:model="aiCount" class="w-20 rounded border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="rounded bg-purple-600 px-4 py-2 text-sm text-white">Generate</button>
                </form>
                @error('aiPrompt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('aiCount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @if ($aiError) <div class="mt-3 rounded bg-red-100 px-3 py-2 text-sm text-red-800">{{ $aiError }}</div> @endif

                @if ($aiItems)
                    <p class="mt-4 text-sm font-medium">Preview — pilih item yang ingin disimpan:</p>
                    <ul class="mt-2 divide-y rounded border">
                        @foreach ($aiItems as $i => $item)
                            <li wire:key="ai-{{ $i }}" class="flex gap-3 p-3 text-sm">
                                <input type="checkbox" value="{{ $i }}" wire:model="aiSelected" class="mt-1">
                                <div>
                                    <div class="font-medium">{{ $item['name'] }} <span class="text-slate-500">({{ $item['sku'] ?? 'tanpa SKU' }})</span></div>
                                    <div class="text-slate-600">{{ $item['quantity'] }} {{ $item['unit'] }} · min {{ $item['min_stock'] }} · {{ $item['price'] === null ? 'harga -' : 'Rp '.number_format($item['price'], 2, ',', '.') }}</div>
                                    <div class="text-slate-500">{{ $item['description'] }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="closeAi" class="rounded bg-slate-200 px-4 py-2">Tutup</button>
                    @if ($aiItems) <button type="button" wire:click="saveAiItems" class="rounded bg-slate-900 px-4 py-2 text-white">Simpan terpilih</button> @endif
                </div>
            </div>
        </div>
    @endif
</div>
