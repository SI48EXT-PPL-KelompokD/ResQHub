<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" wire:poll.60s>
    <button type="button" @click="open = !open" :aria-expanded="open" class="relative inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 hover:text-ink">
        <span class="sr-only">Notifikasi{{ $unreadCount ? ", {$unreadCount} belum dibaca" : '' }}</span>
        @include('layouts.partials.nav-icon', ['name' => 'bell', 'class' => 'h-5 w-5'])
        @if ($unreadCount)
            <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-700 px-1 text-[10px] font-bold text-white tabular-nums">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h2 class="text-sm font-bold text-ink">Notifikasi</h2>
            @if ($unreadCount)
                <button type="button" wire:click="markAllRead" class="cursor-pointer text-[13px] font-semibold text-brand-600 hover:underline">Tandai semua dibaca</button>
            @endif
        </div>

        <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
            @forelse ($notifications as $notification)
                <li wire:key="notif-{{ $notification->id }}">
                    <button type="button" wire:click="open('{{ $notification->id }}')" class="block w-full cursor-pointer px-4 py-3 text-left hover:bg-slate-50 {{ $notification->read_at ? '' : 'bg-brand-50' }}">
                        @if (! empty($notification->data['title']))
                            <span class="block text-sm font-semibold text-ink">{{ $notification->data['title'] }}</span>
                        @endif
                        <span class="mt-0.5 block text-[13px] leading-snug text-slate-700">{{ $notification->data['message'] ?? 'Ada pembaruan untuk Anda.' }}</span>
                        <span class="mt-1 block text-xs text-slate-500">{{ $notification->created_at->locale('id')->diffForHumans() }}</span>
                    </button>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-slate-500">Belum ada notifikasi.</li>
            @endforelse
        </ul>
    </div>
</div>
