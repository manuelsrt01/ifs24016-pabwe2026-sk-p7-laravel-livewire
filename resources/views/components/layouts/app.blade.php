<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? 'Stock Inventory') }} - {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
@auth
    <div class="flex min-h-screen">
        <aside class="w-60 shrink-0 bg-slate-900 text-slate-100 flex flex-col">
            <div class="px-5 py-5 text-lg font-bold">📦 Stock Inventory</div>
            <nav class="flex-1 px-3 space-y-1 text-sm">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.users') }}" class="block rounded px-3 py-2 hover:bg-slate-700 {{ request()->routeIs('admin.*') ? 'bg-slate-700' : '' }}">Pengguna</a>
                @else
                    <a href="{{ route('stocks.index') }}" class="block rounded px-3 py-2 hover:bg-slate-700 {{ request()->routeIs('stocks.*') ? 'bg-slate-700' : '' }}">Stok Barang</a>
                @endif
                <a href="{{ route('profile') }}" class="block rounded px-3 py-2 hover:bg-slate-700 {{ request()->routeIs('profile') ? 'bg-slate-700' : '' }}">Profil</a>
            </nav>
            <div class="p-4 border-t border-slate-700 text-sm">
                <div class="flex items-center gap-2 mb-3">
                    @if (auth()->user()->photoUrl())
                        <img src="{{ auth()->user()->photoUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover">
                    @endif
                    <div>
                        <div class="font-medium">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-400">{{ auth()->user()->role }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" id="logout-submit" class="w-full rounded bg-slate-700 px-3 py-2 hover:bg-slate-600">Keluar</button>
                </form>
            </div>
        </aside>
        <main class="flex-1 p-6 overflow-x-auto">{{ $slot }}</main>
    </div>
@else
    <main class="min-h-screen flex items-center justify-center p-6">{{ $slot }}</main>
@endauth
</body>
</html>
