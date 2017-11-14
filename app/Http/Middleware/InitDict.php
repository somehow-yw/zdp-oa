<?php

namespace App\Http\Middleware;

use App\Exceptions\AppException;
use Closure;
use Cache;
use Artisan;
use Log;

class InitDict
{
    /**
     * 最大索引锁时间，单位分钟
     */
    const MAX_INDEX_INIT_TIME = 1;
    /**
     * 索引锁key
     */
    const INDEX_LOCK_KEY = "dict_init_lock";

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
            throw  new AppException("字典还在建立，请稍后再试");
        }
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
        //Call index init Artisan command
        Artisan::call('search:dict:init');

        //Index init completed,release lock
        Cache::put(self::INDEX_LOCK_KEY, false, self::MAX_INDEX_INIT_TIME);

        Log::info("search dict init done!");
    }
}
