<?php

namespace Wyxos\ZephyrUI;

use Illuminate\Support\ServiceProvider;
use Wyxos\ZephyrUI\Commands\PublishAssetsCommand;
use Wyxos\ZephyrUI\Commands\SetupZephyr;

class ZephyrUIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/zephyr.php' => config_path('zephyr.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/../views', 'taskmanager');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        app('router')->aliasMiddleware('zephyr', ZephyrMiddleware::class);


        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishAssetsCommand::class,
                SetupZephyr::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/zephyr.php', 'zephyr'
        );
    }
}
