@props(['selectedIds' => [], 'clipboardIds' => [], 'clipboardAction' => 'copy'])

<div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Universal File Manager</h1>
    </div>

    <div class="flex items-center gap-3">
        <x-ufm::input wire:model.live="search" placeholder="Search files...">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </x-ufm::input>

        <x-ufm::button class="text-white" onclick="document.getElementById('file-upload').click()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Upload
        </x-ufm::button>
        <input type="file" id="file-upload" wire:model="files" multiple class="hidden">

        <x-ufm::action-menu :selectedIds="$selectedIds" :clipboardIds="$clipboardIds" :clipboardAction="$clipboardAction" />
    </div>
</div>