<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/27
 * Time: 11:48
 */

namespace App\Workflows;

use App\Services\UserService;
use App\Services\PrivilegeService;

use App\Models\User;

class UserWorkflow
{
    private $userService;

    private $privilegeService;

    public function __construct(
        UserService $userService,
        PrivilegeService $privilegeService
    ) {
        $this->userService = $userService;
        $this->privilegeService = $privilegeService;
    }

    /**
     * 会员列表
     *
     * @param       $page             integer 当前页数
     * @param       $size             integer 获取记录数
     * @param array $loginUserInfoArr array 登录会员的信息
     *
     * @return array
     */
    public function getUserList($page, $size, $loginUserInfoArr)
    {
        // 取得会员信息
        $userStatusArr = [
            User::NORMAL_STATUS,
            User::CLOSE_STATUS,
        ];
        $userInfoArr = $this->userService->getUserList($size, $userStatusArr);
        $userInfoObj = $userInfoArr['data'];
        $reUserInfoArr = [
            'page'       => (int)$page,
            'total'      => $userInfoObj->total(),
            'users'      => [],
            'privileges' => '',
        ];
        if (!$userInfoObj->isEmpty()) {
            $for_n = 0;
            foreach ($userInfoObj as $item) {
                $reUserInfoArr['users'][$for_n]['id'] = $item->id;
                $reUserInfoArr['users'][$for_n]['name'] = $item->user_name;
                $reUserInfoArr['users'][$for_n]['department_id'] = $item->department_id;
                $reUserInfoArr['users'][$for_n]['department_name'] = $item->department->department_name;
                $reUserInfoArr['users'][$for_n]['login_name'] = $item->login_name;
                $reUserInfoArr['users'][$for_n]['we_chat_binding'] = $item->we_chat_binding;
                $reUserInfoArr['users'][$for_n]['status'] = $item->user_status;
                $reUserInfoArr['users'][$for_n]['create_time'] = $item->created_at->format('Y-m-d H:i:s');

                // 当前会员权限获取
                $superActionUserArr = User::superActionUsers();
                $reUserInfoArr['users'][$for_n]['privileges']['navigates'] = '';
                $reUserInfoArr['users'][$for_n]['privileges']['execute_privilege'] = '';
                if (in_array($item->user_name, $superActionUserArr)) {
                    $reUserInfoArr['users'][$for_n]['privileges']['privilege_scope'] = '全部';
                } else {
                    $reUserInfoArr['users'][$for_n]['privileges']['privilege_scope'] = '部分';
                    if ($loginUserInfoArr['id'] == $item->id) {
                        $userPrivilegeArr = $this->privilegeService->getUserPrivilegeByUserId($item->id);
                        $reUserInfoArr['users'][$for_n]['privileges']['navigates'] =
                            $userPrivilegeArr['data']['navigates'];
                        $reUserInfoArr['users'][$for_n]['privileges']['execute_privilege'] =
                            $userPrivilegeArr['data']['execute_privilege'];
                    }
                }
                $for_n++;
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reUserInfoArr,
        ];
    }
}
