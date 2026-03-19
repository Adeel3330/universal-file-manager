import React from 'react';
import ActionMenu from './ActionMenu.jsx';

export default function Header({
    selectedIds = [],
    clipboardIds = [],
    searchValue = '',
    onUpload,
    onSearch,
    onSelectAll,
    onUnselectAll,
    onCopy,
    onMove,
    onDownload,
    onDelete,
    onPaste
}) {
    const fileInputRef = React.useRef(null);

    return (
        <div className="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 className="text-3xl font-black text-gray-900 tracking-tight">Universal File Manager</h1>
            </div>
            <div className="flex items-center gap-3">
                {/* Search */}
                <div className="relative">
                    <input
                        value={searchValue}
                        onChange={(e) => onSearch(e.target.value)}
                        placeholder="Search files..."
                        className="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all shadow-sm w-full font-medium"
                    />
                    <div className="absolute left-3 top-2.5 text-gray-400">
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                {/* Upload */}
                <button
                    onClick={() => fileInputRef.current.click()}
                    className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 shadow-lg active:scale-95 font-bold transition-all flex items-center gap-2 text-white"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" /></svg>
                    Upload
                </button>
                <input
                    type="file"
                    ref={fileInputRef}
                    onChange={(e) => onUpload(e.target.files)}
                    multiple
                    className="hidden"
                />

                <ActionMenu
                    selectedIds={selectedIds}
                    clipboardIds={clipboardIds}
                    onSelectAll={onSelectAll}
                    onUnselectAll={onUnselectAll}
                    onCopy={onCopy}
                    onMove={onMove}
                    onDownload={onDownload}
                    onDelete={onDelete}
                    onPaste={onPaste}
                />
            </div>
        </div>
    );
}
