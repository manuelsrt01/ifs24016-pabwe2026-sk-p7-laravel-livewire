<div class="w-full max-w-sm rounded-lg bg-white p-6 shadow">
    <h1 class="mb-1 text-xl font-bold">Masuk</h1>
    <p class="mb-5 text-sm text-slate-500">Stock Inventory</p>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="login-username" class="mb-1 block text-sm font-medium">Username atau Email</label>
            <input id="login-username" type="text" wire:model="username" autocomplete="username"
                   class="w-full rounded border border-slate-300 px-3 py-2">
            @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="login-password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="login-password" type="password" wire:model="password" autocomplete="current-password"
                   class="w-full rounded border border-slate-300 px-3 py-2">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="remember"> Ingat saya</label>
        <button id="login-submit" type="submit" class="w-full rounded bg-slate-900 px-3 py-2 font-medium text-white hover:bg-slate-700">Masuk</button>
    </form>

    <p class="mt-4 text-center text-sm">Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 underline">Daftar</a></p>
</div>
