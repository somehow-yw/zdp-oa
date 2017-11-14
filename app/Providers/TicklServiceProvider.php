<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TicklServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \App\Repositories\Tickl\Contracts\TicklRepositories::class,
            \App\Repositories\Tickl\TicklRepositories::class
        );
    }
}
