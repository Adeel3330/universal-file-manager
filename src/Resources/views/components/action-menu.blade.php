@props(['selectedIds' => [], 'clipboardIds' => [], 'clipboardAction' => 'copy'])

<div class="relative" x-data="{ open: false }">
    <x-ufm::button variant="ghost" x-on:click.stop="open = !open">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
    </x-ufm::button>

    <div x-show="open" x-on:click.away="open = false" x-cloak class="absolute right-0 mt-3 w-64 bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 py-2 overflow-hidden transform origin-top-right transition-all">
        <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-b border-gray-100 mb-2">Selection & View</div>

        <x-ufm::button variant="menu" wire:click="selectAll" x-on:click="open = false">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Select All Items
        </x-ufm::button>
        <x-ufm::button variant="menu" wire:click="unselectAll" x-on:click="open = false">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Clear Selection
        </x-ufm::button>

        <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-y border-gray-100 mt-2 mb-2">Bulk Operations</div>

        <x-ufm::button variant="menu" wire:click="copyMedia" x-on:click="open = false" :disabled="empty($selectedIds)" class="hover:bg-blue-50 hover:text-blue-600">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
            Copy Selection ({{ count($selectedIds) }})
        </x-ufm::button>
        <x-ufm::button variant="menu" wire:click="moveMedia" x-on:click="open = false" :disabled="empty($selectedIds)" class="hover:bg-yellow-50 hover:text-yellow-600">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Move Selection ({{ count($selectedIds) }})
        </x-ufm::button>
        <x-ufm::button variant="menu" wire:click="downloadMedia" x-on:click="open = false" :disabled="empty($selectedIds)" class="hover:bg-purple-50 hover:text-purple-600">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download Selected
        </x-ufm::button>
        <x-ufm::button variant="menu" wire:click="deleteMedia" wire:confirm="Are you sure you want to delete these items?" x-on:click="open = false" :disabled="empty($selectedIds)" class="text-red-600 hover:bg-red-50">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete Selection ({{ count($selectedIds) }})
        </x-ufm::button>

        <div class="h-px bg-gray-100 my-2"></div>

        <x-ufm::button variant="menu" wire:click="paste" x-on:click="open = false" :disabled="empty($clipboardIds)" class="text-green-600 hover:bg-green-50">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Paste Here ({{ count($clipboardIds) }})
        </x-ufm::button>
    </div>
</div>