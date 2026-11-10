@php
    $rp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
    $hour = (int) now()->format('G');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $total = max($summary['items'], 1);
    $counts = $summary['status_counts'];
    $needs = $counts['low_stock'] + $counts['out_of_stock'];
    $segments = [
        ['available', 'Tersedia', 'bg-green-500', 'text-green-700'],
        ['low_stock', 'Stok menipis', 'bg-yellow-400', 'text-yellow-700'],
        ['out_of_stock', 'Habis', 'bg-red-500', 'text-red-700'],
    ];
@endphp

<div class="space-y-8">
    <x-page-header :title="$greeting.', '.explode(' ', trim($user->name))[0]"
                   :subtitle="$isAdmin ? 'Ringkasan seluruh stok dari semua pengguna. Mode baca saja: Anda tidak dapat mengubah stok pengguna.' : 'Begini kondisi stok barang Anda hari ini.'">
        @if ($isAdmin)
            <a href="{{ route('admin.users') }}" class="btn btn-dark"><x-icon name="users" size="h-4 w-4" /> Lihat pengguna</a>
        @else
            <a href="{{ route('stocks.index') }}" class="btn btn-dark"><x-icon name="cube" size="h-4 w-4" /> Kelola stok</a>
        @endif
    </x-page-header>

    @if ($summary['items'] === 0)
        <div class="panel flex flex-col items-center px-6 py-16 text-center">
            <span class="grid h-14 w-14 place-items-center rounded-xl bg-signal/30 text-ink"><x-icon name="cube" size="h-7 w-7" /></span>
            <h2 class="mt-5 text-xl font-bold">Belum ada barang</h2>
            <p class="mt-1.5 max-w-sm text-sm text-slate-600">
                {{ $isAdmin ? 'Belum ada pengguna yang mencatat barang.' : 'Tambahkan barang pertama Anda, atau impor sekaligus dari file Excel.' }}
            </p>
            @unless ($isAdmin)
                <a href="{{ route('stocks.index') }}" class="btn btn-primary mt-6"><x-icon name="plus" size="h-4 w-4" /> Tambah barang</a>
            @endunless
        </div>
    @else
        {{-- Panel utama: kondisi gudang dalam satu pita --}}
        <section class="panel overflow-hidden">
            <div class="grid lg:grid-cols-[1.4fr_1fr]">
                <div class="p-6 sm:p-8">
                    <h2 class="text-sm font-semibold text-slate-600">Kondisi stok</h2>
                    <p class="mt-2 flex items-baseline gap-3">
                        <span class="text-6xl font-extrabold leading-none tracking-tight tabular-nums">{{ $summary['items'] }}</span>
                        <span class="text-lg font-semibold text-slate-600">jenis barang</span>
                    </p>

                    <div class="mt-7 flex h-4 overflow-hidden rounded-sm bg-concrete" role="img"
                         aria-label="Tersedia {{ $counts['available'] }}, menipis {{ $counts['low_stock'] }}, habis {{ $counts['out_of_stock'] }}">
                        @foreach ($segments as [$key, $label, $bar])
                            @if ($counts[$key] > 0)
                                <div class="{{ $bar }}" style="width: {{ round($counts[$key] / $total * 100, 2) }}%"></div>
                            @endif
                        @endforeach
                    </div>

                    <dl class="mt-5 grid grid-cols-3 gap-4">
                        @foreach ($segments as [$key, $label, $bar, $text])
                            <div>
                                <dt class="flex items-center gap-2 text-sm text-slate-600"><span class="h-2.5 w-2.5 rounded-sm {{ $bar }}"></span>{{ $label }}</dt>
                                <dd class="mt-0.5 text-2xl font-bold tabular-nums {{ $text }}">{{ $counts[$key] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <dl class="grid grid-cols-2 divide-x divide-y divide-line border-t border-line bg-concrete/60 lg:border-l lg:border-t-0">
                    <div class="p-5 sm:p-6"><dt class="text-sm text-slate-600">Total unit</dt><dd class="mt-1 text-2xl font-bold tabular-nums">{{ number_format($summary['units'], 0, ',', '.') }}</dd></div>
                    <div class="border-t-0 p-5 sm:p-6"><dt class="text-sm text-slate-600">Nilai persediaan</dt><dd class="mt-1 text-2xl font-bold tabular-nums">{{ $rp($summary['value']) }}</dd></div>
                    @if ($isAdmin)
                        <div class="p-5 sm:p-6"><dt class="text-sm text-slate-600">Pengguna</dt><dd class="mt-1 text-2xl font-bold tabular-nums">{{ $overview['users'] }}</dd></div>
                        <div class="p-5 sm:p-6"><dt class="text-sm text-slate-600">Member</dt><dd class="mt-1 text-2xl font-bold tabular-nums">{{ $overview['members'] }}</dd></div>
                    @else
                        <div class="p-5 sm:p-6"><dt class="text-sm text-slate-600">Perlu perhatian</dt><dd class="mt-1 text-2xl font-bold tabular-nums {{ $needs > 0 ? 'text-red-700' : '' }}">{{ $needs }}</dd></div>
                        <div class="p-5 sm:p-6"><dt class="text-sm text-slate-600">Rata-rata per barang</dt><dd class="mt-1 text-2xl font-bold tabular-nums">{{ number_format($summary['units'] / $total, 0, ',', '.') }}</dd></div>
                    @endif
                </dl>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Perlu diisi ulang --}}
            <section class="panel">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h2 class="font-bold">Perlu diisi ulang</h2>
                    @if ($summary['attention']->isNotEmpty())
                        <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800 ring-1 ring-inset ring-red-600/25">{{ $needs }} barang</span>
                    @endif
                </div>
                @forelse ($summary['attention'] as $item)
                    <a href="{{ $isAdmin ? route('admin.users.stocks', $item->user_id) : route('stocks.show', $item) }}" wire:key="att-{{ $item->id }}"
                       class="flex items-center gap-4 border-b border-line px-5 py-3.5 transition last:border-0 hover:bg-concrete/60">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $item->name }}</p>
                            <p class="text-xs text-slate-600">@if ($isAdmin){{ $item->user->name }} - @endif Minimum {{ $item->min_stock }} {{ $item->unit }}</p>
                        </div>
                        <span class="text-right"><span class="block text-lg font-bold tabular-nums">{{ $item->quantity }}</span><span class="text-xs text-slate-600">{{ $item->unit }}</span></span>
                        <x-status-badge :status="$item->status" />
                    </a>
                @empty
                    <div class="flex items-center gap-3 px-5 py-8 text-sm text-slate-600">
                        <span class="text-green-600"><x-icon name="check" size="h-6 w-6" /></span>
                        Semua barang masih aman, tidak ada yang menipis atau habis.
                    </div>
                @endforelse
            </section>

            {{-- Terbaru / pengguna teratas --}}
            @if ($isAdmin)
                <section class="panel">
                    <div class="border-b border-line px-5 py-4"><h2 class="font-bold">Pengguna dengan stok terbanyak</h2></div>
                    @foreach ($overview['top_users'] as $row)
                        <a href="{{ route('admin.users.stocks', $row) }}" wire:key="top-{{ $row->id }}" class="flex items-center gap-4 border-b border-line px-5 py-3.5 transition last:border-0 hover:bg-concrete/60">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-ink text-sm font-bold text-signal">{{ strtoupper(mb_substr($row->name, 0, 1)) }}</span>
                            <div class="min-w-0 flex-1"><p class="truncate font-semibold">{{ $row->name }}</p><p class="text-xs text-slate-600">{{ '@'.$row->username }}</p></div>
                            <span class="text-sm font-semibold tabular-nums">{{ $row->stocks_count }} barang</span>
                        </a>
                    @endforeach
                </section>
            @else
                <section class="panel">
                    <div class="flex items-center justify-between border-b border-line px-5 py-4">
                        <h2 class="font-bold">Barang terbaru</h2>
                        <a href="{{ route('stocks.index') }}" class="text-sm font-semibold text-ink underline decoration-signal decoration-2 underline-offset-4">Lihat semua</a>
                    </div>
                    @foreach ($summary['recent'] as $item)
                        <a href="{{ route('stocks.show', $item) }}" wire:key="rec-{{ $item->id }}" class="flex items-center gap-4 border-b border-line px-5 py-3.5 transition last:border-0 hover:bg-concrete/60">
                            <div class="min-w-0 flex-1"><p class="truncate font-semibold">{{ $item->name }}</p><p class="text-xs text-slate-600">{{ $item->sku ?? 'Tanpa SKU' }}</p></div>
                            <span class="text-sm font-semibold tabular-nums">{{ $item->quantity }} {{ $item->unit }}</span>
                        </a>
                    @endforeach
                </section>
            @endif
        </div>

        @if ($isAdmin)
            <section class="panel">
                <div class="border-b border-line px-5 py-4"><h2 class="font-bold">Barang terbaru dari semua pengguna</h2></div>
                @foreach ($summary['recent'] as $item)
                    <div wire:key="rec-{{ $item->id }}" class="flex items-center gap-4 border-b border-line px-5 py-3.5 last:border-0">
                        <div class="min-w-0 flex-1"><p class="truncate font-semibold">{{ $item->name }}</p><p class="text-xs text-slate-600">{{ $item->user->name }}</p></div>
                        <span class="text-sm font-semibold tabular-nums">{{ $item->quantity }} {{ $item->unit }}</span>
                        <x-status-badge :status="$item->status" />
                    </div>
                @endforeach
            </section>
        @endif
    @endif
</div>
