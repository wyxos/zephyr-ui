<?php

namespace Wyxos\ZephyrUI;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Wyxos\ZephyrUI\Commands\PublishAssetsCommand;
use Wyxos\ZephyrUI\Commands\SetupTaskManagerInterface;

class ZephyrUIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'taskmanager');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');


        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishAssetsCommand::class,
                SetupTaskManagerInterface::class,
            ]);
        }
    }

    public function register()
    {

    }
}
