<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'workspace', 'middleware' => 'web'], function () {
    Route::get('/{page?}', function () {
        $configPath = base_path('../../../wyxos/js/zephyr-spa/dist/dev-config.json');
        $devServerConfig = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : null;

        return view('taskmanager::dashboard')->with([
            'devServerConfig' => $devServerConfig,
            'isDevMode' => !is_null($devServerConfig)
        ]);
    })
        ->where('page', '.*')
        ->middleware(['zephyr', 'auth']);
});
