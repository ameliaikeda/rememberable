<?php

namespace Watson\Rememberable;

use Amelia\Rememberable\HmacCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom($config = __DIR__ . '/config.php', 'rememberable');
        $this->publishes([$config => config_path('rememberable.php')], 'config');
        $this->commands(HmacCommand::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
