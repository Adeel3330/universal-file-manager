<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[150] flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-6" @click="$emit('close')">
            <div class="relative max-w-6xl w-full max-h-full flex flex-col items-center" @click.stop>
                <button @click="$emit('close')" class="absolute -top-14 right-0 text-white hover:text-blue-400 flex items-center gap-3 transition-colors group">
                    <span class="text-xs font-black uppercase tracking-[0.2em] opacity-50 group-hover:opacity-100 transition-opacity">Dismiss Preview</span>
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <div class="w-full bg-white rounded-[2.5rem] overflow-hidden shadow-[0_50px_100px_rgba(0,0,0,0.5)] flex flex-col">
                    <div class="px-8 py-6 border-b flex justify-between items-center bg-white">
                        <div>
                            <h3 class="font-black text-2xl text-gray-900 leading-none">{{ name }}</h3>
                            <span class="text-gray-400 text-xs font-bold uppercase tracking-widest mt-2 block">{{ type }} resource</span>
                        </div>
                        <div class="flex gap-3">
                            <a v-if="type === 'image'" :href="url" download class="bg-blue-600 text-white px-8 py-3 rounded-2xl text-sm font-black hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all hover:scale-105">Download</a>
                        </div>
                    </div>
                    <div class="flex-1 flex items-center justify-center p-12 bg-gray-50 min-h-[500px]">
                        <img v-if="type === 'image'" :src="url" class="max-w-full max-h-[65vh] object-contain shadow-2xl rounded-3xl border-[12px] border-white" :alt="name">
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
defineProps({
    url: String,
    name: String,
    type: String,
});

defineEmits(['close']);
</script>
