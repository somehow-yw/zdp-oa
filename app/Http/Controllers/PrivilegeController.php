<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/22
 * Time: 11:09
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

use App\Services\PrivilegeService;

class PrivilegeController extends Controller
{
    /**
     * 权限添加
     *
     * @param Request          $request
     * @param PrivilegeService $privilegeService
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\AppException
     */
    public function addPrivilege(
        Request $request,
        PrivilegeService $privilegeService
    ) {
        $this->validate(
            $request,
            [
                'parent_id'      => 'required|integer|between:0,9999999',
                'privilege_name' => 'required|string|between:1,50',
                'privilege_tag'  => 'required|string|between:3,100',
                'navigate_rank'  => 'required|integer|between:0,99',
                'route'          => 'string|between:1,255',
                'remark'         => 'required|string|between:2,255',
            ],
            [
                'parent_id.required' => '父级ID必须有',
                'parent_id.integer'  => '父级ID必须是一个整型',
                'parent_id.between'  => '父级ID必须是:min, 到:max的整数',

                'privilege_name.required' => '权限名称必须有',
                'privilege_name.string'   => '权限名称必须是一个字符串',
                'privilege_name.between'  => '权限名称长度必须在:min, 到:max之间',

                'privilege_tag.required' => '权限标记必须有',
                'privilege_tag.string'   => '权限标记必须是一个字符串',
                'privilege_tag.between'  => '权限标记长度必须在:min, 到:max之间',

                'navigate_rank.required' => '权限级别必须有',
                'navigate_rank.integer'  => '权限级别必须是一个整型',
                'navigate_rank.between'  => '权限级别必须是:min, 到:max的整数',

                'route.string'  => '导航路由必须是一个字符串',
                'route.between' => '导航路由长度必须在:min, 到:max之间',

                'remark.required' => '权限备注必须有',
                'remark.string'   => '权限备注必须是一个字符串',
                'remark.between'  => '权限备注长度必须在:min, 到:max之间',
            ]
        );

        $routeTxt = $request->has('route') ? $request->input('route') : '';
        $reData = $privilegeService->addPrivilege(
            $request->input('parent_id'),
            $request->input('privilege_name'),
            $request->input('privilege_tag'),
            $request->input('navigate_rank'),
            $routeTxt,
            $request->input('remark')
        );

        return $this->render(
            'privilege.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 权限列表
     *
     * @param Request          $request
     * @param PrivilegeService $privilegeService
     * @return \Illuminate\Http\Response
     */
    public function getPrivilegeList(
        Request $request,
        PrivilegeService $privilegeService
    ) {
        $reData = $privilegeService->getPrivilegeList();

        return $this->render(
            'privilege.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 权限状态修改
     *
     * @param Request          $request
     * @param PrivilegeService $privilegeService
     * @return \Illuminate\Http\Response
     */
    public function updatePrivilegeStatus(
        Request $request,
        PrivilegeService $privilegeService
    ) {
        $this->validate(
            $request,
            [
                'id'     => 'required|integer|between:1,99999999',
                'status' => 'required|string|between:1,2',
            ],
            [
                'id.required' => '权限ID必须有',
                'id.integer'  => '权限ID必须是一个整型',
                'id.between'  => '权限ID必须是:min, 到:max的整数',

                'status.required' => '权限状态必须有',
                'status.integer'  => '权限状态必须是一个整型',
                'status.between'  => '权限状态必须是:min, 到:max的整数',
            ]
        );

        $reData = $privilegeService->updatePrivilegeStatus($request->input('id'), $request->input('status'));

        return $this->render(
            'privilege.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 权限信息获取
     *
     * @param Request          $request
     * @param PrivilegeService $privilegeService
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\AppException
     */
    public function getPrivilegeInfo(
        Request $request,
        PrivilegeService $privilegeService
    ) {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|between:1,99999999',
            ],
            [
                'id.required' => '权限ID必须有',
                'id.integer'  => '权限ID必须是一个整型',
                'id.between'  => '权限ID必须是:min, 到:max的整数',
            ]
        );

        $reData = $privilegeService->getPrivilegeInfo($request->input('id'));

        return $this->render(
            'privilege.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 权限信息修改
     *
     * @param Request          $request
     * @param PrivilegeService $privilegeService
     * @return \Illuminate\Http\Response
     */
    public function updatePrivilege(
        Request $request,
        PrivilegeService $privilegeService
    ) {
        $this->validate(
            $request,
            [
                'id'             => 'required|integer|between:1,99999999',
                'privilege_name' => 'required|string|between:1,50',
                'route'          => 'string|between:1,255',
                'remark'         => 'required|string|between:2,255',
            ],
            [
                'id.required' => '权限ID必须有',
                'id.integer'  => '权限ID必须是一个整型',
                'id.between'  => '权限ID必须是:min, 到:max的整数',

                'privilege_name.required' => '权限名称必须有',
                'privilege_name.string'   => '权限名称必须是一个字符串',
                'privilege_name.between'  => '权限名称长度必须在:min, 到:max之间',

                'route.string'  => '导航路由必须是一个字符串',
                'route.between' => '导航路由长度必须在:min, 到:max之间',

                'remark.required' => '权限备注必须有',
                'remark.string'   => '权限备注必须是一个字符串',
                'remark.between'  => '权限备注长度必须在:min, 到:max之间',
            ]
        );

        $routeTxt = $request->has('route') ? $request->input('route') : '';
        $reData = $privilegeService->updatePrivilege(
            $request->input('id'),
            $request->input('privilege_name'),
            $routeTxt,
            $request->input('remark')
        );

        return $this->render(
            'privilege.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    // 获取登录会员的权限
    public function getUserNavigate(
        Request $request,
        PrivilegeService $privilegeService,
        Guard $auth
    ) {
        $userInfoArr = $auth->user()->toArray();
        $reData = $privilegeService->getUserNavigate($userInfoArr);

        return $this->render(
            'privilege.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}