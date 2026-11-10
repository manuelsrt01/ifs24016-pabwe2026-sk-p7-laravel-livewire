@props(['status'])
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $status->badgeClass() }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $status->label() }}
</span>
