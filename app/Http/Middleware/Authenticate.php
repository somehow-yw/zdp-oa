<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Responses\ResponseFormatTrait;

use App\Services\UserLogService;

use App\Exceptions\ExceptionCode;

class Authenticate
{
    use ResponseFormatTrait;

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    private $userLogService;

    /**
     * Create a new middleware instance.
     *
     * @param  Guard         $auth
     * @param UserLogService $userLogService
     */
    public function __construct(
        Guard $auth,
        UserLogService $userLogService
    ) {
        $this->auth = $auth;
        $this->userLogService = $userLogService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('auth/login');
            }
        }*/
        $logStatus = $this->auth->check();
        // 记录操作员日志
        $this->genUserLog($request, $logStatus);

        if ( ! $logStatus) {
            $requestType = request()->format();
            if($requestType != 'json'){
                return redirect('/');
            } else {
                return $this->render('errors.custom', [], '您尚未登录', ExceptionCode::USER_LOGIN_NOT);
            }
        }

        return $next($request);
    }

    /**
     * 记录操作员日志
     *
     * @param $request
     * @param $logStatus
     */
    private function genUserLog($request, $logStatus)
    {
        if ($logStatus) {
            $userArr = $this->auth->user()->toArray();
        } else {
            $userArr = [
                'id'         => 0,
                'user_name'  => '未登录',
                'login_name' => '未登录',
            ];
        }
        $userIp = $request->getClientIp();
        $actions = $request->route()->getAction();
        if ($actions['uses'] instanceof Closure) {
            $routeUses = 'user/home';
        } else {
            $routeUses = str_replace('App\\Http\\Controllers\\', '', $actions['uses']);
        }
        $this->userLogService->genUserLog(
            $userArr['id'],
            $userArr['user_name'],
            $userArr['login_name'],
            $routeUses,
            $userIp
        );
    }
}
