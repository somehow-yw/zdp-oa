<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // 处理推荐商品的缓存
        'App\Events\RecommendGoodsUpdate' => [
            'App\Listeners\clearRecommendGoodsCache',
        ],
    ];

    /**
     * 要注册的订阅者
     *
     * @var array
     */
    protected $subscribe = [
        // 订单事件监听 主包内
        'Zdp\Main\Data\Listeners\OrderEventListener',
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
    }
}
