<div class="p-6 bg-gray-50 min-h-screen" wire:poll.600s
    x-data="{ 
        contextMenu: { show: false, x: 0, y: 0, targetId: null, isFolder: false },
        preview: { show: false, url: '', name: '', type: '' },
        closeMenu() { this.contextMenu.show = false },
        openMenu(e, id, isFolder) {
            e.preventDefault();
            this.contextMenu.show = true;
            this.contextMenu.x = e.clientX;
            this.contextMenu.y = e.clientY;
            this.contextMenu.targetId = id;
            this.contextMenu.isFolder = isFolder;
            if (!$wire.selectedIds.includes(id.toString()) && !$wire.selectedIds.includes(id)) {
                $wire.selectMedia(id);
            }
        },
        openPreview(url, name, type) {
            this.preview.url = url;
            this.preview.name = name;
            this.preview.type = type;
            this.preview.show = true;
        }
    }"
    @click="closeMenu()"
    @contextmenu="closeMenu()"
    @keydown.escape="preview.show = false">

    <div class="max-w-7xl mx-auto">
        <!-- Status Messages -->
        @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm">
            {{ session('message') }}
        </div>
        @endif
        @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-sm">
            {{ session('error') }}
        </div>
        @endif
        @if (session()->has('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg shadow-sm">
            {{ session('info') }}
        </div>
        @endif

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

                <!-- Header Action Menu (Three Dots) -->
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" class="p-2 hover:bg-white bg-gray-100 rounded-lg transition-colors text-gray-700 border border-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white border border-gray-100 rounded-xl shadow-xl z-50 py-1 overflow-hidden">
                        <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100 mb-1">Bulk Actions</div>

                        <button onclick="document.getElementById('file-upload').click()" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Upload Files
                        </button>

                        <button wire:click="copyMedia" @click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            Copy Selected ({{ count($selectedIds) }})
                        </button>
                        <button wire:click="moveMedia" @click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Move Selected ({{ count($selectedIds) }})
                        </button>
                        <button wire:click="downloadMedia" @click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Selected
                        </button>
                        <button wire:click="deleteMedia" wire:confirm="Are you sure you want to delete the selected items?" @click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Selected
                        </button>

                        <div class="h-px bg-gray-100 my-1"></div>

                        <button wire:click="paste" @click="open = false" @if(empty($clipboardIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Paste Here ({{ count($clipboardIds) }})
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clipboard Bar -->
        @if(!empty($clipboardIds))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3 text-blue-800">
                <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
                <span class="font-medium text-lg">
                    {{ ucfirst($clipboardAction) }}ing <span class="font-bold underline">{{ count($clipboardIds) }} item(s)</span>. Navigate and click Paste.
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="paste" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition-all transform hover:scale-105 shadow-md">
                    Paste Here
                </button>
                <button wire:click="cancelClipboard" class="bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
            </div>
        </div>
        @endif

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
            @drop.prevent.stop="dragging = false; if($event.dataTransfer.files.length > 0) $wire.upload('files', $event.dataTransfer.files)">

            <div x-show="dragging" class="fixed inset-0 z-50 flex items-center justify-center bg-blue-600 bg-opacity-10 pointer-events-none">
                <div class="bg-white p-8 rounded-2xl shadow-xl border-2 border-dashed border-blue-500">
                    <p class="text-xl font-semibold text-blue-600">Drop files to upload</p>
                </div>
            </div>

            @forelse($mediaItems as $item)
            <div wire:key="media-{{ $item->id }}"
                @click.stop="$wire.selectMedia({{ $item->id }})"
                @dblclick.stop="{{ $item->is_folder ? '$wire.navigateTo('.$item->id.')' : ( $item->is_image ? 'openPreview(\''.$item->url.'\', \''.$item->name.'\', \'image\')' : '' ) }}"
                @contextmenu.prevent="openMenu($event, {{ $item->id }}, {{ $item->is_folder ? 'true' : 'false' }})"
                class="group relative rounded-xl p-4 transition-all cursor-pointer overflow-hidden border-2 {{ in_array($item->id, $selectedIds) ? 'bg-blue-50 border-blue-400 shadow-md' : 'bg-white border-transparent hover:border-gray-100 hover:shadow-sm' }}">

                <div class="flex flex-col items-center">
                    <div class="w-full aspect-square flex items-center justify-center mb-3 bg-gray-50 rounded-lg transition-transform group-hover:scale-105">
                        @if($item->is_folder)
                        <svg class="w-16 h-16 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                        </svg>
                        @elseif($item->is_image)
                        <div class="w-full h-full relative group">
                            <img src="{{ $item->url }}" alt="{{ $item->name }}" class="w-full h-full object-cover rounded-lg shadow-inner">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg"></div>
                        </div>
                        @else
                        <svg class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        @endif
                    </div>

                    <span class="w-full text-center text-sm font-medium text-gray-700 truncate px-1" title="{{ $item->name }}">
                        {{ $item->name }}
                    </span>
                    @if(!$item->is_folder)
                    <span class="text-xs text-gray-400 mt-1">
                        {{ number_format($item->size / 1024, 1) }} KB
                    </span>
                    @endif
                </div>

                @if(in_array($item->id, $selectedIds))
                <div class="absolute top-2 right-2">
                    <div class="bg-blue-600 rounded-full p-1.5 text-white shadow-md transform scale-110">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-full py-20 flex flex-col items-center justify-center text-gray-400">
                <svg class="w-20 h-20 mb-4 opacity-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <p class="text-xl font-medium">This folder is empty</p>
                <button onclick="document.getElementById('file-upload').click()" class="mt-4 text-blue-600 hover:underline">Upload some files</button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Context Menu -->
    <div x-show="contextMenu.show"
        @click.away="closeMenu()"
        x-cloak
        :style="`position: fixed; left: ${contextMenu.x}px; top: ${contextMenu.y}px;`"
        class="bg-white border border-gray-100 rounded-xl shadow-2xl z-[100] py-2 w-64 overflow-hidden border border-gray-200">

        <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100 mb-1">Item Actions</div>

        <button wire:click="copyMedia(contextMenu.targetId)" @click="closeMenu()" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-3 transition-colors">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
            Copy
        </button>
        <button wire:click="moveMedia(contextMenu.targetId)" @click="closeMenu()" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 flex items-center gap-3 transition-colors">
            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Move
        </button>
        <button wire:click="paste" @click="closeMenu()" @if(empty($clipboardIds)) disabled @endif class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 hover:text-green-600 flex items-center gap-3 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Paste Here
        </button>

        <div class="h-px bg-gray-100 my-1"></div>

        <button wire:click="downloadMedia(contextMenu.targetId)" @click="closeMenu()" x-show="!contextMenu.isFolder" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-3 transition-colors">
            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download
        </button>
        <button wire:click="selectMedia(contextMenu.targetId)" @click="closeMenu()" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Select/Deselect
        </button>

        <div class="h-px bg-gray-100 my-1"></div>

        <button wire:click="deleteMedia(contextMenu.targetId)" wire:confirm="Are you sure you want to delete this?" @click="closeMenu()" class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
        </button>
    </div>

    <!-- Preview Modal -->
    <div x-show="preview.show"
        x-cloak
        class="fixed inset-0 z-[150] flex items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm p-4"
        @click="preview.show = false">
        <div class="relative max-w-5xl w-full max-h-full flex flex-col items-center" @click.stop>
            <button @click="preview.show = false" class="absolute -top-12 right-0 text-white hover:text-gray-300 flex items-center gap-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-sm font-bold uppercase tracking-widest">Close</span>
            </button>

            <div class="w-full bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col">
                <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-900 truncate" x-text="preview.name"></h3>
                    <div class="flex gap-2">
                        <template x-if="preview.type === 'image'">
                            <a :href="preview.url" download class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-blue-700">Download Original</a>
                        </template>
                    </div>
                </div>
                <div class="flex-1 flex items-center justify-center p-8 bg-gray-100 min-h-[400px]">
                    <template x-if="preview.type === 'image'">
                        <img :src="preview.url" class="max-w-full max-h-[70vh] object-contain shadow-lg rounded-lg border-4 border-white" :alt="preview.name">
                    </template>
                </div>
            </div>
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