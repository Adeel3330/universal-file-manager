<?php

namespace UniversalFileManager;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use UniversalFileManager\Http\Livewire\FileManager;

class UniversalFileManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/Config/ufm.php' => config_path('ufm.php'),
        ], 'ufm-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/ufm'),
        ], 'ufm-views');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'ufm');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Register Livewire Component
        Livewire::component('ufm-file-manager', FileManager::class);
    }

    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/Config/ufm.php',
            'ufm'
        );
    }
}
