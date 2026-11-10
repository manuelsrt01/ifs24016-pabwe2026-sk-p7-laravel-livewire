<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="stock-name" class="label">Nama barang <span class="text-red-600">*</span></label>
        <input id="stock-name" type="text" wire:model="name" class="input" placeholder="Contoh: Kertas A4 80 gsm">
        @error('name') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="stock-sku" class="label">SKU</label>
        <input id="stock-sku" type="text" wire:model="sku" class="input font-mono" placeholder="KRT-A4-80">
        @error('sku') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="stock-unit" class="label">Satuan <span class="text-red-600">*</span></label>
        <input id="stock-unit" type="text" wire:model="unit" class="input" placeholder="pcs, rim, box">
        @error('unit') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="stock-quantity" class="label">Jumlah <span class="text-red-600">*</span></label>
        <input id="stock-quantity" type="number" min="0" wire:model="quantity" class="input tabular-nums">
        @error('quantity') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="stock-min_stock" class="label">Batas minimum <span class="text-red-600">*</span></label>
        <input id="stock-min_stock" type="number" min="0" wire:model="min_stock" class="input tabular-nums">
        @error('min_stock') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1.5 text-xs text-slate-600">Status berubah jadi "Stok menipis" saat jumlah sama dengan atau di bawah angka ini.</p>
    </div>
    <div>
        <label for="stock-price" class="label">Harga satuan (Rp)</label>
        <input id="stock-price" type="number" min="0" step="0.01" wire:model="price" class="input tabular-nums">
        @error('price') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
        <label for="stock-cover" class="label">Gambar cover</label>
        <input id="stock-cover" type="file" accept="image/*" wire:model="cover"
               class="block w-full rounded-lg border border-dashed border-slate-400 bg-concrete/50 p-2.5 text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-ink file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-ink-2">
        <p class="mt-1.5 text-xs text-slate-600" wire:loading wire:target="cover">Mengunggah gambar…</p>
        @error('cover') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
        <label for="stock-description" class="label">Deskripsi <span class="font-normal text-slate-600">(mendukung Markdown)</span></label>
        <textarea id="stock-description" wire:model="description" rows="5" class="input font-mono" placeholder="Tulis **tebal**, daftar dengan tanda minus, dan seterusnya."></textarea>
        @error('description') <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
