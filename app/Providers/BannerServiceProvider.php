<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BannerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
            \App\Repositories\Banner\Contracts\BannerRepository::class,
            \App\Repositories\Banner\BannerRepository::class
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            \App\Repositories\Banner\Contracts\BannerRepository::class,
        ];
    }
}
