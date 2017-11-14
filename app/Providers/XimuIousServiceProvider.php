<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Ious\Contracts\XimuIousRepository as RepositoriesContract;
use App\Repositories\Ious\XimuIousRepository;

/**
 * 徙木冻品贷数据仓库注入
 * Class XimuIousServiceProvider
 * @package App\Providers
 */
class XimuIousServiceProvider extends ServiceProvider
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
            RepositoriesContract::class,
            XimuIousRepository::class
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
            RepositoriesContract::class,
        ];
    }
}
