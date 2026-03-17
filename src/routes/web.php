<?php

use Illuminate\Support\Facades\Route;
use UniversalFileManager\Http\Livewire\FileManager;

Route::group([
    'middleware' => config('ufm.middleware', ['web']),
    'prefix' => config('ufm.route_prefix', 'file-manager')
], function () {
    Route::get('/', function () {
        return view('ufm::file-manager.index');
    })->name('ufm.index');
});
