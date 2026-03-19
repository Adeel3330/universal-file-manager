<?php

namespace UniversalFileManager\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'ufm:install';
    protected $description = 'Install the Universal File Manager package';

    public function handle()
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   Universal File Manager — Installer     ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('');

        $stack = $this->choice(
            'Which frontend stack would you like to use?',
            ['blade', 'livewire', 'vue', 'react'],
            1 // default: livewire
        );

        $this->info("→ Installing with [{$stack}] stack...");
        $this->newLine();

        // 1. Publish & update config
        $this->publishConfig($stack);

        // 2. Run migrations
        $this->call('migrate');
        $this->info('✓ Database migrations completed.');

        // 3. Publish stack-specific files
        match ($stack) {
            'blade' => $this->installBlade(),
            'livewire' => $this->installLivewire(),
            'vue' => $this->installVue(),
            'react' => $this->installReact(),
        };

        // 4. Create storage link if needed
        if (!file_exists(public_path('storage'))) {
            $this->call('storage:link');
            $this->info('✓ Storage link created.');
        }

        $this->newLine();
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   ✓ Installation Complete!               ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $prefix = config('ufm.route_prefix', 'file-manager');
        $this->info("  Visit: /{$prefix}");
        $this->newLine();

        if (in_array($stack, ['vue', 'react'])) {
            $ext = $stack === 'vue' ? 'js' : 'jsx';
            $this->warn("  Note: {$stack} components have been published to");
            $this->warn("  resources/js/components/ufm/");
            $this->warn("  ");
            $this->warn("  IMPORTANT: Register the auto-mounting entry point in your vite.config.js:");
            $this->warn("  input: [..., 'resources/js/components/ufm/ufm.{$ext}']");
            $this->newLine();
        }
    }

    protected function publishConfig(string $stack)
    {
        $this->callSilent('vendor:publish', ['--tag' => 'ufm-config', '--force' => true]);

        // Update the stack value in the published config
        $configPath = config_path('ufm.php');
        if (file_exists($configPath)) {
            $contents = file_get_contents($configPath);
            $contents = preg_replace(
                "/'stack'\s*=>\s*'[^']*'/",
                "'stack' => '{$stack}'",
                $contents
            );
            file_put_contents($configPath, $contents);
        }

        $this->info("✓ Config published with stack: {$stack}");
    }

    protected function installBlade()
    {
        $this->callSilent('vendor:publish', ['--tag' => 'ufm-blade-views', '--force' => true]);
        $this->info('✓ Blade views published to resources/views/vendor/ufm/');
    }

    protected function installLivewire()
    {
        $this->callSilent('vendor:publish', ['--tag' => 'ufm-livewire-views', '--force' => true]);
        $this->info('✓ Livewire views published to resources/views/vendor/ufm/');
    }

    protected function installVue()
    {
        $stubsPath = dirname(__DIR__, 2) . '/stubs/vue';
        $targetPath = resource_path('js/components/ufm');

        (new Filesystem)->ensureDirectoryExists($targetPath);
        (new Filesystem)->copyDirectory($stubsPath, $targetPath);

        $this->callSilent('vendor:publish', ['--tag' => 'ufm-spa-views', '--force' => true]);
        $this->info('✓ Vue components published to resources/js/components/ufm/');
        $this->info('✓ SPA layout view published.');
    }

    protected function installReact()
    {
        $stubsPath = dirname(__DIR__, 2) . '/stubs/react';
        $targetPath = resource_path('js/components/ufm');

        (new Filesystem)->ensureDirectoryExists($targetPath);
        (new Filesystem)->copyDirectory($stubsPath, $targetPath);

        $this->callSilent('vendor:publish', ['--tag' => 'ufm-spa-views', '--force' => true]);
        $this->info('✓ React components published to resources/js/components/ufm/');
        $this->info('✓ SPA layout view published.');
    }
}
