<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Profil</h1>
    @if ($notice) <div class="rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ $notice }}</div> @endif

    <section class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-semibold">Foto Profil</h2>
        <div class="flex items-center gap-4">
            @if ($user->photoUrl())
                <img src="{{ $user->photoUrl() }}" alt="Foto profil" class="h-16 w-16 rounded-full object-cover">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-200 text-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
            <form wire:submit="savePhoto" class="flex items-center gap-2 text-sm">
                <input type="file" accept="image/*" wire:model="photo">
                <button type="submit" class="rounded bg-slate-900 px-3 py-1.5 text-white">Unggah</button>
            </form>
        </div>
        @error('photo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </section>

    <section class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-semibold">Informasi Profil</h2>
        <form wire:submit="saveProfile" class="space-y-3 text-sm">
            @foreach ([['name','Nama'],['username','Username'],['email','Email']] as [$f,$l])
                <div>
                    <label class="mb-1 block font-medium">{{ $l }}</label>
                    <input type="text" wire:model="{{ $f }}" class="w-full rounded border border-slate-300 px-3 py-2">
                    @error($f) <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
            <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-white">Simpan</button>
        </form>
    </section>

    <section class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-semibold">Ubah Password</h2>
        <form wire:submit="savePassword" class="space-y-3 text-sm">
            @foreach ([['current_password','Password saat ini'],['password','Password baru'],['password_confirmation','Konfirmasi password baru']] as [$f,$l])
                <div>
                    <label class="mb-1 block font-medium">{{ $l }}</label>
                    <input type="password" wire:model="{{ $f }}" class="w-full rounded border border-slate-300 px-3 py-2">
                    @if ($f !== 'password_confirmation') @error($f) <p class="mt-1 text-red-600">{{ $message }}</p> @enderror @endif
                </div>
            @endforeach
            <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-white">Ubah Password</button>
        </form>
    </section>
</div>
