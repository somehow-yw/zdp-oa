<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DailyNewsServiceProvider extends ServiceProvider
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
            \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::class,
            \App\Repositories\DailyNews\DailyNewsInfoRepository::class
        );
        $this->app->singleton(
            \App\Repositories\DailyNews\Contracts\DailyNewsLogRepository::class,
            \App\Repositories\DailyNews\DailyNewsLogRepository::class
        );
        $this->app->singleton(
            \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::class,
            \App\Repositories\DailyNews\DailyNewsGodsRepository::class
        );
        $this->app->singleton(
            \App\Repositories\DailyNews\Contracts\DailyNewsDeclineGoodsRepository::class,
            \App\Repositories\DailyNews\DailyNewsDeclineGoodsRepository::class
        );
        $this->app->singleton(
            \App\Repositories\DailyNews\Contracts\DailyNewsRiseGoodsRepository::class,
            \App\Repositories\DailyNews\DailyNewsRiseGoodsRepository::class
        );
        $this->app->singleton(
            \App\Repositories\DailyNews\Contracts\NewsManageRepository::class,
            \App\Repositories\DailyNews\NewsManageRepository::class
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
            \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::class,
            \App\Repositories\DailyNews\Contracts\DailyNewsLogRepository::class,
            \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::class,
            \App\Repositories\DailyNews\Contracts\DailyNewsDeclineGoodsRepository::class,
            \App\Repositories\DailyNews\Contracts\DailyNewsRiseGoodsRepository::class,
            \App\Repositories\DailyNews\Contracts\NewsManageRepository::class,
        ];
    }
}
