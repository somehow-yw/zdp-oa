<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/29/16
 * Time: 1:33 PM
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ActivityServiceProvider extends ServiceProvider
{
    /**
     * 服务提供者加是否延迟加载.
     *
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(
            \App\Repositories\Goods\Contracts\ActivityRepository::class,
            \App\Repositories\Goods\ActivityRepository::class
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [\App\Repositories\Goods\Contracts\ActivityRepository::class];
    }

}