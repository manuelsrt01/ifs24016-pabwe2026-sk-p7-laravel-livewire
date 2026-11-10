<div class="space-y-6">
    <x-page-header title="Pengguna" subtitle="Pilih pengguna untuk melihat stok miliknya. Halaman ini hanya untuk dibaca." />

    <div class="relative sm:w-96">
        <span class="pointer-events-none absolute inset-y-0 left-3.5 grid place-items-center text-slate-400"><x-icon name="search" size="h-4 w-4" /></span>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, username, atau email" class="input pl-10">
    </div>

    <section class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-line bg-concrete/60">
                    <tr><th class="th">Pengguna</th><th class="th hidden md:table-cell">Email</th><th class="th">Role</th><th class="th">Barang</th><th class="th text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-line">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="transition hover:bg-concrete/40">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($user->photoUrl())
                                    <img src="{{ $user->photoUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-ink text-sm font-bold text-signal">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                @endif
                                <div class="min-w-0"><p class="truncate font-semibold">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ '@'.$user->username }}</p></div>
                            </div>
                        </td>
                        <td class="hidden px-4 py-3 text-sm text-slate-600 md:table-cell">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->isAdmin() ? 'bg-signal/40 text-ink ring-1 ring-inset ring-signal-deep/50' : 'bg-concrete text-slate-700 ring-1 ring-inset ring-line' }}">{{ $user->role }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold tabular-nums">{{ $user->stocks_count }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.users.stocks', $user) }}" class="btn btn-secondary px-3 py-2"><x-icon name="eye" size="h-4 w-4" /> Lihat stok</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-14 text-center"><p class="font-bold">Tidak ada pengguna yang cocok</p><p class="mt-1 text-sm text-slate-600">Coba kata kunci lain.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $users->links() }}
</div>
