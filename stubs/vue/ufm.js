import { createApp } from 'vue';
import FileManager from './FileManager.vue';

const el = document.getElementById('ufm-app');
if (el) {
    createApp(FileManager).mount(el);
}
