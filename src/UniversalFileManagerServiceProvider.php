<?php

namespace UniversalFileManager;

use Illuminate\Support\ServiceProvider;
use UniversalFileManager\Console\InstallCommand;

class UniversalFileManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $stack = config('ufm.stack', 'livewire');

        // ─── Publish Groups ──────────────────────────────────
        $this->publishes([
            __DIR__ . '/Config/ufm.php' => config_path('ufm.php'),
        ], 'ufm-config');

        // Livewire views (livewire blade + components)
        $this->publishes([
            __DIR__ . '/Resources/views/livewire' => resource_path('views/vendor/ufm/livewire'),
            __DIR__ . '/Resources/views/components' => resource_path('views/vendor/ufm/components'),
            __DIR__ . '/Resources/views/file-manager' => resource_path('views/vendor/ufm/file-manager'),
        ], 'ufm-livewire-views');

        // Blade views
        $this->publishes([
            __DIR__ . '/Resources/views/blade' => resource_path('views/vendor/ufm/blade'),
        ], 'ufm-blade-views');

        // SPA layout (vue/react)
        $this->publishes([
            __DIR__ . '/Resources/views/spa' => resource_path('views/vendor/ufm/spa'),
        ], 'ufm-spa-views');

        // ─── Load Views ──────────────────────────────────────
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'ufm');

        // ─── Migrations ──────────────────────────────────────
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // ─── Routes ──────────────────────────────────────────
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load API routes for non-livewire stacks
        if (in_array($stack, ['blade', 'vue', 'react'])) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }

        // ─── Livewire (only if livewire stack) ───────────────
        if ($stack === 'livewire') {
            if (class_exists(\Livewire\Livewire::class)) {
                \Livewire\Livewire::component('ufm-file-manager', \UniversalFileManager\Http\Livewire\FileManager::class);
            }
        }

        // ─── Blade Components ────────────────────────────────
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(__DIR__ . '/Resources/views/components', 'ufm');

        // ─── Artisan Commands ────────────────────────────────
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/ufm.php',
            'ufm'
        );
    }
}
