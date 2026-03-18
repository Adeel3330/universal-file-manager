@props(['breadcrumbs' => []])

<div class="flex items-center justify-between mb-8 bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
    <nav class="flex items-center text-sm font-bold text-gray-500 overflow-x-auto whitespace-nowrap scrollbar-hide">
        <button wire:click="navigateTo(null)" class="hover:text-blue-600 flex items-center gap-2 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Root
        </button>
        @foreach($breadcrumbs as $breadcrumb)
        <svg class="w-4 h-4 mx-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <button wire:click="navigateTo({{ $breadcrumb->id }})" class="hover:text-blue-600 transition-colors">
            {{ $breadcrumb->name }}
        </button>
        @endforeach
    </nav>

    <div class="flex items-center gap-3 ml-4">
        <form wire:submit.prevent="createFolder" class="flex items-center gap-2">
            <x-ufm::input wire:model="newFolderName" placeholder="New folder name" class="w-48" />
            <x-ufm::button type="submit" variant="secondary" class="p-2.5" title="Create Folder">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
            </x-ufm::button>
        </form>
    </div>
</div>