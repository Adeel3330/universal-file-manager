<?php

use Illuminate\Support\Facades\Route;
use UniversalFileManager\Http\Controllers\FileManagerController;

Route::group([
    'middleware' => array_merge(config('ufm.middleware', ['web']), ['web']),
    'prefix' => config('ufm.route_prefix', 'file-manager') . '/api',
], function () {
    Route::get('/media', [FileManagerController::class, 'index'])->name('ufm.api.media.index');
    Route::get('/breadcrumbs', [FileManagerController::class, 'breadcrumbs'])->name('ufm.api.breadcrumbs');
    Route::post('/media/upload', [FileManagerController::class, 'upload'])->name('ufm.api.media.upload');
    Route::post('/media/folder', [FileManagerController::class, 'createFolder'])->name('ufm.api.media.folder');
    Route::delete('/media', [FileManagerController::class, 'destroy'])->name('ufm.api.media.destroy');
    Route::post('/media/copy', [FileManagerController::class, 'copy'])->name('ufm.api.media.copy');
    Route::post('/media/move', [FileManagerController::class, 'move'])->name('ufm.api.media.move');
    Route::post('/media/paste', [FileManagerController::class, 'paste'])->name('ufm.api.media.paste');
    Route::post('/media/move-to-folder', [FileManagerController::class, 'moveToFolder'])->name('ufm.api.media.moveToFolder');
    Route::get('/media/{id}/download', [FileManagerController::class, 'download'])->name('ufm.api.media.download');
});
