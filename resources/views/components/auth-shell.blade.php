@props(['title', 'subtitle' => null])
<div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
    {{-- Panel merek: rak gudang digambar dengan garis grid --}}
    <aside class="relative hidden overflow-hidden bg-ink p-12 text-white lg:flex lg:flex-col lg:justify-between"
           style="background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px); background-size: 56px 56px;">
        <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-md bg-signal text-ink"><x-icon name="cube" size="h-6 w-6" /></span>
            <span class="text-lg font-bold tracking-tight">Stock Inventory</span>
        </div>

        <div>
            <p class="max-w-md text-5xl font-extrabold leading-[1.05] tracking-tight">Tahu persis barang apa yang hampir habis.</p>
            <p class="mt-5 max-w-md text-base leading-relaxed text-slate-300">Catat stok, sesuaikan jumlahnya dengan satu klik, dan lihat status tiap barang berubah otomatis.</p>

            <ul class="mt-10 max-w-md divide-y divide-white/10 border-y border-white/10 text-sm">
                <li class="flex items-center justify-between py-3"><span>Kopi sachet</span><span class="tag border-white/30 bg-transparent text-slate-200">KPS-01</span><span class="rounded-full bg-green-500/15 px-2.5 py-1 text-xs font-semibold text-green-300">Tersedia</span></li>
                <li class="flex items-center justify-between py-3"><span>Gelas kertas</span><span class="tag border-white/30 bg-transparent text-slate-200">GLS-14</span><span class="rounded-full bg-yellow-400/15 px-2.5 py-1 text-xs font-semibold text-yellow-300">Stok menipis</span></li>
                <li class="flex items-center justify-between py-3"><span>Tisu gulung</span><span class="tag border-white/30 bg-transparent text-slate-200">TSU-07</span><span class="rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-semibold text-red-300">Habis</span></li>
            </ul>
        </div>

        <p class="text-sm text-slate-400">Impor dan ekspor Excel, deskripsi Markdown, dan rekomendasi barang dari AI.</p>
    </aside>

    <section class="flex items-center justify-center bg-concrete p-6 sm:p-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <span class="grid h-10 w-10 place-items-center rounded-md bg-signal text-ink"><x-icon name="cube" size="h-6 w-6" /></span>
                <span class="text-lg font-bold tracking-tight text-ink">Stock Inventory</span>
            </div>

            <div class="panel p-7 sm:p-9">
                <h1 class="text-3xl font-extrabold tracking-tight text-ink">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1.5 text-sm text-slate-600">{{ $subtitle }}</p>
                @endif
                <div class="mt-7">{{ $slot }}</div>
            </div>
        </div>
    </section>
</div>
