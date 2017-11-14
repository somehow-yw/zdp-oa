<?php

namespace App\Services;

use Auth;
use DB;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Auth\Guard;

use App\Hashing\PasswordHasher;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Contracts\UserLogRepository;

use App\Models\User;

use App\Utils\GenerateRandomNumber;

use App\Exceptions\AppException;
use App\Exceptions\Privilege\PrivilegeExceptionCode;
use App\Exceptions\User\UserNotExistsException;

class UserService
{
    /**
     * 会员密码算法
     *
     * @var \App\Hashing\PasswordHasher
     */
    private $hasher;

    private $auth;

    private $userRepo;

    private $userLogRepo;

    public function __construct(
        Hasher $hasher = null,
        Guard $auth,
        UserRepository $userRepo,
        UserLogRepository $userLogRepo
    ) {
        $this->hasher = $hasher ? : new PasswordHasher();
        $this->auth = $auth;
        $this->userRepo = $userRepo;
        $this->userLogRepo = $userLogRepo;
    }

    /**
     * 会员登录
     *
     * @param string $username  登录名
     * @param string $password  登录密码
     * @param string $userIp    操作员IP
     * @param string $routeUses 操作的路由
     * @param bool   $remember  true if login successfully, false otherwise
     *
     * @return bool
     */
    public function login($username, $password, $userIp, $routeUses, $remember = false)
    {
        $logStatus = $this->auth->check();
        // 记录操作员日志
        $this->genUserLog($logStatus, $routeUses, $userIp, $username);

        if ($logStatus) {
            return true;
            // throw new UserNotExistsException('您已登录');
        }

        return Auth::attempt(
            [
                'login_name' => $username,
                'password'   => $password,
            ],
            $remember
        );
    }

    /**
     * 记录操作员日志
     *
     * @param $logStatus
     * @param $routeUses
     * @param $userIp
     * @param $username
     */
    private function genUserLog($logStatus, $routeUses, $userIp, $username)
    {
        if ($logStatus) {
            $userArr = $this->auth->user()->toArray();
        } else {
            $userArr = [
                'id'         => 0,
                'user_name'  => '进行登录',
                'login_name' => $username,
            ];
        }
        $logDate = date('Y-m-d');
        $this->userLogRepo->genUserLog(
            $userArr['id'],
            $userArr['user_name'],
            $userArr['login_name'],
            $routeUses,
            $userIp,
            $logDate
        );
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Auth::logout();
    }

