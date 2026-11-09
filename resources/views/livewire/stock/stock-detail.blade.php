<div class="mx-auto max-w-3xl space-y-4">
    <a href="{{ route('stocks.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke daftar</a>

    @if ($notice) <div class="rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ $notice }}</div> @endif
    @error('adjust') <div class="rounded bg-red-100 px-4 py-2 text-sm text-red-800">{{ $message }}</div> @enderror

    <div class="rounded-lg bg-white p-6 shadow-sm">
        @if ($editing)
            <form wire:submit="save">
                @include('livewire.stock.partials.fields')
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="cancelEdit" class="rounded bg-slate-200 px-4 py-2">Batal</button>
                    <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-white">Simpan</button>
                </div>
            </form>
        @else
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ $stock->name }}</h1>
                    <p class="text-sm text-slate-500">SKU: {{ $stock->sku ?? '-' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
            </div>

            @if ($stock->coverUrl())
                <img src="{{ $stock->coverUrl() }}" alt="Cover {{ $stock->name }}" class="mt-4 max-h-72 rounded object-cover">
            @endif

            <dl class="mt-5 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div><dt class="text-slate-500">Jumlah</dt><dd class="font-semibold">{{ $stock->quantity }}</dd></div>
                <div><dt class="text-slate-500">Satuan</dt><dd class="font-semibold">{{ $stock->unit }}</dd></div>
                <div><dt class="text-slate-500">Harga</dt><dd class="font-semibold">{{ $stock->price === null ? '-' : 'Rp '.number_format((float) $stock->price, 2, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500">Minimum stok</dt><dd class="font-semibold">{{ $stock->min_stock }}</dd></div>
            </dl>

            <div class="mt-5 flex items-center gap-2 text-sm">
                <span class="text-slate-500">Sesuaikan jumlah:</span>
                <button wire:click="adjust(-1)" class="h-8 w-8 rounded bg-slate-200">−</button>
                <button wire:click="adjust(1)" class="h-8 w-8 rounded bg-slate-200">+</button>
            </div>

            <div class="prose mt-6 max-w-none text-sm">{!! $stock->descriptionHtml() !!}</div>

            <div class="mt-6 flex gap-2 text-sm">
                <button wire:click="startEdit" class="rounded bg-slate-900 px-4 py-2 text-white">Edit</button>
                <button wire:click="delete" wire:confirm="Hapus stok ini?" class="rounded bg-red-600 px-4 py-2 text-white">Hapus</button>
            </div>
        @endif
    </div>
</div>
