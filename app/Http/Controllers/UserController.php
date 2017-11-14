<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/25
 * Time: 11:51
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UserService;
use App\Services\System\Version\VersionManageService;
use App\Workflows\UserWorkflow;

class UserController extends Controller
{
    /**
     * 首页面框架输出
     *
     * @param VersionManageService $versionManageService
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function homeView(VersionManageService $versionManageService)
    {
        $version = $versionManageService->getNewVersionInfo();

        return view('common.mainPage-html', ['version' => $version]);
    }

    /**
     * 获取操作员信息
     *
     * @param Request     $request
     * @param UserService $userService
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\AppException
     */
    public function getUserInfo(
        Request $request,
        UserService $userService
    ) {
        $this->validate(
            $request,
            [
                'user_id' => 'integer|between:1,99999999',
            ],
            [
                'user_id.integer' => '权限ID必须是一个整型',
                'user_id.between' => '权限ID必须是:min, 到:max的整数',
            ]
        );

        $userId = $request->has('user_id') ? $request->input('user_id') : '';
        $reData = $userService->getUserInfo($userId);

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 添加操作员账号
     *
     * @param Request     $request
     * @param UserService $userService
     *
     * @return \Illuminate\Http\Response
     */
    public function addUser(
        Request $request,
        UserService $userService
    ) {
        $this->validate(
            $request,
            [
                'user_name'      => 'required|string|between:2,20',
                'login_name'     => 'required|mobile',
                'login_password' => 'required|string|between:6,16',
                'department_id'  => 'required|integer|between:1,999',
            ],
            [
                'user_name.required' => '操作员姓名必须有',
                'user_name.string'   => '操作员姓名必须是一个字符串',
                'user_name.between'  => '操作员姓名长度必须在:min, 到:max之间',

                'login_name.required' => '登录账号名必须有',
                'login_name.mobile'   => '登录账号名必须是一个手机号',

                'login_password.required' => '账号登录密码必须有',
                'login_password.string'   => '账号登录密码必须是一个字符串',
                'login_password.between'  => '账号登录密码长度必须在:min, 到:max之间',

                'department_id.required' => '所属部门必须有',
                'department_id.integer'  => '所属部门ID必须是一个整型',
                'department_id.between'  => '所属部门ID必须是:min, 到:max的整数',
            ]
        );
        $reData = $userService->addUser(
            $request->input('user_name'),
            $request->input('login_name'),
            $request->input('login_password'),
            $request->input('department_id')
        );

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 操作员信息修改(管理员)
     *
     * @param Request     $request
     * @param UserService $userService
     *
     * @return \Illuminate\Http\Response
     */
    public function updateUserInfo(
        Request $request,
        UserService $userService
    ) {
        $this->validate(
            $request,
            [
                'user_id'        => 'required|integer|between:1,9999999',
                'user_name'      => 'required|string|between:2,20',
                'login_name'     => 'required|string|between:3,16',
                'login_password' => 'string|between:6,16',
                'department_id'  => 'required|integer|between:1,999',
            ],
            [
                'user_id.required' => '操作员ID必须有',
                'user_id.integer'  => '操作员ID必须是一个整型',
                'user_id.between'  => '操作员ID必须是:min, 到:max的整数',

                'user_name.required' => '操作员姓名必须有',
                'user_name.string'   => '操作员姓名必须是一个字符串',
                'user_name.between'  => '操作员姓名长度必须在:min, 到:max之间',

                'login_name.required' => '登录账号名必须有',
                'login_name.string'   => '登录账号名必须是一个字符串',
                'login_name.between'  => '登录账号名长度必须在:min, 到:max之间',

                'login_password.string'  => '账号登录密码必须是一个字符串',
                'login_password.between' => '账号登录密码长度必须在:min, 到:max之间',

                'department_id.required' => '所属部门必须有',
                'department_id.integer'  => '所属部门ID必须是一个整型',
                'department_id.between'  => '所属部门ID必须是:min, 到:max的整数',
            ]
        );
        $loginPassword = $request->has('login_password') ? $request->input('login_password') : '';
        $reData = $userService->updateUserInfo(
            $request->input('user_id'),
            $request->input('user_name'),
            $request->input('login_name'),
            $loginPassword,
            $request->input('department_id')
        );

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 操作员账号状态修改
     *
     * @param Request     $request
     * @param UserService $userService
     *
     * @return \Illuminate\Http\Response
     */
    public function updateUserStatus(
        Request $request,
        UserService $userService
    ) {
        $this->validate(
            $request,
            [
                'user_id' => 'required|integer|between:1,9999999',
                'status'  => 'required|integer|between:1,3',
            ],
            [
                'user_id.required' => '操作员ID必须有',
                'user_id.integer'  => '操作员ID必须是一个整型',
                'user_id.between'  => '操作员ID必须是:min, 到:max的整数',

                'status.required' => '所设状态必须有',
                'status.integer'  => '状态必须是一个整型',
                'status.between'  => '状态必须是:min, 到:max的整数',
            ]
        );
        $reData = $userService->updateUserStatus($request->input('user_id'), $request->input('status'));

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    public function getUserList(
        Request $request,
        UserWorkflow $userWorkflow
    ) {
        $this->validate(
            $request,
            [
                'page' => 'required|integer|between:1,99999',
                'size' => 'required|integer|between:1,100',
            ],
            [
                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',
            ]
        );
        $userInfoArr = $request->user()->toArray();
        $reData = $userWorkflow->getUserList($request->input('page'), $request->input('size'), $userInfoArr);

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 操作员所拥有权限获取(返回了所有权限的标记)
     *
     * @param Request     $request
     * @param UserService $userService
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserPrivilege(
        Request $request,
        UserService $userService
    ) {
        $this->validate(
            $request,
            [
                'user_id' => 'required|integer|between:1,999999',
            ],
            [
                'user_id.required' => '操作员ID必须有',
                'user_id.integer'  => '操作员ID必须是一个整型',
                'user_id.between'  => '操作员ID必须是:min, 到:max的整数',
            ]
        );
        $reData = $userService->getUserPrivilege($request->input('user_id'));

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 修改操作员权限
     *
     * @param Request     $request
     * @param UserService $userService
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\User\UserNotExistsException
     */
    public function updateUserPrivilege(
        Request $request,
        UserService $userService
    ) {
        $this->validate(
            $request,
            [
                'user_id'        => 'required|integer|between:1,999999',
                'privilege_tags' => 'string|between:3,10000',
            ],
            [
                'user_id.required' => '操作员ID必须有',
                'user_id.integer'  => '操作员ID必须是一个整型',
                'user_id.between'  => '操作员ID必须是:min, 到:max的整数',

                'privilege_tags.string'  => '操作员所分配权限必须是一个字符串',
                'privilege_tags.between' => '操作员所分配权限串长度必须在:min, 到:max之间',
            ]
        );
        $userPrivilegeTags = $request->has('privilege_tags') ? $request->input('privilege_tags') : '';
        $reData = $userService->updateUserPrivilege($request->input('user_id'), $userPrivilegeTags);

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 当前登录操作员登录密码修改
     *
     * @param Request     $request
     * @param UserService $userService
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\User\UserNotExistsException
     */
    public function updateUserPassword(
        Request $request,
        UserService $userService
    ) {
        $this->validate(
            $request,
            [
                'old_password' => 'required|string|between:6,16',
                'new_password' => 'required|string|confirmed|between:6,16',
            ],
            [
                'old_password.required' => '操作员姓名必须有',
                'old_password.string'   => '操作员姓名必须是一个字符串',
                'old_password.between'  => '操作员姓名长度必须在:min, 到:max之间',

                'new_password.required'  => '账号登录密码必须有',
                'new_password.string'    => '账号登录密码必须是一个字符串',
                'new_password.confirmed' => '两次输出的新密码不同',
                'new_password.between'   => '账号登录密码长度必须在:min, 到:max之间',
            ]
        );
        $reData = $userService->updateUserPassword(
            $request->input('old_password'),
            $request->input('new_password')
        );

        return $this->render(
            'user.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}