    /**
     * 获取操作员信息
     *
     * @param int $userId 操作员ID
     *
     * @return array
     * @throws AppException UserNotExistsException
     */
    public function getUserInfo($userId)
    {
        $userInfoArr = $this->auth->user()->toArray();
        $reUserInfoArr = [];
        if (empty($userId)) {
            $reData['user_infos']['name'] = $userInfoArr['user_name'];
            $reData['user_infos']['department_id'] = $userInfoArr['department_id'];
            $reData['user_infos']['login_name'] = $userInfoArr['login_name'];
        } elseif ($userInfoArr['user_name'] == 'admin' || $userInfoArr['user_name'] == 'root') {
            $userInfoObj = $this->userRepo->getUserInfoById($userId);
            if (!$userInfoObj) {
                throw new UserNotExistsException();
            }
            $reData['user_infos']['name'] = $userInfoObj->user_name;
            $reData['user_infos']['department_id'] = $userInfoObj->department_id;
            $reData['user_infos']['login_name'] = $userInfoObj->login_name;
        } else {
            throw new AppException('没有权限', PrivilegeExceptionCode::NOT_PRIVILEGE);
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 添加操作员账号
     *
     * @param string $userName      操作员姓名
     * @param string $loginName     操作员登录账号
     * @param string $loginPassword 操作员登录密码
     * @param int    $departmentId  操作员所属部b门(组)ID
     *
     * @return array
     */
    public function addUser(
        $userName,
        $loginName,
        $loginPassword,
        $departmentId
    ) {
        $userInfoArr = $this->auth->user()->toArray();
        // 生成随机salt
        $salt = GenerateRandomNumber::generateString(16);
        // 密码处理
        $userPassword = strtoupper(md5($salt . $loginPassword));
        // 备注信息
        $remark = "{$userInfoArr['user_name']}({$userInfoArr['login_name']})添加";
        $this->userRepo->createUser($userName, $loginName, $departmentId, $userPassword, $salt, $remark);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 操作员信息修改(管理员)
     *
     * @param int    $userId        待修改操作员ID
     * @param string $userName      操作员名称
     * @param string $loginName     操作员登录账号
     * @param string $loginPassword 操作员登录密码
     * @param int    $departmentId  操作员所属部门(组)
     *
     * @return array
     * @throws UserNotExistsException
     */
    public function updateUserInfo(
        $userId,
        $userName,
        $loginName,
        $loginPassword,
        $departmentId
    ) {
        $notUpdateUserArr = User::notUpdateUsers();
        $superUserArr = User::superActionUsers();     // 具有超级权限的操作员
        $userArr = request()->user();
        if (!in_array($userArr->user_name, $superUserArr) && $userArr->id != $userId) {
            throw new UserNotExistsException('非法操作');
        }
        $userInfoObj = $this->userRepo->getUserInfoById($userId);
        if (!$userInfoObj) {
            throw new UserNotExistsException();
        } elseif (in_array($userInfoObj->user_name, $notUpdateUserArr)) {
            throw new UserNotExistsException('此操作员信息不可修改');
        } elseif ($userInfoObj->user_name != User::USER_NAME_ADMIN && in_array($userName, $superUserArr)) {
            throw new UserNotExistsException('操作员名称非法');
        }

        $userPassword = '';
        $salt = '';
        if (!empty($loginPassword)) {
            // 生成随机salt
            $salt = GenerateRandomNumber::generateString(16);
            // 密码处理
            $userPassword = strtoupper(md5($salt . $loginPassword));
        }

        $this->userRepo->updateUserInfo($userInfoObj, $userName, $loginName, $departmentId, $userPassword, $salt);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 操作员账号状态修改
     *
     * @param int $userId 会员ID
     * @param int $status 修改后状态
     *
     * @return array
     * @throws UserNotExistsException
     */
    public function updateUserStatus($userId, $status)
    {
        $notUpdateUserArr = User::notDeleteUsers();
        $userInfoObj = $this->userRepo->getUserInfoById($userId);
        if (!$userInfoObj) {
            throw new UserNotExistsException();
        } elseif (in_array($userInfoObj->user_name, $notUpdateUserArr)) {
            throw new UserNotExistsException('此操作员信息不可修改');
        }

        $self = $this;
        DB::transaction(
            function () use (
                $self,
                $userInfoObj,
                $userId,
                $status
            ) {
                $self->userRepo->updateUserStatus($userInfoObj, $status);
                if ($status == User::DELETE_STATUS) {
                    // 如果是删除操作员，就要相应的删除其权限
                    $self->userRepo->delUserActionMap($userId);
                }
            }
        );

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 操作员列表信息获取
     *
     * @param int   $size          获取数据量
     * @param array $userStatusArr 可获取的操作员状态 如：[1,2]
     *
     * @return array
     */
    public function getUserList($size, $userStatusArr)
    {
        $userInfoListObj = $this->userRepo->getUserList($size, $userStatusArr);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $userInfoListObj,
        ];
    }

    /**
     * 操作员所拥有权限获取(返回了所有权限的标记)
     *
     * @param int $userId 会员ID
     *
     * @return array
     * @throws UserNotExistsException
     */
    public function getUserPrivilege($userId)
    {
        $notUpdateUserArr = User::superActionUsers();
        $userInfoObj = $this->userRepo->getUserInfoById($userId);
        if (!$userInfoObj) {
            throw new UserNotExistsException();
        } elseif (in_array($userInfoObj->user_name, $notUpdateUserArr)) {
            throw new UserNotExistsException('此操作员不需要此信息');
        }

        $userPrivilegeObj = $this->userRepo->getUserPrivilege($userId);
        $userPrivilegeArr = [];
        if (!$userPrivilegeObj->isEmpty()) {
            foreach ($userPrivilegeObj as $item) {
                $userPrivilegeArr['user_tags'][] = $item->privilege_tag;
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $userPrivilegeArr,
        ];
    }

    /**
     * 修改操作员权限
     *
     * @param int    $userId            会员ID
     * @param string $userPrivilegeTags 所分配的权限标记串 如：'user_add,user_update'
     *
     * @return array
     * @throws UserNotExistsException
     */
    public function updateUserPrivilege($userId, $userPrivilegeTags)
    {
        $notUpdateUserArr = User::superActionUsers();
        $userInfoObj = $this->userRepo->getUserInfoById($userId);
        if (!$userInfoObj) {
            throw new UserNotExistsException();
        } elseif (in_array($userInfoObj->user_name, $notUpdateUserArr)) {
            throw new UserNotExistsException('此操作员权限不可更改');
        }

        $userPrivilegeTagArr = empty($userPrivilegeTags) ? [] : explode(',', $userPrivilegeTags);
        $this->userRepo->updateUserPrivilege($userId, $userPrivilegeTagArr);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 当前登录操作员登录密码修改
     *
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     *
     * @return array
     * @throws UserNotExistsException
     */
    public function updateUserPassword($oldPassword, $newPassword)
    {
        $userInfoArr = $this->auth->user()->toArray();
        $oldPasswordAuth = $this->login($userInfoArr['login_name'], $oldPassword);
        if (!$oldPasswordAuth) {
            throw new UserNotExistsException('旧密码不正确');
        }
        // 生成随机salt
        $salt = GenerateRandomNumber::generateString(16);
        // 密码处理
        $userPassword = strtoupper(md5($salt . $newPassword));
        $this->userRepo->updateUserPassword($userInfoArr['id'], $salt, $userPassword);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 根据部门(组)ID获得此部门下所有操作员信息
     *
     * @param int $departmentId 部门(组)ID
     *
     * @return object
     */
    public function getUserInfoByDepartmentId($departmentId)
    {
        $statusArr = [
            User::NORMAL_STATUS,
            User::CLOSE_STATUS,
        ];
        $userInfoObj = $this->userRepo->getUserInfoByDepartmentId($departmentId, $statusArr);

        return $userInfoObj;
    }
}
