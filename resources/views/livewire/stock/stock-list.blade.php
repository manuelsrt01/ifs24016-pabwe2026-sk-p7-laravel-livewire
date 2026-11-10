@php
    $chips = ['' => 'Semua'];
    foreach ($statuses as $s) { $chips[$s->value] = $s->label(); }
@endphp
<div class="space-y-6">
    <x-toast :message="$notice" />

    <x-page-header title="Stok barang" subtitle="Tambah barang, sesuaikan jumlahnya dengan tombol + dan −, lalu pantau statusnya.">
        <button wire:click="create" class="btn btn-primary"><x-icon name="plus" size="h-4 w-4" /> Tambah barang</button>
        <button wire:click="openAi" class="btn btn-dark"><x-icon name="sparkles" size="h-4 w-4" /> Generate AI</button>
        <button wire:click="export" class="btn btn-secondary"><x-icon name="download" size="h-4 w-4" /> Export Excel</button>
        <button wire:click="downloadTemplate" class="btn btn-secondary">Template Excel</button>
    </x-page-header>

    @error('adjust')
        <div class="flex items-center gap-3 rounded-lg border border-red-600/25 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"><x-icon name="alert" size="h-5 w-5" /> {{ $message }}</div>
    @enderror

    {{-- Import --}}
    <section class="panel p-4 sm:p-5">
        <form wire:submit="import" class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-concrete text-ink"><x-icon name="upload" /></span>
                <div class="min-w-0">
                    <p class="font-semibold">Import dari Excel</p>
                    <p class="text-sm text-slate-600">Unduh template, isi datanya, lalu unggah (.xlsx atau .xls, maks. 5 MB).</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="file" wire:model="importFile" accept=".xlsx,.xls"
                       class="max-w-60 text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-concrete file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink hover:file:bg-line">
                <button type="submit" class="btn btn-dark" wire:loading.attr="disabled" wire:target="importFile,import">Import</button>
            </div>
        </form>
        @error('importFile') <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
        @if ($importErrors)
            <div class="mt-4 rounded-lg border border-red-600/25 bg-red-50 p-4">
                <p class="text-sm font-bold text-red-800">Import dibatalkan. Perbaiki data berikut lalu unggah ulang:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-800">
                    @foreach ($importErrors as $error) <li wire:key="ierr-{{ $loop->index }}">{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif
    </section>

    {{-- Pencarian + filter status --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="relative lg:w-80">
            <span class="pointer-events-none absolute inset-y-0 left-3.5 grid place-items-center text-slate-400"><x-icon name="search" size="h-4 w-4" /></span>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau SKU" class="input pl-10">
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($chips as $value => $label)
                <button type="button" wire:key="chip-{{ $value === '' ? 'all' : $value }}" wire:click="$set('statusFilter', '{{ $value }}')"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $statusFilter === (string) $value ? 'border-ink bg-ink text-white' : 'border-line bg-white text-slate-700 hover:border-ink' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Tabel --}}
    <section class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-line bg-concrete/60">
                    <tr><th class="th">Barang</th><th class="th">Jumlah</th><th class="th hidden md:table-cell">Harga</th><th class="th">Status</th><th class="th text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-line">
                @forelse ($stocks as $stock)
                    <tr wire:key="stock-{{ $stock->id }}" class="transition hover:bg-concrete/40">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($stock->coverUrl())
                                    <img src="{{ $stock->coverUrl() }}" alt="" class="h-12 w-12 shrink-0 rounded-lg border border-line object-cover">
                                @else
                                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-concrete text-slate-400"><x-icon name="cube" size="h-6 w-6" /></span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('stocks.show', $stock) }}" class="block max-w-64 truncate font-semibold text-ink hover:underline hover:decoration-signal hover:decoration-2 hover:underline-offset-4">{{ $stock->name }}</a>
                                    @if ($stock->sku) <span class="tag mt-1">{{ $stock->sku }}</span> @else <span class="text-xs text-slate-400">Tanpa SKU</span> @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="inline-flex items-center rounded-lg border border-line bg-white">
                                <button wire:click="adjust({{ $stock->id }}, -1)" class="grid h-9 w-9 place-items-center rounded-l-lg text-slate-600 transition hover:bg-concrete hover:text-ink" aria-label="Kurangi satu {{ $stock->unit }}"><x-icon name="minus" size="h-4 w-4" /></button>
                                <span class="min-w-20 border-x border-line px-3 text-center text-sm font-bold tabular-nums">{{ $stock->quantity }} <span class="font-medium text-slate-500">{{ $stock->unit }}</span></span>
                                <button wire:click="adjust({{ $stock->id }}, 1)" class="grid h-9 w-9 place-items-center rounded-r-lg text-slate-600 transition hover:bg-concrete hover:text-ink" aria-label="Tambah satu {{ $stock->unit }}"><x-icon name="plus" size="h-4 w-4" /></button>
                            </div>
                        </td>
                        <td class="hidden px-4 py-3 text-sm tabular-nums md:table-cell">{{ $stock->price === null ? '-' : 'Rp '.number_format((float) $stock->price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$stock->status" /></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('stocks.show', $stock) }}" class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-concrete hover:text-ink" title="Lihat detail" aria-label="Lihat detail {{ $stock->name }}"><x-icon name="eye" size="h-4 w-4" /></a>
                                <button wire:click="edit({{ $stock->id }})" class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-concrete hover:text-ink" title="Edit" aria-label="Edit {{ $stock->name }}"><x-icon name="pencil" size="h-4 w-4" /></button>
                                <button wire:click="delete({{ $stock->id }})" wire:confirm="Hapus barang ini? Tindakan ini tidak dapat dibatalkan." class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-red-50 hover:text-red-600" title="Hapus" aria-label="Hapus {{ $stock->name }}"><x-icon name="trash" size="h-4 w-4" /></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-16 text-center">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-xl bg-signal/30 text-ink"><x-icon name="cube" size="h-7 w-7" /></span>
                            @if ($search !== '' || $statusFilter !== '')
                                <p class="mt-4 text-lg font-bold">Tidak ada barang yang cocok</p>
                                <p class="mt-1 text-sm text-slate-600">Ubah kata kunci atau pilih status "Semua".</p>
                            @else
                                <p class="mt-4 text-lg font-bold">Belum ada stok.</p>
                                <p class="mt-1 text-sm text-slate-600">Tambah barang pertama Anda, atau impor sekaligus dari Excel.</p>
                                <button wire:click="create" class="btn btn-primary mt-5"><x-icon name="plus" size="h-4 w-4" /> Tambah barang</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $stocks->links() }}

    {{-- Modal tambah / edit --}}
    @if ($showForm)
        <div class="anim-fade fixed inset-0 z-40 flex items-start justify-center overflow-y-auto bg-ink/60 p-4 sm:items-center" wire:key="form-modal">
            <div class="anim-pop w-full max-w-2xl rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="form-title">
                <div class="flex items-center justify-between border-b border-line px-6 py-4">
                    <h2 id="form-title" class="text-lg font-bold">{{ $editingId ? 'Edit barang' : 'Tambah barang' }}</h2>
                    <button type="button" wire:click="closeForm" class="rounded-md p-1 text-slate-500 hover:bg-concrete hover:text-ink" aria-label="Tutup"><x-icon name="x" /></button>
                </div>
                <form wire:submit="save">
                    <div class="p-6">@include('livewire.stock.partials.fields')</div>
                    <div class="flex justify-end gap-2 border-t border-line bg-concrete/50 px-6 py-4">
                        <button type="button" wire:click="closeForm" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save,cover">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal AI --}}
    @if ($showAi)
        <div class="anim-fade fixed inset-0 z-40 flex items-start justify-center overflow-y-auto bg-ink/60 p-4 sm:items-center" wire:key="ai-modal">
            <div class="anim-pop w-full max-w-3xl rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="ai-title">
                <div class="flex items-center justify-between border-b border-line px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-ink text-signal"><x-icon name="sparkles" size="h-5 w-5" /></span>
                        <div><h2 id="ai-title" class="text-lg font-bold leading-tight">Generate stok dengan AI</h2><p class="text-xs text-slate-600">Ceritakan kebutuhan Anda, periksa hasilnya, lalu simpan yang cocok.</p></div>
                    </div>
                    <button type="button" wire:click="closeAi" class="rounded-md p-1 text-slate-500 hover:bg-concrete hover:text-ink" aria-label="Tutup"><x-icon name="x" /></button>
                </div>

                <div class="p-6">
                    <form wire:submit="generateAi" class="flex flex-wrap gap-3">
                        <input type="text" wire:model="aiPrompt" placeholder="Contoh: perlengkapan kantor untuk tim 10 orang" class="input min-w-64 flex-1">
                        <input type="number" min="1" max="10" wire:model="aiCount" class="input w-24 tabular-nums" aria-label="Jumlah item">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="generateAi">
                            <span wire:loading.remove wire:target="generateAi">Generate</span>
                            <span wire:loading wire:target="generateAi">Menyusun…</span>
                        </button>
                    </form>
                    @error('aiPrompt') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                    @error('aiCount') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                    @if ($aiError)
                        <div class="mt-4 flex items-start gap-3 rounded-lg border border-red-600/25 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"><x-icon name="alert" /> <span>{{ $aiError }}</span></div>
                    @endif

                    @if ($aiItems)
                        <p class="mt-6 text-sm font-semibold">Pilih barang yang ingin disimpan ({{ count($aiSelected) }} dari {{ count($aiItems) }})</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($aiItems as $i => $item)
                                <li wire:key="ai-{{ $i }}">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-line p-4 transition hover:border-ink has-checked:border-ink has-checked:bg-signal/10">
                                        <input type="checkbox" value="{{ $i }}" wire:model="aiSelected" class="mt-1 h-4 w-4 rounded accent-ink">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold">{{ $item['name'] }} @if ($item['sku']) <span class="tag ml-1">{{ $item['sku'] }}</span> @endif</p>
                                            <p class="mt-0.5 text-sm text-slate-600 tabular-nums">{{ $item['quantity'] }} {{ $item['unit'] }}, minimum {{ $item['min_stock'] }}, {{ $item['price'] === null ? 'harga belum diisi' : 'Rp '.number_format($item['price'], 2, ',', '.') }}</p>
                                            @if ($item['description']) <p class="mt-1 text-sm text-slate-500">{{ $item['description'] }}</p> @endif
                                        </div>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="flex justify-end gap-2 border-t border-line bg-concrete/50 px-6 py-4">
                    <button type="button" wire:click="closeAi" class="btn btn-secondary">Tutup</button>
                    @if ($aiItems) <button type="button" wire:click="saveAiItems" class="btn btn-primary">Simpan yang dipilih</button> @endif
                </div>
            </div>
        </div>
    @endif
</div>
