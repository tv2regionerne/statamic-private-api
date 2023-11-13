<?php

namespace Tv2regionerne\StatamicPrivateApi;

use Illuminate\Support\Facades\Route;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->mergeConfigFrom($config = dirname(__DIR__) .'/config/private-api.php', 'private-api');

        $this->publishes([
            $config => config_path('private-api.php'),
        ], 'private-api-config');

        $this->loadRoutesFrom(dirname(__DIR__) .'/routes/api.php');
    }
}
