<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Universal File Manager</h1>
                <p class="text-gray-500 mt-1">Manage your files and folders with ease.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Search files..."
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none w-64 transition-all">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button onclick="document.getElementById('file-upload').click()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload
                </button>
                <input type="file" id="file-upload" wire:model="files" multiple class="hidden">
            </div>
        </div>

        <!-- Breadcrumbs & Actions -->
        <div class="flex items-center justify-between mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <nav class="flex items-center text-sm font-medium text-gray-500 overflow-x-auto whitespace-nowrap">
                <button wire:click="navigateTo(null)" class="hover:text-blue-600 flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Root
                </button>
                @foreach($breadcrumbs as $breadcrumb)
                <svg class="w-4 h-4 mx-2 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <button wire:click="navigateTo({{ $breadcrumb->id }})" class="hover:text-blue-600">
                    {{ $breadcrumb->name }}
                </button>
                @endforeach
            </nav>

            <div class="flex items-center gap-2 ml-4">
                <form wire:submit.prevent="createFolder" class="flex items-center gap-2">
                    <input type="text" wire:model="newFolderName" placeholder="New folder name"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-md focus:ring-1 focus:ring-blue-500 outline-none">
                    <button type="submit" class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-md transition-colors" title="Create Folder">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- File List / Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6"
            x-data="{ dragging: false }"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="dragging = false; $wire.upload('files', $event.dataTransfer.files)">

            <div x-show="dragging" class="fixed inset-0 z-50 flex items-center justify-center bg-blue-600 bg-opacity-10 pointer-events-none">
                <div class="bg-white p-8 rounded-2xl shadow-xl border-2 border-dashed border-blue-500">
                    <p class="text-xl font-semibold text-blue-600">Drop files to upload</p>
                </div>
            </div>

            @forelse($mediaItems as $item)
            <div wire:key="media-{{ $item->id }}"
                class="group relative bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition-all hover:shadow-md hover:border-blue-200 cursor-pointer overflow-hidden">

                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                    <button wire:click="deleteMedia({{ $item->id }})" wire:confirm="Are you sure you want to delete this?"
                        class="p-1.5 bg-white text-red-500 hover:bg-red-50 rounded-lg shadow-sm border border-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>

                <div wire:click="{{ $item->is_folder ? 'navigateTo('.$item->id.')' : '' }}" class="flex flex-col items-center">
                    <div class="w-full aspect-square flex items-center justify-center mb-3 bg-gray-50 rounded-lg transition-transform group-hover:scale-105">
                        @if($item->is_folder)
                        <svg class="w-16 h-16 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                        </svg>
                        @elseif($item->is_image)
                        <img src="{{ $item->url }}" alt="{{ $item->name }}" class="w-full h-full object-cover rounded-lg">
                        @else
                        <svg class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        @endif
                    </div>

                    <span class="w-full text-center text-sm font-medium text-gray-700 truncate" title="{{ $item->name }}">
                        {{ $item->name }}
                    </span>
                    @if(!$item->is_folder)
                    <span class="text-xs text-gray-400 mt-1">
                        {{ number_format($item->size / 1024, 1) }} KB
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full py-20 flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <p class="text-lg">No files or folders found here.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Upload Progress -->
    <div wire:loading wire:target="files" class="fixed bottom-6 right-6 z-50">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Uploading files...</span>
        </div>
    </div>
</div>