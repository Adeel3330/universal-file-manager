<div class="p-6 bg-gray-50 min-h-screen" wire:poll.600s
    x-data="{ 
        contextMenu: { show: false, x: 0, y: 0, targetId: null, isFolder: false },
        preview: { show: false, url: '', name: '', type: '' },
        upload: {
            active: false,
            progress: 0,
            speed: '0 KB/s',
            eta: 'Calculating...',
            startTime: null,
            totalSize: 0,
            start(files) {
                this.active = true;
                this.progress = 0;
                this.startTime = Date.now();
                this.totalSize = Array.from(files).reduce((acc, file) => acc + file.size, 0);
            },
            update(progress) {
                this.progress = progress;
                let elapsed = (Date.now() - this.startTime) / 1000;
                if (elapsed > 0) {
                    let uploadedBytes = (progress / 100) * this.totalSize;
                    let speedBps = uploadedBytes / elapsed;
                    
                    if (speedBps > 1024 * 1024) {
                        this.speed = (speedBps / (1024 * 1024)).toFixed(2) + ' MB/s';
                    } else {
                        this.speed = (speedBps / 1024).toFixed(2) + ' KB/s';
                    }
                    
                    let remainingBytes = this.totalSize - uploadedBytes;
                    let etaSeconds = speedBps > 0 ? remainingBytes / speedBps : 0;
                    if (etaSeconds > 60) {
                        this.eta = Math.ceil(etaSeconds / 60) + 'm remaining';
                    } else {
                        this.eta = Math.ceil(etaSeconds) + 's remaining';
                    }
                }
            },
            finish() {
                this.active = false;
            }
        },
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
    x-on:click="closeMenu()"
    x-on:contextmenu="closeMenu()"
    x-on:keydown.escape="preview.show = false"
    x-on:livewire-upload-start="upload.start($event.detail.files)"
    x-on:livewire-upload-finish="upload.finish()"
    x-on:livewire-upload-error="upload.finish()"
    x-on:livewire-upload-progress="upload.update($event.detail.progress)">

    <div class="max-w-7xl mx-auto">
        <!-- Status Messages -->
        @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm font-medium">
            {{ session('message') }}
        </div>
        @endif
        @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-sm font-medium">
            {{ session('error') }}
        </div>
        @endif
        @if (session()->has('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg shadow-sm font-medium">
            {{ session('info') }}
        </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Universal File Manager</h1>
                <p class="text-gray-500 mt-1 font-medium">Advanced Multi-Selection & Bulk Actions Enabled.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Search files..."
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none w-64 transition-all shadow-sm">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button onclick="document.getElementById('file-upload').click()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all shadow-lg active:scale-95 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload
                </button>
                <input type="file" id="file-upload" wire:model="files" multiple class="hidden">

                <!-- Header Action Menu (Three Dots) -->
                <div class="relative" x-data="{ open: false }">
                    <button x-on:click.stop="open = !open" class="p-2.5 hover:bg-white bg-gray-100 rounded-xl transition-all text-gray-700 border border-gray-200 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                    <div x-show="open" x-on:click.away="open = false" x-cloak class="absolute right-0 mt-3 w-64 bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 py-2 overflow-hidden transform origin-top-right transition-all">
                        <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-b border-gray-100 mb-2">Selection & View</div>

                        <button wire:click="selectAll" x-on:click="open = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-3 transition-colors">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Select All Items
                        </button>
                        <button wire:click="unselectAll" x-on:click="open = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear Selection
                        </button>

                        <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-y border-gray-100 mt-2 mb-2">Bulk Operations</div>

                        <button wire:click="copyMedia" x-on:click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            Copy Selection ({{ count($selectedIds) }})
                        </button>
                        <button wire:click="moveMedia" x-on:click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Move Selection ({{ count($selectedIds) }})
                        </button>
                        <button wire:click="downloadMedia" x-on:click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Selected
                        </button>
                        <button wire:click="deleteMedia" wire:confirm="Are you sure you want to delete these items?" x-on:click="open = false" @if(empty($selectedIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Selection ({{ count($selectedIds) }})
                        </button>

                        <div class="h-px bg-gray-100 my-2"></div>

                        <button wire:click="paste" x-on:click="open = false" @if(empty($clipboardIds)) disabled @endif class="w-full text-left px-4 py-2 text-sm font-bold text-green-600 hover:bg-green-50 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="mb-6 p-5 bg-blue-600 border border-blue-700 rounded-2xl flex items-center justify-between shadow-xl animate-in slide-in-from-top duration-300">
            <div class="flex items-center gap-4 text-white">
                <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                </div>
                <div>
                    <span class="font-black text-xl leading-none block">{{ ucfirst($clipboardAction) }} In Progress</span>
                    <span class="text-blue-100 text-sm font-medium">{{ count($clipboardIds) }} items ready in buffer. Navigateto target folder.</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="paste" class="bg-white text-blue-600 px-8 py-3 rounded-xl font-black transition-all transform hover:scale-105 shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    Confirm Paste
                </button>
                <button wire:click="cancelClipboard" class="text-white/80 hover:text-white px-4 py-2 rounded-xl font-bold transition-colors">
                    Cancel
                </button>
            </div>
        </div>
        @endif

        <!-- Breadcrumbs & Actions -->
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
                    <input type="text" wire:model="newFolderName" placeholder="New folder name"
                        class="px-4 py-2 text-sm border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 transition-all">
                    <button type="submit" class="bg-gray-100 text-gray-700 hover:bg-blue-600 hover:text-white p-2.5 rounded-xl transition-all shadow-sm" title="Create Folder">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- File List / Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8"
            x-data="{ dragging: false }"
            x-on:dragover.prevent="dragging = true"
            x-on:dragleave.prevent="dragging = false"
            x-on:drop.prevent.stop="dragging = false; if($event.dataTransfer.files.length > 0) $wire.upload('files', $event.dataTransfer.files)">

            <div x-show="dragging" class="fixed inset-0 z-50 flex items-center justify-center bg-blue-600/10 backdrop-blur-sm pointer-events-none transition-all">
                <div class="bg-white p-10 rounded-3xl shadow-2xl border-4 border-dashed border-blue-500 animate-bounce">
                    <p class="text-2xl font-black text-blue-600">Drop files to upload instantly</p>
                </div>
            </div>

            @forelse($mediaItems as $item)
            @php
            $itemUrl = $this->getUrl($item->path);
            @endphp
            <div wire:key="media-{{ $item->id }}"
                x-on:click.stop="$wire.selectMedia({{ $item->id }})"
                x-on:dblclick.stop="{{ $item->is_folder ? '$wire.navigateTo('.$item->id.')' : ( $item->is_image ? 'openPreview(\''.$itemUrl.'\', \''.$item->name.'\', \'image\')' : '' ) }}"
                x-on:contextmenu.prevent.stop="openMenu($event, {{ $item->id }}, {{ $item->is_folder ? 'true' : 'false' }})"
                class="group relative rounded-2xl p-5 transition-all cursor-pointer overflow-hidden border-2 {{ in_array($item->id, $selectedIds) ? 'bg-blue-50 border-blue-400 shadow-lg ring-2 ring-blue-100' : 'bg-white border-transparent hover:border-gray-100 hover:shadow-md' }}">

                <div class="flex flex-col items-center">
                    <div class="w-full aspect-square flex items-center justify-center mb-4 bg-gray-50 rounded-2xl transition-all group-hover:scale-110 group-hover:rotate-1 shadow-inner relative overflow-hidden">
                        @if($item->is_folder)
                        <svg class="w-20 h-20 text-yellow-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                        </svg>
                        @elseif($item->is_image)
                        <div class="w-full h-full relative group/img">
                            <img src="{{ $itemUrl }}" alt="{{ $item->name }}" class="w-full h-full object-cover rounded-2xl shadow-sm transition-transform duration-500 group-hover/img:scale-110">
                            <div class="absolute inset-0 bg-blue-600/0 group-hover/img:bg-blue-600/10 transition-all rounded-2xl"></div>
                        </div>
                        @else
                        <svg class="w-20 h-20 text-blue-400 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        @endif
                    </div>

                    <span class="w-full text-center text-sm font-bold text-gray-800 truncate px-2" title="{{ $item->name }}">
                        {{ $item->name }}
                    </span>
                    @if(!$item->is_folder)
                    <span class="text-[10px] font-black text-gray-400 mt-1 uppercase tracking-tighter bg-gray-100 px-2 py-0.5 rounded-full">
                        {{ number_format($item->size / 1024, 1) }} KB
                    </span>
                    @endif
                </div>

                @if(in_array($item->id, $selectedIds))
                <div class="absolute top-3 right-3 animate-in zoom-in duration-200">
                    <div class="bg-blue-600 rounded-full p-2 text-white shadow-xl ring-4 ring-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-full py-24 flex flex-col items-center justify-center text-gray-300 border-4 border-dashed border-gray-100 rounded-3xl">
                <svg class="w-24 h-24 mb-6 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <p class="text-2xl font-black text-gray-400">Empty Sanctuary</p>
                <button onclick="document.getElementById('file-upload').click()" class="mt-4 text-blue-600 font-bold hover:underline decoration-2 underline-offset-4 transition-all">Start your collection</button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Context Menu -->
    <div x-show="contextMenu.show"
        x-on:click.away="closeMenu()"
        x-cloak
        :style="`position: fixed; left: ${contextMenu.x}px; top: ${contextMenu.y}px;`"
        class="bg-white/95 backdrop-blur-xl border border-white/20 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] z-[100] py-2 w-72 overflow-hidden transform scale-100 transition-all border border-gray-200">

        <div class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 border-b border-gray-100 mb-2">Advanced Options</div>

        <button x-on:click="$wire.copyMedia(contextMenu.targetId); closeMenu()" class="w-full text-left px-5 py-3 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-4 transition-all">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
            Copy Item
        </button>
        <button x-on:click="$wire.moveMedia(contextMenu.targetId); closeMenu()" class="w-full text-left px-5 py-3 text-sm font-bold text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 flex items-center gap-4 transition-all">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Move Item
        </button>
        <button x-on:click="$wire.paste(); closeMenu()" @if(empty($clipboardIds)) disabled @endif class="w-full text-left px-5 py-3 text-sm font-bold text-gray-700 hover:bg-green-50 hover:text-green-600 flex items-center gap-4 transition-all disabled:opacity-20">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Paste Here
        </button>

        <div class="h-px bg-gray-100 my-2"></div>

        <button x-on:click="$wire.downloadMedia(contextMenu.targetId); closeMenu()" x-show="!contextMenu.isFolder" class="w-full text-left px-5 py-3 text-sm font-bold text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-4 transition-all">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download Item
        </button>
        <button x-on:click="$wire.selectMedia(contextMenu.targetId); closeMenu()" class="w-full text-left px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-4 transition-all">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Toggle Selection
        </button>

        <div class="h-px bg-gray-100 my-2"></div>

        <button x-on:click="if(confirm('Are you sure?')) { $wire.deleteMedia(contextMenu.targetId); closeMenu(); }" class="w-full text-left px-5 py-3 text-sm font-black text-red-600 hover:bg-red-50 flex items-center gap-4 transition-all">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Destroy Permanently
        </button>
    </div>

    <!-- Preview Modal -->
    <div x-show="preview.show"
        x-cloak
        class="fixed inset-0 z-[150] flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-6"
        x-on:click="preview.show = false">
        <div class="relative max-w-6xl w-full max-h-full flex flex-col items-center" x-on:click.stop>
            <button x-on:click="preview.show = false" class="absolute -top-14 right-0 text-white hover:text-blue-400 flex items-center gap-3 transition-colors group">
                <span class="text-xs font-black uppercase tracking-[0.2em] opacity-50 group-hover:opacity-100 transition-opacity">Dismiss Preview</span>
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="w-full bg-white rounded-[2.5rem] overflow-hidden shadow-[0_50px_100px_rgba(0,0,0,0.5)] flex flex-col">
                <div class="px-8 py-6 border-b flex justify-between items-center bg-white">
                    <div>
                        <h3 class="font-black text-2xl text-gray-900 leading-none" x-text="preview.name"></h3>
                        <span class="text-gray-400 text-xs font-bold uppercase tracking-widest mt-2 block" x-text="preview.type + ' resource'"></span>
                    </div>
                    <div class="flex gap-3">
                        <template x-if="preview.type === 'image'">
                            <a :href="preview.url" download class="bg-blue-600 text-white px-8 py-3 rounded-2xl text-sm font-black hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all hover:scale-105">Download</a>
                        </template>
                    </div>
                </div>
                <div class="flex-1 flex items-center justify-center p-12 bg-gray-50 min-h-[500px]">
                    <template x-if="preview.type === 'image'">
                        <img :src="preview.url" class="max-w-full max-h-[65vh] object-contain shadow-2xl rounded-3xl border-[12px] border-white transition-opacity duration-500" :alt="preview.name" x-on:load="$el.style.opacity = 1" style="opacity: 0">
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Upload Progress Indicator -->
    <div x-show="upload.active"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform translate-y-10"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-cloak
        class="fixed bottom-8 right-8 z-[200] w-96">
        <div class="bg-gray-900 rounded-3xl shadow-[0_30px_70px_rgba(0,0,0,0.3)] border border-white/5 p-6 flex flex-col gap-6 backdrop-blur-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-500 p-3 rounded-2xl shadow-lg shadow-blue-500/20">
                        <svg class="w-7 h-7 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-white leading-none">Syncing Data</h4>
                        <p class="text-blue-400 text-xs font-bold mt-1 uppercase tracking-widest" x-text="upload.speed + ' • ' + upload.eta"></p>
                    </div>
                </div>
                <span class="text-3xl font-black text-white" x-text="upload.progress + '%'"></span>
            </div>

            <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden p-1 shadow-inner">
                <div class="bg-gradient-to-r from-blue-600 to-blue-400 h-full rounded-full transition-all duration-300 shadow-sm" :style="`width: ${upload.progress}%`"></div>
            </div>

            <div class="flex justify-between items-center text-[10px] uppercase tracking-widest font-black text-gray-500">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-ping"></span>
                    Transfusing Files
                </span>
                <span x-text="upload.progress < 100 ? 'Processing...' : 'Finalizing Stream'"></span>
            </div>
        </div>
    </div>
</div>