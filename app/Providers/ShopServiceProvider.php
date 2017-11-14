<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class ShopServiceProvider.
 * 店铺数据服务绑定
 * @package App\Providers
 */
class ShopServiceProvider extends ServiceProvider
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
            \App\Repositories\Shops\Contracts\ShopRepository::class,
            \App\Repositories\Shops\ShopRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Shops\Contracts\MarketRepository::class,
            \App\Repositories\Shops\MarketRepository::class
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
            \App\Repositories\Shops\Contracts\ShopRepository::class,
            \App\Repositories\Shops\Contracts\MarketRepository::class,
        ];
    }
}
