<?php

namespace Statamic\Addons\Spock;

use Statamic\Extend\ServiceProvider;

class SpockServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Commander::class, function () {
            return (new Commander($this->app['log']))
                ->config($this->getConfig())
                ->environment($this->app->environment());
        });

        $this->app->alias(Commander::class, 'spock');
    }
}
