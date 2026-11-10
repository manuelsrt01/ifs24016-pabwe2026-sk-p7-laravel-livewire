@props(['message' => null])
@if ($message)
    <div wire:key="toast-{{ md5($message) }}" x-data x-init="setTimeout(() => $wire.$set('notice', null), 4500)"
         role="status" class="anim-pop fixed right-4 top-4 z-50 flex max-w-sm items-start gap-3 rounded-xl border border-green-600/25 bg-white p-4 shadow-lg shadow-ink/10">
        <span class="mt-0.5 text-green-600"><x-icon name="check" /></span>
        <p class="flex-1 text-sm font-medium text-ink">{{ $message }}</p>
        <button type="button" wire:click="$set('notice', null)" class="text-slate-400 transition hover:text-ink" aria-label="Tutup pesan">
            <x-icon name="x" size="h-4 w-4" />
        </button>
    </div>
@endif
