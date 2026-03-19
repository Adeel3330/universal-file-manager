import React from 'react';

export default function UploadProgress({
    progress = 0
}) {
    return (
        <div className="fixed bottom-8 right-8 z-[200] w-96">
            <div className="bg-gray-900 rounded-3xl shadow-[0_30px_70px_rgba(0,0,0,0.3)] border border-white/5 p-6 flex flex-col gap-6 backdrop-blur-2xl">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="bg-blue-500 p-3 rounded-2xl shadow-lg shadow-blue-500/20">
                            <svg className="w-7 h-7 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                        </div>
                        <div>
                            <h4 className="text-lg font-black text-white leading-none">Syncing Data</h4>
                            <p className="text-blue-400 text-xs font-bold mt-1 uppercase tracking-widest">{progress}%</p>
                        </div>
                    </div>
                    <span className="text-3xl font-black text-white">{progress}%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-3 overflow-hidden p-1 shadow-inner">
                    <div className="bg-gradient-to-r from-blue-600 to-blue-400 h-full rounded-full transition-all duration-300 shadow-sm" style={{ width: `${progress}%` }}></div>
                </div>
                <div className="flex justify-between items-center text-[10px] uppercase tracking-widest font-black text-gray-500">
                    <span className="flex items-center gap-2">
                        <span className="w-2 h-2 bg-blue-500 rounded-full animate-ping"></span>
                        Transfusing Files
                    </span>
                    <span>{progress < 100 ? 'Processing...' : 'Finalizing Stream'}</span>
                </div>
            </div>
        </div>
    );
}
