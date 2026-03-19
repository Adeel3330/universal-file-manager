<template>
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Universal File Manager</h1>
        </div>
        <div class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <input :value="searchValue" @input="$emit('search', $event.target.value)" placeholder="Search files..."
                    class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all shadow-sm w-full font-medium">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <!-- Upload -->
            <button @click="$refs.fileUpload.click()" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 shadow-lg active:scale-95 font-bold transition-all flex items-center gap-2 text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Upload
            </button>
            <input type="file" ref="fileUpload" @change="$emit('upload', $event)" multiple class="hidden">

            <ActionMenu
                :selectedIds="selectedIds"
                :clipboardIds="clipboardIds"
                @select-all="$emit('select-all')"
                @unselect-all="$emit('unselect-all')"
                @copy="$emit('copy')"
                @move="$emit('move')"
                @download="$emit('download')"
                @delete="$emit('delete')"
                @paste="$emit('paste')"
            />
        </div>
    </div>
</template>

<script setup>
import ActionMenu from './ActionMenu.vue';

defineProps({
    selectedIds: { type: Array, default: () => [] },
    clipboardIds: { type: Array, default: () => [] },
    searchValue: { type: String, default: '' },
});

defineEmits(['upload', 'search', 'select-all', 'unselect-all', 'copy', 'move', 'download', 'delete', 'paste']);
</script>
