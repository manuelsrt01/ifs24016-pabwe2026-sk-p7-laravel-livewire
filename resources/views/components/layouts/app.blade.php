<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? 'Stock Inventory') }} - {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-concrete font-sans text-ink antialiased">
@auth
    @php
        $me = auth()->user();
        $nav = [['dashboard', 'Dashboard', 'home', 'dashboard']];
        $nav[] = $me->isAdmin()
            ? ['admin.users', 'Pengguna', 'users', 'admin.*']
            : ['stocks.index', 'Stok barang', 'cube', 'stocks.*'];
        $nav[] = ['profile', 'Profil', 'user', 'profile'];
    @endphp

    <div x-data="{ open: false }" class="min-h-screen lg:flex">
        {{-- Bilah atas (ponsel) --}}
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/10 bg-ink px-4 py-3 text-white lg:hidden">
            <div class="flex items-center gap-2.5">
                <span class="grid h-8 w-8 place-items-center rounded-md bg-signal text-ink"><x-icon name="cube" size="h-5 w-5" /></span>
                <span class="font-bold tracking-tight">Stock Inventory</span>
            </div>
            <button type="button" @click="open = true" class="rounded-md p-1.5 hover:bg-white/10" aria-label="Buka menu"><x-icon name="menu" size="h-6 w-6" /></button>
        </header>

        <div x-cloak x-show="open" @click="open = false" class="anim-fade fixed inset-0 z-30 bg-ink/60 lg:hidden"></div>

        <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink text-slate-200 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex items-center justify-between px-5 py-5">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-md bg-signal text-ink"><x-icon name="cube" size="h-5 w-5" /></span>
                    <span class="text-lg font-bold tracking-tight text-white">Stock Inventory</span>
                </div>
                <button type="button" @click="open = false" class="rounded-md p-1 hover:bg-white/10 lg:hidden" aria-label="Tutup menu"><x-icon name="x" size="h-5 w-5" /></button>
            </div>

            <nav class="mt-2 flex-1 space-y-1 px-3 text-sm font-medium">
                @foreach ($nav as [$route, $label, $icon, $match])
                    @php $active = request()->routeIs($match); @endphp
                    <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                       class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ $active ? 'bg-ink-3 text-white' : 'hover:bg-ink-2 hover:text-white' }}">
                        @if ($active)<span class="absolute inset-y-2 left-0 w-1 rounded-r bg-signal"></span>@endif
                        <x-icon :name="$icon" /> {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="mb-3 flex items-center gap-3">
                    @if ($me->photoUrl())
                        <img src="{{ $me->photoUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover ring-2 ring-white/20">
                    @else
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-signal text-sm font-bold text-ink">{{ strtoupper(mb_substr($me->name, 0, 1)) }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $me->name }}</p>
                        <p class="text-xs text-slate-400">{{ $me->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" id="logout-submit" class="flex w-full items-center justify-center gap-2 rounded-lg border border-white/15 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                        <x-icon name="logout" size="h-4 w-4" /> Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <div class="mx-auto max-w-6xl p-4 sm:p-6 lg:p-10">{{ $slot }}</div>
        </main>
    </div>
@else
    <main class="min-h-screen">{{ $slot }}</main>
@endauth
</body>
</html>
