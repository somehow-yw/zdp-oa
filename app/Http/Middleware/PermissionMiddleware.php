<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Responses\ResponseFormatTrait;

use App\Models\UserActionMap;
use App\Models\User;

use App\Exceptions\User\UserNotExistsException;
use App\Exceptions\Privilege\PrivilegeExceptionCode;

class PermissionMiddleware
{
    use ResponseFormatTrait;

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $actions = $request->route()->getAction();

        if (empty($actions['permissions'])) {
            // 不需要权限
            return $next($request);
        }
        $permits = $actions['permissions'];

        $userArr = $this->auth->user()->toArray();

        $superActionUserArr = User::superActionUsers();     // 具有超级权限的操作员
        // 只要有一个有权限，就可以进入请求
        if ('root' == $userArr['user_name'] && in_array($userArr['user_name'], $permits)) {
            return $next($request);
        } elseif (in_array($userArr['user_name'], $superActionUserArr)) {
            return $next($request);
        }
        if ($this->hasPermits($userArr['id'], $permits)) {
            return $next($request);
        }

        return $this->render('errors.custom', [], '没有权限', PrivilegeExceptionCode::NOT_PRIVILEGE);
    }

    /**
     * 取得当前会员是否拥有当前权限
     *
     * @param int   $userId  当前会员ID
     * @param array $permits array 权限标记
     *
     * @return int
     */
    private function hasPermits($userId, $permits)
    {
        return UserActionMap::where('user_id', $userId)
            ->whereIn('privilege_tag', $permits)
            ->count();
    }

    /**
     * 根据登录者ID取得登录者信息
     *
     * @param int $userId 登录者ID
     *
     * @throws UserNotExistsException
     * @return object
     */
    private function getUserInfo($userId)
    {
        $userInfo = User::where('id', $userId)
            ->first();
        if (!$userInfo) {
            throw new UserNotExistsException();
        }

        return $userInfo;
    }
}
