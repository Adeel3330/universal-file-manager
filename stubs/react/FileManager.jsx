import React, { useEffect, useState, useRef } from 'react';
import { useFileManager } from './hooks/useFileManager.js';
import Header from './components/Header.jsx';
import Navbar from './components/Navbar.jsx';
import ItemCard from './components/ItemCard.jsx';
import ContextMenu from './components/ContextMenu.jsx';
import PreviewModal from './components/PreviewModal.jsx';
import UploadProgress from './components/UploadProgress.jsx';

export default function FileManager() {
    const {
        mediaItems, breadcrumbs, currentFolderId, selectedIds, clipboardIds,
        clipboardAction, search, loading, message, error, upload,
        loadMedia, navigateTo, toggleSelect, selectAll, unselectAll,
        uploadFiles, createFolder, deleteItems, copyItems, moveItems,
        cancelClipboard, pasteItems, downloadItem, setSearch,
    } = useFileManager();

    const [contextMenu, setContextMenu] = useState({ show: false, x: 0, y: 0, targetId: null, isFolder: false });
    const [preview, setPreview] = useState({ show: false, url: '', name: '', type: '' });

    useEffect(() => { loadMedia(); }, []);

    const handleUpload = (files) => {
        uploadFiles(files, currentFolderId);
    };

    const handleDblClick = (item) => {
        if (item.is_folder) navigateTo(item.id);
        else if (item.mime_type?.startsWith('image/')) {
            setPreview({ show: true, url: item.url, name: item.name, type: 'image' });
        }
    };

    const handleContextMenu = (e, item) => {
        e.preventDefault();
        setContextMenu({ show: true, x: e.clientX, y: e.clientY, targetId: item.id, isFolder: item.is_folder });
        if (!selectedIds.includes(item.id)) toggleSelect(item.id);
    };

    const handleDelete = () => {
        if (window.confirm('Are you sure you want to delete these items?')) deleteItems();
    };

    const handleContextDelete = (id) => {
        if (window.confirm('Are you sure you want to delete this item permanently?')) deleteItems([id]);
    };

    const handleSearch = (value) => {
        setSearch(value);
        loadMedia(undefined, value);
    };

    return (
        <div className="p-6 bg-gray-50 min-h-screen">
            <div className="max-w-7xl mx-auto">
                {message && <div className="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm font-medium">{message}</div>}
                {error && <div className="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-sm font-medium">{error}</div>}

                <Header
                    selectedIds={selectedIds}
                    clipboardIds={clipboardIds}
                    onUpload={handleUpload}
                    onSelectAll={selectAll}
                    onUnselectAll={unselectAll}
                    onCopy={() => copyItems()}
                    onMove={() => moveItems()}
                    onDownload={() => downloadItem()}
                    onDelete={handleDelete}
                    onPaste={pasteItems}
                    onSearch={handleSearch}
                />

                {clipboardIds.length > 0 && (
                    <div className="mb-6 p-5 bg-blue-100 border border-blue-300 rounded-2xl flex items-center justify-between shadow-xl text-blue-900">
                        <div className="flex items-center gap-4 text-white">
                            <div className="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                                <svg className="w-6 h-6 animate-pulse text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                            </div>
                            <div>
                                <span className="text-blue-500 text-md font-bold leading-none block">{clipboardAction?.charAt(0).toUpperCase() + clipboardAction?.slice(1)} In Progress</span>
                                <span className="text-blue-500 text-sm font-medium">{clipboardIds.length} items ready in buffer.</span>
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <button onClick={pasteItems} className="bg-white text-blue-600 px-8 hover:text-white py-3 rounded-xl font-black transition-all transform hover:scale-105 shadow-lg flex items-center gap-2">Confirm Paste</button>
                            <button onClick={cancelClipboard} className="text-blue-600/80 hover:text-blue-600 px-4 py-2 rounded-xl font-bold transition-colors">Cancel</button>
                        </div>
                    </div>
                )}

                <Navbar breadcrumbs={breadcrumbs} onNavigate={navigateTo} onCreateFolder={createFolder} />

                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8">
                    {loading && <div className="col-span-full py-24 text-center text-gray-400 font-bold">Loading...</div>}
                    {mediaItems.map(item => (
                        <ItemCard
                            key={item.id}
                            item={item}
                            selected={selectedIds.includes(item.id)}
                            onSelect={() => toggleSelect(item.id)}
                            onDblClick={() => handleDblClick(item)}
                            onContextMenu={(e) => handleContextMenu(e, item)}
                        />
                    ))}
                    {!loading && mediaItems.length === 0 && (
                        <div className="col-span-full py-24 flex flex-col items-center justify-center text-gray-300 border-4 border-dashed border-gray-100 rounded-3xl">
                            <svg className="w-24 h-24 mb-6 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                            <p className="text-2xl font-black text-gray-400">Empty Sanctuary</p>
                        </div>
                    )}
                </div>
            </div>

            {contextMenu.show && (
                <ContextMenu
                    x={contextMenu.x}
                    y={contextMenu.y}
                    targetId={contextMenu.targetId}
                    isFolder={contextMenu.isFolder}
                    clipboardCount={clipboardIds.length}
                    onClose={() => setContextMenu(prev => ({ ...prev, show: false }))}
                    onCopy={(id) => copyItems([id])}
                    onMove={(id) => moveItems([id])}
                    onPaste={pasteItems}
                    onDownload={(id) => downloadItem(id)}
                    onToggleSelect={(id) => toggleSelect(id)}
                    onDelete={handleContextDelete}
                />
            )}

            {preview.show && (
                <PreviewModal
                    url={preview.url}
                    name={preview.name}
                    type={preview.type}
                    onClose={() => setPreview(prev => ({ ...prev, show: false }))}
                />
            )}

            {upload.active && <UploadProgress progress={upload.progress} />}
        </div>
    );
}
