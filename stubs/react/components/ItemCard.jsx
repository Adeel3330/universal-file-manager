import React from 'react';

export default function ItemCard({
    item,
    selected,
    onSelect,
    onDblClick,
    onContextMenu
}) {
    const isImage = item.mime_type?.startsWith('image/');

    return (
        <div
            onClick={(e) => { e.stopPropagation(); onSelect(); }}
            onDoubleClick={(e) => { e.stopPropagation(); onDblClick(); }}
            onContextMenu={(e) => onContextMenu(e)}
            className={`group relative rounded-2xl p-5 transition-all cursor-pointer overflow-hidden border-2 ${selected ? 'bg-blue-50 border-blue-400 shadow-lg ring-2 ring-blue-100' : 'bg-white border-transparent hover:border-gray-100 hover:shadow-md'
                }`}
        >
            <div className="flex flex-col items-center">
                <div className="w-full aspect-square flex items-center justify-center mb-4 bg-gray-50 rounded-2xl transition-all group-hover:scale-110 group-hover:rotate-1 shadow-inner relative overflow-hidden">
                    {item.is_folder ? (
                        <svg className="w-20 h-20 text-yellow-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" /></svg>
                    ) : isImage ? (
                        <div className="w-full h-full relative group/img">
                            <img src={item.url} alt={item.name} className="w-full h-full object-cover rounded-2xl shadow-sm transition-transform duration-500 group-hover/img:scale-110" />
                            <div className="absolute inset-0 bg-blue-600/0 group-hover/img:bg-blue-600/10 transition-all rounded-2xl"></div>
                        </div>
                    ) : (
                        <svg className="w-20 h-20 text-blue-400 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    )}
                </div>
                <span className="w-full text-center text-sm font-bold text-gray-800 truncate px-2" title={item.name}>{item.name}</span>
                {!item.is_folder && (
                    <span className="text-[10px] font-black text-gray-400 mt-1 uppercase tracking-tighter bg-gray-100 px-2 py-0.5 rounded-full">
                        {(item.size / 1024).toFixed(1)} KB
                    </span>
                )}
            </div>
            {selected && (
                <div className="absolute top-3 right-3">
                    <div className="bg-blue-600 rounded-full p-2 text-white shadow-xl ring-4 ring-white">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 20 20"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="4" d="M5 13l4 4L19 7" /></svg>
                    </div>
                </div>
            )}
        </div>
    );
}
