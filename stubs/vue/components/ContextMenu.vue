<template>
    <Teleport to="body">
        <div :style="`position: fixed; left: ${x}px; top: ${y}px;`"
            class="bg-white/95 backdrop-blur-xl border border-gray-200 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] z-[100] py-2 w-72 overflow-hidden"
            @click.away="$emit('close')">
            <div class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 border-b border-gray-100 mb-2">Advanced Options</div>
            <button @click.stop="$emit('copy', targetId); $emit('close')" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                Copy Item
            </button>
            <button @click.stop="$emit('move', targetId); $emit('close')" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                Move Item
            </button>
            <button @click.stop="$emit('paste'); $emit('close')" :disabled="!clipboardCount" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-green-50 hover:text-green-600 flex items-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Paste Here
            </button>
            <div class="h-px bg-gray-100 my-2"></div>
            <button v-if="!isFolder" @click.stop="$emit('download', targetId); $emit('close')" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-3">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Download Item
            </button>
            <button @click.stop="$emit('toggle-select', targetId); $emit('close')" class="w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                Toggle Selection
            </button>
            <div class="h-px bg-gray-100 my-2"></div>
            <button @click.stop="$emit('close'); handleDelete()" class="w-full text-left px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Destroy Permanently
            </button>
        </div>
    </Teleport>
</template>

<script setup>
import { nextTick } from 'vue';

const props = defineProps({
    x: Number,
    y: Number,
    targetId: [Number, String],
    isFolder: Boolean,
    clipboardCount: { type: Number, default: 0 },
});

const emit = defineEmits(['close', 'copy', 'move', 'paste', 'download', 'toggle-select', 'delete']);

function handleDelete() {
    nextTick(() => emit('delete', props.targetId));
}
</script>
