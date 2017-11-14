<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class GoodsServiceProvider extends ServiceProvider
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
            \App\Repositories\Goods\Contracts\GoodsPriceChangeLogRepository::class,
            \App\Repositories\Goods\GoodsPriceChangeLogRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\GoodsRepository::class,
            \App\Repositories\Goods\GoodsRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\GoodsTypeRepository::class,
            \App\Repositories\Goods\GoodsTypeRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\GoodsConstraintsRepository::class,
            \App\Repositories\Goods\GoodsConstraintsRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\ActivityGoodsRepository::class,
            \App\Repositories\Goods\ActivityGoodsRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::class,
            \App\Repositories\Goods\GoodsTypeSpecialAttrRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\GoodsTransferRepository::class,
            \App\Repositories\Goods\GoodsTransferRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\GoodsOperationRepository::class,
            \App\Repositories\Goods\GoodsOperationRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\GoodsListRepository::class,
            \App\Repositories\Goods\GoodsListRepository::class
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
            \App\Repositories\Goods\Contracts\GoodsPriceChangeLogRepository::class,
            \App\Repositories\Goods\Contracts\GoodsRepository::class,
            \App\Repositories\Goods\Contracts\GoodsTypeRepository::class,
            \App\Repositories\Goods\Contracts\GoodsConstraintsRepository::class,
            \App\Repositories\Goods\Contracts\ActivityGoodsRepository::class,
            \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::class,
            \App\Repositories\Goods\Contracts\GoodsTransferRepository::class,
            \App\Repositories\Goods\Contracts\GoodsOperationRepository::class,
            \App\Repositories\Goods\Contracts\GoodsListRepository::class,
        ];
    }
}
