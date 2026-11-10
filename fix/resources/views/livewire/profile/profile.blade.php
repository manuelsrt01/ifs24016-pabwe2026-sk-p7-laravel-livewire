<div class="mx-auto max-w-4xl space-y-6">
    <x-toast :message="$notice" />

    <x-page-header title="Profil" subtitle="Atur data akun, password, dan foto profil Anda." />

    <section class="panel p-6">
        <div class="flex flex-wrap items-center gap-6">
            @if ($user->photoUrl())
                <img src="{{ $user->photoUrl() }}" alt="Foto profil" class="h-24 w-24 rounded-full border-4 border-signal object-cover">
            @else
                <span class="grid h-24 w-24 place-items-center rounded-full border-4 border-signal bg-ink text-4xl font-extrabold text-signal">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            @endif
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold tracking-tight">{{ $user->name }}</h2>
                <p class="text-sm text-slate-600">{{ '@'.$user->username }}, {{ $user->role }}</p>

                <form wire:submit="savePhoto" class="mt-4 flex flex-wrap items-center gap-2">
                    <input type="file" accept="image/*" wire:model="photo" aria-label="Pilih foto profil"
                           class="max-w-64 text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-concrete file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink hover:file:bg-line">
                    <button type="submit" class="btn btn-dark" wire:loading.attr="disabled" wire:target="photo,savePhoto">Unggah foto</button>
                </form>
                @error('photo') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-slate-600">JPG atau PNG, maks. 2 MB.</p>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="panel">
            <div class="border-b border-line px-6 py-4"><h2 class="font-bold">Informasi akun</h2></div>
            <form wire:submit="saveProfile" class="space-y-4 p-6">
                @foreach ([['name', 'Nama lengkap'], ['username', 'Username'], ['email', 'Email']] as [$f, $l])
                    <div>
                        <label for="profile-{{ $f }}" class="label">{{ $l }}</label>
                        <input id="profile-{{ $f }}" type="text" wire:model="{{ $f }}" class="input">
                        @error($f) <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            </form>
        </section>

        <section class="panel">
            <div class="border-b border-line px-6 py-4"><h2 class="font-bold">Ubah password</h2></div>
            <form wire:submit="savePassword" class="space-y-4 p-6">
                @foreach ([['current_password', 'Password saat ini'], ['password', 'Password baru'], ['password_confirmation', 'Ulangi password baru']] as [$f, $l])
                    <div>
                        <label for="profile-{{ $f }}" class="label">{{ $l }}</label>
                        <input id="profile-{{ $f }}" type="password" wire:model="{{ $f }}" class="input">
                        @if ($f !== 'password_confirmation') @error($f) <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror @endif
                    </div>
                @endforeach
                <p class="text-xs text-slate-600">Minimal 8 karakter.</p>
                <button type="submit" class="btn btn-primary">Ubah password</button>
            </form>
        </section>
    </div>
</div>
