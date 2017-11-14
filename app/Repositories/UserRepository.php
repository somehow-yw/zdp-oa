<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/25
 * Time: 12:55
 */

namespace App\Repositories;

use DB;
use Carbon\Carbon;

use App\Repositories\Contracts\UserRepository as RepositoriesContract;

use App\Models\User;
use App\Models\UserActionMap;

class UserRepository implements RepositoriesContract
{
    /**
     * 获取操作员信息 根据ID
     *
     * @see \App\Repositories\Contracts\UserRepository::getUserInfoById()
     *
     * @param int $userId 操作员ID
     *
     * @return object
     */
    public function getUserInfoById($userId)
    {
        $userStatusArr = [
            User::NORMAL_STATUS,
            User::CLOSE_STATUS,
        ];

        return User::where('id', $userId)
            ->whereIn('user_status', $userStatusArr)
            ->first();
    }

    /**
     * 添加操作员账号
     *
     * @see \App\Repositories\Contracts\UserRepository::createUser()
     *
     * @param string $userName     操作员姓名
     * @param string $loginName    操作员登录名
     * @param int    $departmentId 操作员所属部门(组)ID
     * @param string $userPassword 操作员密码
     * @param string $salt         操作员密码成份
     * @param string $remark       备注
     *
     * @return void
     */
    public function createUser($userName, $loginName, $departmentId, $userPassword, $salt, $remark)
    {
        $createUserArr = [
            'user_name'     => $userName,
            'department_id' => $departmentId,
            'login_name'    => $loginName,
            'salt'          => $salt,
            'password'      => $userPassword,
            'remark'        => $remark,
        ];

        User::create($createUserArr);
    }

    /**
     * 操作员信息修改(管理员)
     *
     * @see \App\Repositories\Contracts\UserRepository::updateUserInfo()
     *
     * @param object $userInfoObj  待修改信息的对象
     * @param string $userName     操作员姓名
     * @param string $loginName    操作员登录名
     * @param int    $departmentId 操作员所属部门(组)ID
     * @param string $userPassword 操作员登录密码 不修改为空
     * @param string $salt         操作员密码成份 不修改密码为空
     *
     * @return void
     */
    public function updateUserInfo($userInfoObj, $userName, $loginName, $departmentId, $userPassword = '', $salt = '')
    {
        $superUserArr = User::superActionUsers();     // 具有超级权限的操作员
        $userArr = request()->user();
        if ($userInfoObj->user_name == User::USER_NAME_ADMIN) {
            $userInfoObj->login_name = $loginName;
        } elseif (in_array($userArr->user_name, $superUserArr)) {
            $userInfoObj->user_name = $userName;
            $userInfoObj->login_name = $loginName;
            $userInfoObj->department_id = $departmentId;
        }

        if (!empty($userPassword) && !empty($salt)) {
            $userInfoObj->password = $userPassword;
            $userInfoObj->salt = $salt;
        }

        $userInfoObj->save();
    }

    /**
     * 操作员账号状态修改
     *
     * @see \App\Repositories\Contracts\UserRepository::updateUserStatus()
     *
     * @param object $userInfoObj 待修改的操作员数据对象
     * @param int    $status      修改后的操作员状态
     *
     * @return void
     */
    public function updateUserStatus($userInfoObj, $status)
    {
        $userInfoObj->user_status = $status;
        $userInfoObj->save();
    }

    /**
     * 删除操作员所拥有的权限
     *
     * @see \App\Repositories\Contracts\UserRepository::delUserActionMap()
     *
     * @param int $userId 操作员ID
     *
     * @return void
     */
    public function delUserActionMap($userId)
    {
        UserActionMap::where('user_id', $userId)
            ->delete();
    }

    /**
     * 操作员列表信息获取
     *
     * @see \App\Repositories\Contracts\UserRepository::getUserList()
     *
     * @param int   $size          获取数据量
     * @param array $userStatusArr 可获取的操作员状态 如：[1,2]
     *
     * @return object
     */
    public function getUserList($size, $userStatusArr)
    {
        $userInfoObj = User::with(
            [
                'department' => function ($query) {
                    $query->select(['id', 'department_name']);
                },
            ]
        )
            ->whereIn('user_status', $userStatusArr)
            ->orderBy('id', 'asc')
            ->paginate($size);

        return $userInfoObj;
    }

    /**
     * 返回操作员所拥有的权限标记
     *
     * @see \App\Repositories\Contracts\UserRepository::getUserPrivilege()
     *
     * @param int $userId 操作员ID
     *
     * @return object
     */
    public function getUserPrivilege($userId)
    {
        $userPrivilegeInfo = UserActionMap::where('user_id', $userId)
            ->select(['privilege_tag'])
            ->get();

        return $userPrivilegeInfo ? $userPrivilegeInfo : collect([]);
    }

    /**
     * 修改操作员权限
     *
     * @see \App\Repositories\Contracts\UserRepository::updateUserPrivilege()
     *
     * @param int   $userId              会员ID
     * @param array $userPrivilegeTagArr 所分配的权限标记 如：['user_add','user_update']
     *
     * @return void
     */
    public function updateUserPrivilege($userId, $userPrivilegeTagArr)
    {
        DB::transaction(
            function () use (
                $userId,
                $userPrivilegeTagArr
            ) {
                // 删除当前会员所有的权限
                $this->delUserActionMap($userId);
                // 添加新的权限
                $dateNew = Carbon::now()->format('Y-m-d H:i:s');
                $addArr = [];
                foreach ($userPrivilegeTagArr as $value) {
                    $addArr[] = [
                        'user_id'       => $userId,
                        'privilege_tag' => $value,
                        'created_at'    => $dateNew,
                        'updated_at'    => $dateNew,
                    ];
                }
                if (count($addArr)) {
                    UserActionMap::insert($addArr);
                }
            }
        );
    }

    /**
     * 当前登录操作员登录密码修改
     *
     * @see \App\Repositories\Contracts\UserRepository::updateUserPassword()
     *
     * @param int    $userId       操作员ID
     * @param string $salt         密码成份
     * @param string $userPassword 加密后的密码
     *
     * @return void
     */
    public function updateUserPassword($userId, $salt, $userPassword)
    {
        $updateArr = [
            'salt'     => $salt,
            'password' => $userPassword,
        ];
        User::where('id', $userId)
            ->update($updateArr);
    }

    /**
     * 根据部门(组)ID获得此部门下所有操作员信息
     *
     * @see \App\Repositories\Contracts\UserRepository::getUserInfoByDepartmentId()
     *
     * @param int   $departmentId 部门(组)ID
     * @param array $statusArr    可返回的操作员状态
     *
     * @return object
     */
    public function getUserInfoByDepartmentId($departmentId, $statusArr)
    {
        $userInfo = User::where('department_id', $departmentId)
            ->whereIn('user_status', $statusArr)
            ->get();

        return $userInfo ? $userInfo : collect([]);
    }

    /**
     * 获取指定操作员的电话信息
     *
     * @see \App\Repositories\Contracts\UserRepository::getUserTelByIds()
     *
     * @param array $receiverArr 操作员ID 格式：[1,2,3]
     *
     * @return string 格式：'132xxxxx,132567xxx'
     */
    public function getUserTelByIds($receiverArr)
    {
        $userObj = User::whereIn('id', $receiverArr)
            ->select(DB::raw('GROUP_CONCAT(login_name) as tels'))
            ->first();

        return $userObj->tels;
    }
}
