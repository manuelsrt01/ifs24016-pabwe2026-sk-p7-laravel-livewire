<x-auth-shell title="Masuk" subtitle="Gunakan username atau email akun Anda.">
    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="login-username" class="label">Username atau email</label>
            <input id="login-username" type="text" wire:model="username" autocomplete="username" autofocus class="input">
            @error('username') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ show: false }">
            <label for="login-password" class="label">Password</label>
            <div class="relative">
                <input id="login-password" type="password" :type="show ? 'text' : 'password'" wire:model="password" autocomplete="current-password" class="input pr-16">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-slate-500 hover:text-ink" x-text="show ? 'Sembunyi' : 'Tampil'">Tampil</button>
            </div>
            @error('password') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2.5 text-sm text-slate-700">
            <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-line accent-ink"> Tetap masuk di perangkat ini
        </label>

        <button id="login-submit" type="submit" class="btn btn-primary w-full py-3" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memeriksa…</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-ink underline decoration-signal decoration-2 underline-offset-4">Daftar</a></p>
</x-auth-shell>
