@props(['clipboardIds' => []])

<div x-show="contextMenu.show"
    x-on:click.away="closeMenu()"
    x-cloak
    :style="`position: fixed; left: ${contextMenu.x}px; top: ${contextMenu.y}px;`"
    class="bg-white/95 backdrop-blur-xl border border-white/20 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] z-[100] py-2 w-72 overflow-hidden transform scale-100 transition-all border border-gray-200">

    <div class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 border-b border-gray-100 mb-2">Advanced Options</div>

    <x-ufm::button variant="menu" x-on:click="$wire.copyMedia(contextMenu.targetId); closeMenu()" class="hover:bg-blue-50 hover:text-blue-600">
        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
        </svg>
        Copy Item
    </x-ufm::button>
    <x-ufm::button variant="menu" x-on:click="$wire.moveMedia(contextMenu.targetId); closeMenu()" class="hover:bg-yellow-50 hover:text-yellow-600">
        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        Move Item
    </x-ufm::button>
    <x-ufm::button variant="menu" x-on:click="$wire.paste(); closeMenu()" :disabled="empty($clipboardIds)" class="hover:bg-green-50 hover:text-green-600">
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        Paste Here
    </x-ufm::button>

    <div class="h-px bg-gray-100 my-2"></div>

    <x-ufm::button variant="menu" x-on:click="$wire.downloadMedia(contextMenu.targetId); closeMenu()" x-show="!contextMenu.isFolder" class="hover:bg-purple-50 hover:text-purple-600">
        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Download Item
    </x-ufm::button>
    <x-ufm::button variant="menu" x-on:click="$wire.selectMedia(contextMenu.targetId); closeMenu()" class="hover:bg-gray-50 text-gray-700">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Toggle Selection
    </x-ufm::button>

    <div class="h-px bg-gray-100 my-2"></div>

    <x-ufm::button variant="menu" x-on:click="if(confirm('Are you sure?')) { $wire.deleteMedia(contextMenu.targetId); closeMenu(); }" class="text-red-600 hover:bg-red-50 font-black">
        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Destroy Permanently
    </x-ufm::button>
</div>