<template>
    <div class="flex items-center justify-between mb-8 bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <nav class="flex items-center text-sm font-bold text-gray-500 overflow-x-auto whitespace-nowrap scrollbar-hide">
            <button @click="$emit('navigate', null)" class="hover:text-blue-600 flex items-center gap-2 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                Root
            </button>
            <template v-for="crumb in breadcrumbs" :key="crumb.id">
                <svg class="w-4 h-4 mx-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <button @click="$emit('navigate', crumb.id)" class="hover:text-blue-600 transition-colors">{{ crumb.name }}</button>
            </template>
        </nav>
        <div class="flex items-center gap-3 ml-4">
            <form @submit.prevent="submitFolder">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input v-model="folderName" placeholder="New folder name"
                            class="pl-4 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all shadow-sm w-48 font-medium">
                    </div>
                    <button type="submit" class="p-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 shadow-sm active:scale-95 font-bold transition-all" title="Create Folder">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    breadcrumbs: { type: Array, default: () => [] },
});

const emit = defineEmits(['navigate', 'create-folder']);
const folderName = ref('');

function submitFolder() {
    if (!folderName.value) return;
    emit('create-folder', folderName.value);
    folderName.value = '';
}
</script>
