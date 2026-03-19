<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => config('ufm.middleware', ['web']),
    'prefix' => config('ufm.route_prefix', 'file-manager')
], function () {
    $stack = config('ufm.stack', 'livewire');

    Route::get('/', function () use ($stack) {
        return match ($stack) {
            'blade' => view('ufm::blade.file-manager'),
            'vue', 'react' => view('ufm::spa.index', ['stack' => $stack]),
            default => view('ufm::file-manager.index'), // livewire
        };
    })->name('ufm.index');
});
