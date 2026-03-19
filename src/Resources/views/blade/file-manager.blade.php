<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Universal File Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-50">
    <div class="p-6 bg-gray-50 min-h-screen"
        x-data="fileManager()"
        x-init="init()"
        x-on:keydown.escape="preview.show = false">

        <div class="max-w-7xl mx-auto">
            <!-- Status Messages -->
            <template x-if="message">
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm font-medium" x-text="message"></div>
            </template>
            <template x-if="error">
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-sm font-medium" x-text="error"></div>
            </template>

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Universal File Manager</h1>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <input x-model="search" @input.debounce.300ms="loadMedia()" placeholder="Search files..."
                            class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all shadow-sm w-full font-medium">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Upload -->
                    <button @click="$refs.fileUpload.click()" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 shadow-lg active:scale-95 font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-2 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Upload
                    </button>
                    <input type="file" x-ref="fileUpload" @change="uploadFiles($event)" multiple class="hidden">

                    <!-- Action Menu -->
                    <div class="relative" x-data="{ menuOpen: false }">
                        <button @click.stop="menuOpen = !menuOpen" class="p-2.5 rounded-xl hover:bg-white bg-gray-100 text-gray-700 border border-gray-200 shadow-sm active:scale-95 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>
                        <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak
                            class="absolute right-0 mt-3 w-64 bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 py-2 overflow-hidden">
                            <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-b border-gray-100 mb-2">Selection & View</div>
                            <button @click="selectAll(); menuOpen = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Select All Items
                            </button>
                            <button @click="selectedIds = []; menuOpen = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear Selection
                            </button>

                            <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-y border-gray-100 mt-2 mb-2">Bulk Operations</div>
                            <button @click="copyItems(); menuOpen = false" :disabled="selectedIds.length === 0" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                </svg>
                                Copy Selection (<span x-text="selectedIds.length"></span>)
                            </button>
                            <button @click="moveItems(); menuOpen = false" :disabled="selectedIds.length === 0" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Move Selection (<span x-text="selectedIds.length"></span>)
                            </button>
                            <button @click="downloadItem(); menuOpen = false" :disabled="selectedIds.length === 0" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Selected
                            </button>
                            <button @click.stop="menuOpen = false; $nextTick(() => { if(confirm('Are you sure you want to delete these items?')) { deleteItems(); } })" :disabled="selectedIds.length === 0" class="w-full text-left px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Selection (<span x-text="selectedIds.length"></span>)
                            </button>

                            <div class="h-px bg-gray-100 my-2"></div>
                            <button @click="pasteItems(); menuOpen = false" :disabled="clipboardIds.length === 0" class="w-full text-left px-4 py-2 text-sm font-bold text-green-600 hover:bg-green-50 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Paste Here (<span x-text="clipboardIds.length"></span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clipboard Bar -->
            <template x-if="clipboardIds.length > 0">
                <div class="mb-6 p-5 bg-blue-100 border border-blue-300 rounded-2xl flex items-center justify-between shadow-xl text-blue-900">
                    <div class="flex items-center gap-4 text-white">
                        <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                            <svg class="w-6 h-6 animate-pulse text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-blue-500 text-md font-bold leading-none block" x-text="clipboardAction.charAt(0).toUpperCase() + clipboardAction.slice(1) + ' In Progress'"></span>
                            <span class="text-blue-500 text-sm font-medium" x-text="clipboardIds.length + ' items ready in buffer. Navigate to target folder.'"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="pasteItems()" class="bg-white text-blue-600 px-8 hover:text-white py-3 rounded-xl font-black transition-all transform hover:scale-105 shadow-lg flex items-center gap-2">
                            <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            Confirm Paste
                        </button>
                        <button @click="clipboardIds = []; clipboardAction = null" class="text-blue-600/80 hover:text-blue-600 px-4 py-2 rounded-xl font-bold transition-colors">Cancel</button>
                    </div>
                </div>
            </template>

            <!-- Navbar / Breadcrumbs -->
            <div class="flex items-center justify-between mb-8 bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <nav class="flex items-center text-sm font-bold text-gray-500 overflow-x-auto whitespace-nowrap scrollbar-hide">
                    <button @click="navigateTo(null)" class="hover:text-blue-600 flex items-center gap-2 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Root
                    </button>
                    <template x-for="crumb in breadcrumbs" :key="crumb.id">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mx-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <button @click="navigateTo(crumb.id)" class="hover:text-blue-600 transition-colors" x-text="crumb.name"></button>
                        </span>
                    </template>
                </nav>
                <div class="flex items-center gap-3 ml-4">
                    <form @submit.prevent="createFolder()">
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <input x-model="newFolderName" placeholder="New folder name"
                                    class="pl-4 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all shadow-sm w-48 font-medium">
                            </div>
                            <button type="submit" class="p-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 shadow-sm active:scale-95 font-bold transition-all" title="Create Folder">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- File Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8"
                @dragover.prevent
                @drop.prevent="dropUpload($event)">

                <template x-if="loading">
                    <div class="col-span-full py-24 text-center text-gray-400 font-bold">Loading...</div>
                </template>

                <template x-for="item in mediaItems" :key="item.id">
                    <div @click.stop="toggleSelect(item.id)"
                        @dblclick.stop="item.is_folder ? navigateTo(item.id) : (item.mime_type && item.mime_type.startsWith('image/') ? openPreview(item.url, item.name, 'image') : null)"
                        @contextmenu.prevent.stop="openContextMenu($event, item)"
                        :class="selectedIds.includes(item.id) ? 'bg-blue-50 border-blue-400 shadow-lg ring-2 ring-blue-100' : 'bg-white border-transparent hover:border-gray-100 hover:shadow-md'"
                        class="group relative rounded-2xl p-5 transition-all cursor-pointer overflow-hidden border-2">
                        <div class="flex flex-col items-center">
                            <div class="w-full aspect-square flex items-center justify-center mb-4 bg-gray-50 rounded-2xl transition-all group-hover:scale-110 group-hover:rotate-1 shadow-inner relative overflow-hidden">
                                <template x-if="item.is_folder">
                                    <svg class="w-20 h-20 text-yellow-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                    </svg>
                                </template>
                                <template x-if="!item.is_folder && item.mime_type && item.mime_type.startsWith('image/')">
                                    <div class="w-full h-full relative group/img">
                                        <img :src="item.url" :alt="item.name" class="w-full h-full object-cover rounded-2xl shadow-sm transition-transform duration-500 group-hover/img:scale-110">
                                        <div class="absolute inset-0 bg-blue-600/0 group-hover/img:bg-blue-600/10 transition-all rounded-2xl"></div>
                                    </div>
                                </template>
                                <template x-if="!item.is_folder && !(item.mime_type && item.mime_type.startsWith('image/'))">
                                    <svg class="w-20 h-20 text-blue-400 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </template>
                            </div>
                            <span class="w-full text-center text-sm font-bold text-gray-800 truncate px-2" :title="item.name" x-text="item.name"></span>
                            <template x-if="!item.is_folder">
                                <span class="text-[10px] font-black text-gray-400 mt-1 uppercase tracking-tighter bg-gray-100 px-2 py-0.5 rounded-full" x-text="(item.size / 1024).toFixed(1) + ' KB'"></span>
                            </template>
                        </div>
                        <template x-if="selectedIds.includes(item.id)">
                            <div class="absolute top-3 right-3">
                                <div class="bg-blue-600 rounded-full p-2 text-white shadow-xl ring-4 ring-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!loading && mediaItems.length === 0">
                    <div class="col-span-full py-24 flex flex-col items-center justify-center text-gray-300 border-4 border-dashed border-gray-100 rounded-3xl">
                        <svg class="w-24 h-24 mb-6 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <p class="text-2xl font-black text-gray-400">Empty Sanctuary</p>
                        <button @click="$refs.fileUpload.click()" class="mt-4 text-blue-600 font-bold hover:underline decoration-2 underline-offset-4 transition-all">Start your collection</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Context Menu -->
        <div x-show="contextMenu.show"
            @click.away="contextMenu.show = false"
            x-cloak
            :style="`position: fixed; left: ${contextMenu.x}px; top: ${contextMenu.y}px;`"
            class="bg-white/95 backdrop-blur-xl border border-white/20 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] z-[100] py-2 w-72 overflow-hidden border border-gray-200">
            <div class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 border-b border-gray-100 mb-2">Advanced Options</div>
            <button @click.stop="copyItems([contextMenu.targetId]); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                </svg>
                Copy Item
            </button>
            <button @click.stop="moveItems([contextMenu.targetId]); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Move Item
            </button>
            <button @click.stop="pasteItems(); contextMenu.show = false" :disabled="clipboardIds.length === 0" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-green-50 hover:text-green-600 flex items-center gap-3 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Paste Here
            </button>
            <div class="h-px bg-gray-100 my-2"></div>
            <button x-show="!contextMenu.isFolder" @click.stop="downloadItem(contextMenu.targetId); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-3 transition-colors">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Item
            </button>
            <button @click.stop="toggleSelect(contextMenu.targetId); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Toggle Selection
            </button>
            <div class="h-px bg-gray-100 my-2"></div>
            <button @click.stop="let id = contextMenu.targetId; contextMenu.show = false; $nextTick(() => { if(confirm('Are you sure you want to delete this item permanently?')) { deleteItems([id]); } })" class="w-full text-left px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Destroy Permanently
            </button>
        </div>

        <!-- Preview Modal -->
        <div x-show="preview.show" x-cloak
            class="fixed inset-0 z-[150] flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-6"
            @click="preview.show = false">
            <div class="relative max-w-6xl w-full max-h-full flex flex-col items-center" @click.stop>
                <button @click="preview.show = false" class="absolute -top-14 right-0 text-white hover:text-blue-400 flex items-center gap-3 transition-colors group">
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
                            <img :src="preview.url" class="max-w-full max-h-[65vh] object-contain shadow-2xl rounded-3xl border-[12px] border-white transition-opacity duration-500" :alt="preview.name">
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Progress -->
        <div x-show="upload.active" x-cloak class="fixed bottom-8 right-8 z-[200] w-96">
            <div class="bg-gray-900 rounded-3xl shadow-[0_30px_70px_rgba(0,0,0,0.3)] border border-white/5 p-6 flex flex-col gap-6 backdrop-blur-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-500 p-3 rounded-2xl shadow-lg shadow-blue-500/20">
                            <svg class="w-7 h-7 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-white leading-none">Uploading</h4>
                            <p class="text-blue-400 text-xs font-bold mt-1 uppercase tracking-widest" x-text="upload.progress + '%'"></p>
                        </div>
                    </div>
                    <span class="text-3xl font-black text-white" x-text="upload.progress + '%'"></span>
                </div>
                <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden p-1 shadow-inner">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-400 h-full rounded-full transition-all duration-300 shadow-sm" :style="`width: ${upload.progress}%`"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fileManager() {
            const API_BASE = '{{ url(config("ufm.route_prefix", "file-manager")) }}/api';
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

            return {
                mediaItems: [],
                breadcrumbs: [],
                currentFolderId: null,
                selectedIds: [],
                clipboardIds: [],
                clipboardAction: null,
                search: '',
                newFolderName: '',
                loading: false,
                message: '',
                error: '',
                contextMenu: {
                    show: false,
                    x: 0,
                    y: 0,
                    targetId: null,
                    isFolder: false
                },
                preview: {
                    show: false,
                    url: '',
                    name: '',
                    type: ''
                },
                upload: {
                    active: false,
                    progress: 0
                },

                async init() {
                    await this.loadMedia();
                },

                async api(endpoint, options = {}) {
                    const url = API_BASE + endpoint;
                    const defaults = {
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                    };
                    if (!(options.body instanceof FormData)) {
                        defaults.headers['Content-Type'] = 'application/json';
                    }
                    const res = await fetch(url, {
                        ...defaults,
                        ...options,
                        headers: {
                            ...defaults.headers,
                            ...(options.headers || {})
                        }
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({
                            message: 'Request failed'
                        }));
                        throw new Error(err.message || 'Request failed');
                    }
                    const contentType = res.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return res.json();
                    }
                    return res;
                },

                async loadMedia() {
                    this.loading = true;
                    this.clearMessages();
                    try {
                        let query = `?parent_id=${this.currentFolderId || ''}`;
                        if (this.search) query += `&search=${encodeURIComponent(this.search)}`;
                        const res = await this.api('/media' + query);
                        this.mediaItems = res.data;

                        const bc = await this.api('/breadcrumbs?parent_id=' + (this.currentFolderId || ''));
                        this.breadcrumbs = bc.data;
                    } catch (e) {
                        this.error = e.message;
                    }
                    this.loading = false;
                },

                async navigateTo(folderId) {
                    this.currentFolderId = folderId;
                    this.selectedIds = [];
                    this.search = '';
                    await this.loadMedia();
                },

                toggleSelect(id) {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx > -1) {
                        this.selectedIds.splice(idx, 1);
                    } else {
                        this.selectedIds.push(id);
                    }
                },

                selectAll() {
                    this.selectedIds = this.mediaItems.map(i => i.id);
                },

                async uploadFiles(event) {
                    const files = event.target.files;
                    if (!files.length) return;

                    this.upload.active = true;
                    this.upload.progress = 0;
                    this.clearMessages();

                    const formData = new FormData();
                    for (let f of files) formData.append('files[]', f);
                    if (this.currentFolderId) formData.append('parent_id', this.currentFolderId);

                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) this.upload.progress = Math.round((e.loaded / e.total) * 100);
                        });

                        await new Promise((resolve, reject) => {
                            xhr.onload = () => {
                                if (xhr.status >= 200 && xhr.status < 300) resolve(JSON.parse(xhr.responseText));
                                else reject(new Error('Upload failed'));
                            };
                            xhr.onerror = () => reject(new Error('Upload failed'));
                            xhr.open('POST', API_BASE + '/media/upload');
                            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
                            xhr.setRequestHeader('Accept', 'application/json');
                            xhr.send(formData);
                        });

                        this.message = 'Files uploaded successfully.';
                        await this.loadMedia();
                    } catch (e) {
                        this.error = e.message;
                    }
                    this.upload.active = false;
                    event.target.value = '';
                },

                async dropUpload(event) {
                    const files = event.dataTransfer.files;
                    if (!files.length) return;
                    // Simulate an event object
                    await this.uploadFiles({
                        target: {
                            files,
                            value: ''
                        }
                    });
                },

                async createFolder() {
                    if (!this.newFolderName) return;
                    this.clearMessages();
                    try {
                        await this.api('/media/folder', {
                            method: 'POST',
                            body: JSON.stringify({
                                name: this.newFolderName,
                                parent_id: this.currentFolderId
                            }),
                        });
                        this.newFolderName = '';
                        await this.loadMedia();
                    } catch (e) {
                        this.error = e.message;
                    }
                },

                async deleteItems(ids) {
                    const deleteIds = ids || this.selectedIds;
                    if (!deleteIds.length) return;
                    this.clearMessages();
                    try {
                        await this.api('/media', {
                            method: 'DELETE',
                            body: JSON.stringify({
                                ids: deleteIds
                            }),
                        });
                        this.selectedIds = this.selectedIds.filter(id => !deleteIds.includes(id));
                        this.message = 'Items deleted.';
                        await this.loadMedia();
                    } catch (e) {
                        this.error = e.message;
                    }
                },

                copyItems(ids) {
                    this.clipboardIds = ids || [...this.selectedIds];
                    this.clipboardAction = 'copy';
                },

                moveItems(ids) {
                    this.clipboardIds = ids || [...this.selectedIds];
                    this.clipboardAction = 'move';
                },

                async pasteItems() {
                    if (!this.clipboardIds.length) return;
                    this.clearMessages();
                    try {
                        await this.api('/media/paste', {
                            method: 'POST',
                            body: JSON.stringify({
                                ids: this.clipboardIds,
                                action: this.clipboardAction,
                                parent_id: this.currentFolderId,
                            }),
                        });
                        this.clipboardIds = [];
                        this.clipboardAction = null;
                        this.message = 'Paste completed.';
                        await this.loadMedia();
                    } catch (e) {
                        this.error = e.message;
                    }
                },

                downloadItem(id) {
                    const downloadId = id || (this.selectedIds.length > 0 ? this.selectedIds[0] : null);
                    if (!downloadId) return;
                    window.open(API_BASE + '/media/' + downloadId + '/download', '_blank');
                },

                openContextMenu(event, item) {
                    this.contextMenu.show = true;
                    this.contextMenu.x = event.clientX;
                    this.contextMenu.y = event.clientY;
                    this.contextMenu.targetId = item.id;
                    this.contextMenu.isFolder = item.is_folder;
                    if (!this.selectedIds.includes(item.id)) {
                        this.toggleSelect(item.id);
                    }
                },

                openPreview(url, name, type) {
                    this.preview.url = url;
                    this.preview.name = name;
                    this.preview.type = type;
                    this.preview.show = true;
                },

                clearMessages() {
                    this.message = '';
                    this.error = '';
                    setTimeout(() => {
                        this.message = '';
                        this.error = '';
                    }, 5000);
                },
            };
        }
    </script>
</body>

</html>