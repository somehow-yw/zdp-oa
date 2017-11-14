<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\ExceptionCode;
use App\Exceptions\AppException;

class ForceRequestAcceptHeaderMiddleware 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string http request accept format, valid value:
     *                  json|xml|html|txt
     * @return mixed
     */
    public function handle($request, Closure $next, $format)
    {
        // 检查类型是否存在，存在就返回一个MIme类型
        $mineType = $request->getMimeType($format);
        if ( ! $mineType) {
            throw new AppException('wrong force accept format');
        }

        // 设置头部
        $request->headers->set('Accept', $mineType);

        return $next($request);
    }
}
