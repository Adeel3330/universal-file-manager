/**
 * Universal File Manager — Vue Composable
 * Handles all API calls and shared state for the file manager.
 */
import { ref, reactive, computed } from 'vue';

const API_BASE = document.querySelector('meta[name="ufm-api-base"]')?.content || '/file-manager/api';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

export function useFileManager() {
    const mediaItems = ref([]);
    const breadcrumbs = ref([]);
    const currentFolderId = ref(null);
    const selectedIds = ref([]);
    const clipboardIds = ref([]);
    const clipboardAction = ref(null);
    const search = ref('');
    const loading = ref(false);
    const message = ref('');
    const error = ref('');

    const upload = reactive({
        active: false,
        progress: 0,
    });

    async function api(endpoint, options = {}) {
        const url = API_BASE + endpoint;
        const headers = {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            ...(options.headers || {}),
        };
        if (!(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }
        const res = await fetch(url, { ...options, headers });
        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: 'Request failed' }));
            throw new Error(err.message || 'Request failed');
        }
        const ct = res.headers.get('content-type');
        if (ct && ct.includes('application/json')) return res.json();
        return res;
    }

    async function loadMedia() {
        loading.value = true;
        clearMessages();
        try {
            let q = `?parent_id=${currentFolderId.value || ''}`;
            if (search.value) q += `&search=${encodeURIComponent(search.value)}`;
            const res = await api('/media' + q);
            mediaItems.value = res.data;

            const bc = await api('/breadcrumbs?parent_id=' + (currentFolderId.value || ''));
            breadcrumbs.value = bc.data;
        } catch (e) {
            error.value = e.message;
        }
        loading.value = false;
    }

    async function navigateTo(folderId) {
        currentFolderId.value = folderId;
        selectedIds.value = [];
        search.value = '';
        await loadMedia();
    }

    function toggleSelect(id) {
        const idx = selectedIds.value.indexOf(id);
        if (idx > -1) selectedIds.value.splice(idx, 1);
        else selectedIds.value.push(id);
    }

    function selectAll() {
        selectedIds.value = mediaItems.value.map(i => i.id);
    }

    function unselectAll() {
        selectedIds.value = [];
    }

    async function uploadFiles(files, parentId) {
        if (!files.length) return;
        upload.active = true;
        upload.progress = 0;
        clearMessages();

        const formData = new FormData();
        for (const f of files) formData.append('files[]', f);
        if (parentId) formData.append('parent_id', parentId);

        try {
            await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) upload.progress = Math.round((e.loaded / e.total) * 100);
                });
                xhr.onload = () => xhr.status >= 200 && xhr.status < 300 ? resolve(JSON.parse(xhr.responseText)) : reject(new Error('Upload failed'));
                xhr.onerror = () => reject(new Error('Upload failed'));
                xhr.open('POST', API_BASE + '/media/upload');
                xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.send(formData);
            });
            message.value = 'Files uploaded successfully.';
            await loadMedia();
        } catch (e) {
            error.value = e.message;
        }
        upload.active = false;
    }

    async function createFolder(name) {
        if (!name) return;
        clearMessages();
        try {
            await api('/media/folder', {
                method: 'POST',
                body: JSON.stringify({ name, parent_id: currentFolderId.value }),
            });
            await loadMedia();
        } catch (e) {
            error.value = e.message;
        }
    }

    async function deleteItems(ids) {
        const deleteIds = ids || [...selectedIds.value];
        if (!deleteIds.length) return;
        clearMessages();
        try {
            await api('/media', {
                method: 'DELETE',
                body: JSON.stringify({ ids: deleteIds }),
            });
            selectedIds.value = selectedIds.value.filter(id => !deleteIds.includes(id));
            message.value = 'Items deleted.';
            await loadMedia();
        } catch (e) {
            error.value = e.message;
        }
    }

    function copyItems(ids) {
        clipboardIds.value = ids || [...selectedIds.value];
        clipboardAction.value = 'copy';
    }

    function moveItems(ids) {
        clipboardIds.value = ids || [...selectedIds.value];
        clipboardAction.value = 'move';
    }

    function cancelClipboard() {
        clipboardIds.value = [];
        clipboardAction.value = null;
    }

    async function pasteItems() {
        if (!clipboardIds.value.length) return;
        clearMessages();
        try {
            await api('/media/paste', {
                method: 'POST',
                body: JSON.stringify({
                    ids: clipboardIds.value,
                    action: clipboardAction.value,
                    parent_id: currentFolderId.value,
                }),
            });
            cancelClipboard();
            message.value = 'Paste completed.';
            await loadMedia();
        } catch (e) {
            error.value = e.message;
        }
    }

    function downloadItem(id) {
        const downloadId = id || (selectedIds.value.length > 0 ? selectedIds.value[0] : null);
        if (!downloadId) return;
        window.open(API_BASE + '/media/' + downloadId + '/download', '_blank');
    }

    function clearMessages() {
        message.value = '';
        error.value = '';
    }

    return {
        mediaItems, breadcrumbs, currentFolderId, selectedIds, clipboardIds,
        clipboardAction, search, loading, message, error, upload,
        loadMedia, navigateTo, toggleSelect, selectAll, unselectAll,
        uploadFiles, createFolder, deleteItems, copyItems, moveItems,
        cancelClipboard, pasteItems, downloadItem, clearMessages, API_BASE,
    };
}
