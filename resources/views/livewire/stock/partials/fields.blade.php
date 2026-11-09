<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium">Nama barang *</label>
        <input type="text" wire:model="name" class="w-full rounded border border-slate-300 px-3 py-2">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">SKU</label>
        <input type="text" wire:model="sku" class="w-full rounded border border-slate-300 px-3 py-2">
        @error('sku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Satuan *</label>
        <input type="text" wire:model="unit" class="w-full rounded border border-slate-300 px-3 py-2">
        @error('unit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Jumlah *</label>
        <input type="number" min="0" wire:model="quantity" class="w-full rounded border border-slate-300 px-3 py-2">
        @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Minimum stok *</label>
        <input type="number" min="0" wire:model="min_stock" class="w-full rounded border border-slate-300 px-3 py-2">
        @error('min_stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Harga</label>
        <input type="number" min="0" step="0.01" wire:model="price" class="w-full rounded border border-slate-300 px-3 py-2">
        @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Gambar cover</label>
        <input type="file" accept="image/*" wire:model="cover" class="w-full text-sm">
        @error('cover') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium">Deskripsi (Markdown)</label>
        <textarea wire:model="description" rows="5" class="w-full rounded border border-slate-300 px-3 py-2 font-mono text-sm"></textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
<p class="mt-3 text-xs text-slate-500">Status ditentukan otomatis dari jumlah dan minimum stok.</p>
