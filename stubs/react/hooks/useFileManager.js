/**
 * Universal File Manager — React Custom Hook
 * Handles all API calls and shared state for the file manager.
 */
import { useState, useCallback } from 'react';

const API_BASE = document.querySelector('meta[name="ufm-api-base"]')?.content || '/file-manager/api';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

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

export function useFileManager() {
    const [mediaItems, setMediaItems] = useState([]);
    const [breadcrumbs, setBreadcrumbs] = useState([]);
    const [currentFolderId, setCurrentFolderId] = useState(null);
    const [selectedIds, setSelectedIds] = useState([]);
    const [clipboardIds, setClipboardIds] = useState([]);
    const [clipboardAction, setClipboardAction] = useState(null);
    const [search, setSearch] = useState('');
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [upload, setUpload] = useState({ active: false, progress: 0 });

    const clearMessages = useCallback(() => {
        setMessage('');
        setError('');
    }, []);

    const loadMedia = useCallback(async (folderId, searchVal) => {
        setLoading(true);
        clearMessages();
        try {
            const fid = folderId !== undefined ? folderId : currentFolderId;
            let q = `?parent_id=${fid || ''}`;
            if (searchVal) q += `&search=${encodeURIComponent(searchVal)}`;
            const res = await api('/media' + q);
            setMediaItems(res.data);

            const bc = await api('/breadcrumbs?parent_id=' + (fid || ''));
            setBreadcrumbs(bc.data);
        } catch (e) {
            setError(e.message);
        }
        setLoading(false);
    }, [currentFolderId, clearMessages]);

    const navigateTo = useCallback(async (folderId) => {
        setCurrentFolderId(folderId);
        setSelectedIds([]);
        setSearch('');
        await loadMedia(folderId, '');
    }, [loadMedia]);

    const toggleSelect = useCallback((id) => {
        setSelectedIds(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
    }, []);

    const selectAll = useCallback(() => {
        setSelectedIds(mediaItems.map(i => i.id));
    }, [mediaItems]);

    const unselectAll = useCallback(() => {
        setSelectedIds([]);
    }, []);

    const uploadFiles = useCallback(async (files, parentId) => {
        if (!files.length) return;
        setUpload({ active: true, progress: 0 });
        clearMessages();

        const formData = new FormData();
        for (const f of files) formData.append('files[]', f);
        if (parentId) formData.append('parent_id', parentId);

        try {
            await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) setUpload({ active: true, progress: Math.round((e.loaded / e.total) * 100) });
                });
                xhr.onload = () => xhr.status >= 200 && xhr.status < 300 ? resolve(JSON.parse(xhr.responseText)) : reject(new Error('Upload failed'));
                xhr.onerror = () => reject(new Error('Upload failed'));
                xhr.open('POST', API_BASE + '/media/upload');
                xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.send(formData);
            });
            setMessage('Files uploaded successfully.');
            await loadMedia();
        } catch (e) {
            setError(e.message);
        }
        setUpload({ active: false, progress: 0 });
    }, [loadMedia, clearMessages]);

    const createFolder = useCallback(async (name) => {
        if (!name) return;
        clearMessages();
        try {
            await api('/media/folder', {
                method: 'POST',
                body: JSON.stringify({ name, parent_id: currentFolderId }),
            });
            await loadMedia();
        } catch (e) {
            setError(e.message);
        }
    }, [currentFolderId, loadMedia, clearMessages]);

    const deleteItems = useCallback(async (ids) => {
        const deleteIds = ids || [...selectedIds];
        if (!deleteIds.length) return;
        clearMessages();
        try {
            await api('/media', {
                method: 'DELETE',
                body: JSON.stringify({ ids: deleteIds }),
            });
            setSelectedIds(prev => prev.filter(id => !deleteIds.includes(id)));
            setMessage('Items deleted.');
            await loadMedia();
        } catch (e) {
            setError(e.message);
        }
    }, [selectedIds, loadMedia, clearMessages]);

    const copyItems = useCallback((ids) => {
        setClipboardIds(ids || [...selectedIds]);
        setClipboardAction('copy');
    }, [selectedIds]);

    const moveItems = useCallback((ids) => {
        setClipboardIds(ids || [...selectedIds]);
        setClipboardAction('move');
    }, [selectedIds]);

    const cancelClipboard = useCallback(() => {
        setClipboardIds([]);
        setClipboardAction(null);
    }, []);

    const pasteItems = useCallback(async () => {
        if (!clipboardIds.length) return;
        clearMessages();
        try {
            await api('/media/paste', {
                method: 'POST',
                body: JSON.stringify({
                    ids: clipboardIds,
                    action: clipboardAction,
                    parent_id: currentFolderId,
                }),
            });
            cancelClipboard();
            setMessage('Paste completed.');
            await loadMedia();
        } catch (e) {
            setError(e.message);
        }
    }, [clipboardIds, clipboardAction, currentFolderId, loadMedia, cancelClipboard, clearMessages]);

    const downloadItem = useCallback((id) => {
        const downloadId = id || (selectedIds.length > 0 ? selectedIds[0] : null);
        if (!downloadId) return;
        window.open(API_BASE + '/media/' + downloadId + '/download', '_blank');
    }, [selectedIds]);

    return {
        mediaItems, breadcrumbs, currentFolderId, selectedIds, clipboardIds,
        clipboardAction, search, loading, message, error, upload,
        loadMedia, navigateTo, toggleSelect, selectAll, unselectAll,
        uploadFiles, createFolder, deleteItems, copyItems, moveItems,
        cancelClipboard, pasteItems, downloadItem, clearMessages,
        setSearch, API_BASE,
    };
}
