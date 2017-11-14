<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/22
 * Time: 11:56
 */

namespace App\Repositories\Contracts;

use App\Models\ActionPrivilege;

interface PrivilegeRepository
{
    /**
     * 添加权限
     *
     * @param int    $parentId      父级ID
     * @param string $nodes         所有的可以追溯的父节点串
     * @param string $privilegeName 权限名称
     * @param string $privilegeTag  权限标记
     * @param int    $navigateRank  权限级别
     * @param string $routeTxt      操作路由
     * @param string $remark        权限说明
     * @return object
     */
    public function addPrivilege(
        $parentId,
        $nodes,
        $privilegeName,
        $privilegeTag,
        $navigateRank,
        $routeTxt,
        $remark
    );

    /**
     * 根据ID取得权限信息
     *
     * @param int   $parentId  权限ID
     * @param array $statusArr 需满足的状态 如：[1,2]
     * @return object
     */
    public function getPrivilegeById($parentId, $statusArr = [ActionPrivilege::NORMAL_STATUS]);

    /**
     * 根据权限标记取得权限信息
     *
     * @param string $privilegeTag 权限标记
     * @return object
     */
    public function getPrivilegeByTag($privilegeTag);

    /**
     * 获取满足状态的所有权限信息
     *
     * @param array $privilegeStatusArr 可获取的权限状态
     * @return object
     */
    public function getPrivilegeList($privilegeStatusArr);

    /**
     * 权限状态修改
     *
     * @param object $privilegeInfoObj 待修改记录的对象
     * @param int    $id               权限ID
     * @param int    $status           待修改的状态
     * @return void
     */
    public function updatePrivilegeStatus($privilegeInfoObj, $id, $status);

    /**
     * 权限信息修改
     *
     * @param object $privilegeInfoObj 待修改记录的对象
     * @param string $privilegeName    权限名称
     * @param string $routeTxt         权限路由
     * @param string $remark           备注
     * @return void
     */
    public function updatePrivilege($privilegeInfoObj, $privilegeName, $routeTxt, $remark);

    /**
     * 根据userId 取得会员所有权限
     *
     * @param int $userId 操作员ID
     * @return object
     */
    public function getUserNavigate($userId);
}