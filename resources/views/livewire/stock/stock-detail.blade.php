<div class="mx-auto max-w-5xl space-y-6">
    <x-toast :message="$notice" />

    <a href="{{ auth()->user()->isAdmin() ? route('admin.users.stocks', $stock->user_id) : route('stocks.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-ink"><x-icon name="arrow-left" size="h-4 w-4" /> Kembali ke daftar</a>

    @error('adjust')
        <div class="flex items-center gap-3 rounded-lg border border-red-600/25 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"><x-icon name="alert" /> {{ $message }}</div>
    @enderror

    @if ($editing)
        <section class="panel">
            <div class="border-b border-line px-6 py-4"><h2 class="text-lg font-bold">Edit barang</h2></div>
            <form wire:submit="save">
                <div class="p-6">@include('livewire.stock.partials.fields')</div>
                <div class="flex justify-end gap-2 border-t border-line bg-concrete/50 px-6 py-4">
                    <button type="button" wire:click="cancelEdit" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save,cover">Simpan</button>
                </div>
            </form>
        </section>
    @else
        <section class="panel overflow-hidden">
            <div class="grid md:grid-cols-[2fr_3fr]">
                <div class="min-h-56 bg-concrete">
                    @if ($stock->coverUrl())
                        <img src="{{ $stock->coverUrl() }}" alt="Gambar {{ $stock->name }}" class="h-full max-h-96 w-full object-cover">
                    @else
                        <div class="grid h-full min-h-56 place-items-center text-slate-400"><span class="text-center"><x-icon name="photo" size="mx-auto h-12 w-12" /><span class="mt-2 block text-sm">Belum ada gambar</span></span></div>
                    @endif
                </div>

                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h1 class="text-3xl font-extrabold tracking-tight">{{ $stock->name }}</h1>
                            <p class="mt-2 text-sm text-slate-600">SKU: @if ($stock->sku) <span class="tag">{{ $stock->sku }}</span> @else - @endif</p>
                        </div>
                        <x-status-badge :status="$stock->status" />
                    </div>

                    <dl class="mt-7 grid grid-cols-2 gap-x-6 gap-y-5 border-y border-line py-6 sm:grid-cols-4">
                        <div><dt class="text-sm text-slate-600">Jumlah</dt><dd class="mt-0.5 text-2xl font-bold tabular-nums">{{ $stock->quantity }}</dd></div>
                        <div><dt class="text-sm text-slate-600">Satuan</dt><dd class="mt-0.5 text-2xl font-bold">{{ $stock->unit }}</dd></div>
                        <div><dt class="text-sm text-slate-600">Harga</dt><dd class="mt-0.5 text-lg font-bold tabular-nums">{{ $stock->price === null ? '-' : 'Rp '.number_format((float) $stock->price, 2, ',', '.') }}</dd></div>
                        <div><dt class="text-sm text-slate-600">Minimum stok</dt><dd class="mt-0.5 text-2xl font-bold tabular-nums">{{ $stock->min_stock }}</dd></div>
                    </dl>

                    @can('update', $stock)
                        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-4">
                            <div>
                                <p class="mb-1.5 text-sm font-semibold">Sesuaikan jumlah</p>
                                <div class="inline-flex items-center rounded-lg border border-line bg-white">
                                    <button wire:click="adjust(-1)" class="grid h-10 w-11 place-items-center rounded-l-lg text-slate-600 transition hover:bg-concrete hover:text-ink" aria-label="Kurangi satu"><x-icon name="minus" size="h-4 w-4" /></button>
                                    <span class="min-w-16 border-x border-line px-3 text-center text-base font-bold tabular-nums">{{ $stock->quantity }}</span>
                                    <button wire:click="adjust(1)" class="grid h-10 w-11 place-items-center rounded-r-lg text-slate-600 transition hover:bg-concrete hover:text-ink" aria-label="Tambah satu"><x-icon name="plus" size="h-4 w-4" /></button>
                                </div>
                            </div>
                            <div class="flex gap-2 sm:ml-auto">
                                <button wire:click="startEdit" class="btn btn-dark"><x-icon name="pencil" size="h-4 w-4" /> Edit</button>
                                <button wire:click="delete" wire:confirm="Hapus barang ini? Tindakan ini tidak dapat dibatalkan." class="btn btn-secondary text-red-600 hover:bg-red-50"><x-icon name="trash" size="h-4 w-4" /> Hapus</button>
                            </div>
                        </div>
                    @else
                        <p class="mt-6 flex items-center gap-2 text-sm font-medium text-slate-600"><x-icon name="lock" size="h-4 w-4" /> Mode baca saja. Admin tidak dapat mengubah stok pengguna.</p>
                    @endcan
                </div>
            </div>
        </section>

        <section class="panel p-6 sm:p-8">
            <h2 class="text-lg font-bold">Deskripsi</h2>
            @if (filled($stock->description))
                <div class="markdown mt-3">{!! $stock->descriptionHtml() !!}</div>
            @else
                <p class="mt-2 text-sm text-slate-600">Belum ada deskripsi. Pilih Edit untuk menambahkannya.</p>
            @endif
        </section>
    @endif
</div>
