<x-auth-shell title="Daftar Akun" subtitle="Buat akun untuk mulai mencatat stok barang Anda.">
    <form wire:submit="register" class="space-y-4">
        <div>
            <label for="register-name" class="label">Nama lengkap</label>
            <input id="register-name" type="text" wire:model="name" autocomplete="name" class="input">
            @error('name') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="register-username" class="label">Username</label>
                <input id="register-username" type="text" wire:model="username" autocomplete="username" class="input">
                @error('username') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="register-email" class="label">Email</label>
                <input id="register-email" type="email" wire:model="email" autocomplete="email" class="input">
                @error('email') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="register-password" class="label">Password</label>
                <input id="register-password" type="password" wire:model="password" autocomplete="new-password" class="input">
                @error('password') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-slate-600">Minimal 8 karakter.</p>
            </div>
            <div>
                <label for="register-password_confirmation" class="label">Ulangi password</label>
                <input id="register-password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="input">
            </div>
        </div>

        <button id="register-submit" type="submit" class="btn btn-primary w-full py-3" wire:loading.attr="disabled" wire:target="register">Daftar</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-ink underline decoration-signal decoration-2 underline-offset-4">Masuk</a></p>
</x-auth-shell>
