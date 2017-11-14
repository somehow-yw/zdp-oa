<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Exceptions\ExceptionCode;

class AuthController extends Controller
{
    /**
     * 用户登录
     *
     * @param Request     $request
     * @param UserService $userService
     * @param Guard       $auth
     * @return \Illuminate\Http\Response
     */
    public function login(
        Request $request,
        UserService $userService,
        Guard $auth
    ) {
        $this->validate(
            $request,
            [
                'login_name'     => 'required|string|between:1,20',
                'login_password' => 'required|string|between:6,32',
            ],
            [
                'login_name.required'     => '登录名必填',
                'login_name.between'      => '登录名长度必须大于:min, 小于:max',
                'login_password.required' => '登录密码必填',
                'login_password.between'  => '登录密码错误',
            ]
        );

        $userIp = $request->getClientIp();
        $actions = $request->route()->getAction();
        $routeUses = str_replace('App\\Http\\Controllers\\', '', $actions['uses']);

        $success = $userService->login(
            $request->input('login_name'),
            $request->input('login_password'),
            $userIp,
            $routeUses,
            $request->has('remember')
        );
        if ( ! $success) {
            return $this->renderError('用户名或密码错误', ExceptionCode::USER_UNAUTH);
        }

        return $this->renderInfo('登录成功');
    }

    /**
     * 退出登录
     *
     * @param Request     $request
     * @param Guard       $auth
     * @param UserService $userService
     * @return \Illuminate\Http\Response
     */
    public function logout(
        Request $request,
        Guard $auth,
        UserService $userService
    ) {
        if ($auth->guest()) {
            return $this->renderInfo('退出成功');
        }

        $userService->logout();

        return $this->renderInfo('退出成功');
    }
}
