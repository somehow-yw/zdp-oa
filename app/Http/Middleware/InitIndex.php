<?php

namespace App\Http\Middleware;

use App\Exceptions\AppException;
use Closure;
use Cache;
use Artisan;
use Log;

class InitIndex
{
    /**
     * 最大索引锁时间，单位分钟
     */
    const MAX_INDEX_INIT_TIME = 30;
    /**
     * 索引锁key
     */
    const INDEX_LOCK_KEY = "index_init_lock";

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws AppException
     */
    public function handle($request, Closure $next)
    {
        //See if there still lock for indexing
        $indexLock = Cache::get(self::INDEX_LOCK_KEY, false);
        if ($indexLock) {
            throw  new AppException("索引还在建立，请稍后再试");
        }
        set_time_limit(self::MAX_INDEX_INIT_TIME * 60);
        //Begin index,enable index lock
        Cache::put(self::INDEX_LOCK_KEY, true, self::MAX_INDEX_INIT_TIME);

        return $next($request);
    }

    /**
     * after send init success result response
     *
     * @param $request
     * @param $response
     */
    public function terminate($request, $response)
    {
        //Call dict init Artisan command
        Artisan::call('search:dict:init');

        //Call index init Artisan command
        Artisan::call('search:index:init');

        //Index init completed,release lock
        Cache::put(self::INDEX_LOCK_KEY, false, self::MAX_INDEX_INIT_TIME);

        Log::info("search index init done!");
    }
}
