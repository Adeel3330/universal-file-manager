<template>
    <div class="relative">
        <button @click.stop="open = !open" class="p-2.5 rounded-xl hover:bg-white bg-gray-100 text-gray-700 border border-gray-200 shadow-sm active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
        </button>
        <div v-if="open" @click.away="open = false" class="absolute right-0 mt-3 w-64 bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 py-2 overflow-hidden">
            <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-b border-gray-100 mb-2">Selection & View</div>
            <button @click="$emit('select-all'); open = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Select All Items
            </button>
            <button @click="$emit('unselect-all'); open = false" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                Clear Selection
            </button>

            <div class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-y border-gray-100 mt-2 mb-2">Bulk Operations</div>
            <button @click="$emit('copy'); open = false" :disabled="!selectedIds.length" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                Copy Selection ({{ selectedIds.length }})
            </button>
            <button @click="$emit('move'); open = false" :disabled="!selectedIds.length" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 flex items-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                Move Selection ({{ selectedIds.length }})
            </button>
            <button @click="$emit('download'); open = false" :disabled="!selectedIds.length" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Download Selected
            </button>
            <button @click.stop="open = false; handleDelete()" :disabled="!selectedIds.length" class="w-full text-left px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Delete Selection ({{ selectedIds.length }})
            </button>

            <div class="h-px bg-gray-100 my-2"></div>
            <button @click="$emit('paste'); open = false" :disabled="!clipboardIds.length" class="w-full text-left px-4 py-2 text-sm font-bold text-green-600 hover:bg-green-50 flex items-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Paste Here ({{ clipboardIds.length }})
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const open = ref(false);

defineProps({
    selectedIds: { type: Array, default: () => [] },
    clipboardIds: { type: Array, default: () => [] },
});

const emit = defineEmits(['select-all', 'unselect-all', 'copy', 'move', 'download', 'delete', 'paste']);

function handleDelete() {
    nextTick(() => emit('delete'));
}
</script>
