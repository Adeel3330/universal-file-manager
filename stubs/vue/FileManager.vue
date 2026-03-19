<template>
    <div class="p-6 bg-gray-50 min-h-screen" @keydown.escape="preview.show = false">
        <div class="max-w-7xl mx-auto">
            <!-- Status Messages -->
            <div v-if="message" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm font-medium">{{ message }}</div>
            <div v-if="error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-sm font-medium">{{ error }}</div>

            <Header
                :selectedIds="selectedIds"
                :clipboardIds="clipboardIds"
                @upload="handleUpload"
                @select-all="selectAll"
                @unselect-all="unselectAll"
                @copy="copyItems()"
                @move="moveItems()"
                @download="downloadItem()"
                @delete="handleDelete()"
                @paste="pasteItems"
                @search="handleSearch"
            />

            <!-- Clipboard Bar -->
            <div v-if="clipboardIds.length > 0" class="mb-6 p-5 bg-blue-100 border border-blue-300 rounded-2xl flex items-center justify-between shadow-xl text-blue-900">
                <div class="flex items-center gap-4 text-white">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                        <svg class="w-6 h-6 animate-pulse text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                    </div>
                    <div>
                        <span class="text-blue-500 text-md font-bold leading-none block">{{ clipboardAction?.charAt(0).toUpperCase() + clipboardAction?.slice(1) }} In Progress</span>
                        <span class="text-blue-500 text-sm font-medium">{{ clipboardIds.length }} items ready in buffer. Navigate to target folder.</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="pasteItems" class="bg-white text-blue-600 px-8 hover:text-white py-3 rounded-xl font-black transition-all transform hover:scale-105 shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        Confirm Paste
                    </button>
                    <button @click="cancelClipboard" class="text-blue-600/80 hover:text-blue-600 px-4 py-2 rounded-xl font-bold transition-colors">Cancel</button>
                </div>
            </div>

            <Navbar :breadcrumbs="breadcrumbs" @navigate="navigateTo" @create-folder="createFolder" />

            <!-- File Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8" @dragover.prevent @drop.prevent="dropUpload">
                <div v-if="loading" class="col-span-full py-24 text-center text-gray-400 font-bold">Loading...</div>

                <ItemCard
                    v-for="item in mediaItems"
                    :key="item.id"
                    :item="item"
                    :selected="selectedIds.includes(item.id)"
                    @select="toggleSelect(item.id)"
                    @dblclick="handleDblClick(item)"
                    @contextmenu="openContextMenu($event, item)"
                />

                <div v-if="!loading && mediaItems.length === 0" class="col-span-full py-24 flex flex-col items-center justify-center text-gray-300 border-4 border-dashed border-gray-100 rounded-3xl">
                    <svg class="w-24 h-24 mb-6 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                    <p class="text-2xl font-black text-gray-400">Empty Sanctuary</p>
                </div>
            </div>
        </div>

        <ContextMenu
            v-if="contextMenu.show"
            :x="contextMenu.x"
            :y="contextMenu.y"
            :target-id="contextMenu.targetId"
            :is-folder="contextMenu.isFolder"
            :clipboard-count="clipboardIds.length"
            @close="contextMenu.show = false"
            @copy="copyItems([$event])"
            @move="moveItems([$event])"
            @paste="pasteItems"
            @download="downloadItem($event)"
            @toggle-select="toggleSelect($event)"
            @delete="handleContextDelete($event)"
        />

        <PreviewModal
            v-if="preview.show"
            :url="preview.url"
            :name="preview.name"
            :type="preview.type"
            @close="preview.show = false"
        />

        <UploadProgress v-if="upload.active" :progress="upload.progress" />
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';
import { useFileManager } from './composables/useFileManager.js';
import Header from './components/Header.vue';
import Navbar from './components/Navbar.vue';
import ItemCard from './components/ItemCard.vue';
import ContextMenu from './components/ContextMenu.vue';
import ActionMenu from './components/ActionMenu.vue';
import PreviewModal from './components/PreviewModal.vue';
import UploadProgress from './components/UploadProgress.vue';

const {
    mediaItems, breadcrumbs, currentFolderId, selectedIds, clipboardIds,
    clipboardAction, search, loading, message, error, upload,
    loadMedia, navigateTo, toggleSelect, selectAll, unselectAll,
    uploadFiles, createFolder, deleteItems, copyItems, moveItems,
    cancelClipboard, pasteItems, downloadItem, clearMessages,
} = useFileManager();

const contextMenu = reactive({ show: false, x: 0, y: 0, targetId: null, isFolder: false });
const preview = reactive({ show: false, url: '', name: '', type: '' });

onMounted(() => loadMedia());

function handleUpload(event) {
    uploadFiles(event.target.files, currentFolderId.value);
    event.target.value = '';
}

function dropUpload(event) {
    const files = event.dataTransfer.files;
    if (files.length) uploadFiles(files, currentFolderId.value);
}

function handleSearch(value) {
    search.value = value;
    loadMedia();
}

function handleDblClick(item) {
    if (item.is_folder) navigateTo(item.id);
    else if (item.mime_type?.startsWith('image/')) {
        preview.url = item.url;
        preview.name = item.name;
        preview.type = 'image';
        preview.show = true;
    }
}

function openContextMenu(event, item) {
    event.preventDefault();
    contextMenu.show = true;
    contextMenu.x = event.clientX;
    contextMenu.y = event.clientY;
    contextMenu.targetId = item.id;
    contextMenu.isFolder = item.is_folder;
    if (!selectedIds.value.includes(item.id)) toggleSelect(item.id);
}

function handleDelete() {
    if (confirm('Are you sure you want to delete these items?')) deleteItems();
}

function handleContextDelete(id) {
    if (confirm('Are you sure you want to delete this item permanently?')) deleteItems([id]);
}
</script>
