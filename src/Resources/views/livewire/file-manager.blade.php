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

        <x-ufm::header :selectedIds="$selectedIds" :clipboardIds="$clipboardIds" :clipboardAction="$clipboardAction" />

        <!-- Clipboard Bar (Keep inline as it's a global notification bar) -->
        @if(!empty($clipboardIds))
        <div class="mb-6 p-5 bg-blue-100 border border-blue-300 rounded-2xl flex items-center justify-between shadow-xl animate-in slide-in-from-top duration-300 text-blue-900">
            <div class="flex items-center gap-4 text-white">
                <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                    <svg class="w-6 h-6 animate-pulse text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                </div>
                <div>
                    <span class="text-blue-500 text-md font-bold leading-none block">{{ ucfirst($clipboardAction) }} In Progress</span>
                    <span class="text-blue-500 text-sm font-medium">{{ count($clipboardIds) }} items ready in buffer. Navigate to target folder.</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <x-ufm::button wire:click="paste" class="bg-white text-blue-600 px-8 py-3 rounded-xl font-black transition-all transform hover:scale-105 shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    Confirm Paste
                </x-ufm::button>
                <button wire:click="cancelClipboard" class="text-white/80 hover:text-white px-4 py-2 rounded-xl font-bold transition-colors">
                    Cancel
                </button>
            </div>
        </div>
        @endif

        <x-ufm::navbar :breadcrumbs="$breadcrumbs" />

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
            <x-ufm::item-card :item="$item" :selectedIds="$selectedIds" :itemUrl="$this->getUrl($item->path)" />
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

    <x-ufm::context-menu :clipboardIds="$clipboardIds" />
    <x-ufm::preview-modal />
    <x-ufm::upload-progress />
</div>