<?php

use Illuminate\Support\Facades\Route;
use UniversalFileManager\Http\Livewire\FileManager;

Route::get('/file-manager', function () {
    return view('ufm::file-manager.index');
})->name('ufm.index');
