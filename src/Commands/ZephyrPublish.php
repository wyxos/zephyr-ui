<?php

namespace Wyxos\ZephyrUI\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ZephyrPublish extends Command
{
    protected $signature = 'zephyr:publish';
    protected $description = 'Publish Zephyr assets to the public directory';

    public function handle()
    {
        // Path to your package's dist directory
        $distPath = __DIR__ . '/../../dist';

        // Path to the Laravel app's public directory
        $publicPath = public_path('vendor/zephyr-ui');

        File::copyDirectory($distPath, $publicPath);

        $this->info('Assets published successfully.');
    }
}
