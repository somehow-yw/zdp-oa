<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/25
 * Time: 12:55
 */

namespace App\Repositories\Contracts;

interface UserRepository
{
    /**
     * 获取操作员信息 根据ID
     *
     * @param int $userId 操作员ID
     * @return object
     */
    public function getUserInfoById($userId);

    /**
     * 添加操作员账号
     *
     * @param string $userName     操作员姓名
     * @param string $loginName    操作员登录名
     * @param int    $departmentId 操作员所属部门(组)ID
     * @param string $userPassword 操作员密码
     * @param string $salt         操作员密码成份
     * @param string $remark       备注
     * @return void
     */
    public function createUser($userName, $loginName, $departmentId, $userPassword, $salt, $remark);

    /**
     * 操作员信息修改(管理员)
     *
     * @param object $userInfoObj  待修改信息的对象
     * @param string $userName     操作员姓名
     * @param string $loginName    操作员登录名
     * @param int    $departmentId 操作员所属部门(组)ID
     * @param string $userPassword 操作员登录密码 不修改为空
     * @param string $salt         操作员密码成份 不修改密码为空
     * @return void
     */
    public function updateUserInfo($userInfoObj, $userName, $loginName, $departmentId, $userPassword = '', $salt = '');

    /**
     * 操作员账号状态修改
     *
     * @param object $userInfoObj 待修改的操作员数据对象
     * @param int    $status      修改后的操作员状态
     * @return void
     */
    public function updateUserStatus($userInfoObj, $status);

    /**
     * 删除操作员所拥有的权限
     *
     * @param int $userId 操作员ID
     * @return void
     */
    public function delUserActionMap($userId);

    /**
     * 操作员列表信息获取
     *
     * @param int   $size          获取数据量
     * @param array $userStatusArr 可获取的操作员状态 如：[1,2]
     * @return object
     */
    public function getUserList($size, $userStatusArr);

    /**
     * 返回操作员所拥有的权限标记
     *
     * @param int $userId 操作员ID
     * @return object
     */
    public function getUserPrivilege($userId);

    /**
     * 修改操作员权限
     *
     * @param int   $userId              会员ID
     * @param array $userPrivilegeTagArr 所分配的权限标记 如：['user_add','user_update']
     * @return void
     */
    public function updateUserPrivilege($userId, $userPrivilegeTagArr);

    /**
     * 当前登录操作员登录密码修改
     *
     * @param int    $userId       操作员ID
     * @param string $salt         密码成份
     * @param string $userPassword 加密后的密码
     * @return void
     */
    public function updateUserPassword($userId, $salt, $userPassword);

    /**
     * 根据部门(组)ID获得此部门下所有操作员信息
     *
     * @param int   $departmentId 部门(组)ID
     * @param array $statusArr    可返回的操作员状态
     * @return object
     */
    public function getUserInfoByDepartmentId($departmentId, $statusArr);

    /**
     * 获取指定操作员的电话信息
     *
     * @param array $receiverArr 操作员ID 格式：[1,2,3]
     *
     * @return string 格式：'132xxxxx,132567xxx'
     */
    public function getUserTelByIds($receiverArr);
}