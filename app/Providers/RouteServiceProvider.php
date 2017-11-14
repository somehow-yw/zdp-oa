<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $router->pattern('method', '\w+');
        $router->pattern('uid', '\d+');
        $router->pattern('id', '\d+');
        $router->pattern('area_id', '\d+');

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Routes/routes.php');
        });

        $router->group(
            [],
            function ($router) {
                require app_path('Routes/logistics.php');
            }
        );

        $router->group(
            ['namespace' => $this->namespace . '\ProviderService'],
            function ($router) {
                require app_path('Routes/provider.php');
            }
        );

        $router->group([], function ($router) {
            require app_path('Routes/bi.php');
        });

        // 营销中心
        $router->group(
            ['namespace' => $this->namespace . '\MarketingCenter'],
            function ($router) {
                require app_path('Routes/marketing.php');
            }
        );

        // 外部供应链
        $router->group(
            ['namespace' => $this->namespace],
            function ($router) {
                require app_path('Routes/external.php');
            }
        );
    }
}
