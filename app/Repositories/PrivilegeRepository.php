<?php

/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/22
 * Time: 12:01
 */

namespace App\Repositories;

use DB;
use App\Models\ActionPrivilege;

use App\Repositories\Contracts\PrivilegeRepository as RepositoriesContract;

class PrivilegeRepository implements RepositoriesContract
{
    /**
     * 添加权限
     *
     * @see \App\Repositories\Contracts\PrivilegeRepository::addPrivilege()
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
    ) {
        $addArr = [
            'parent_id'      => $parentId,
            'nodes'          => $nodes,
            'privilege_name' => $privilegeName,
            'privilege_tag'  => $privilegeTag,
            'navigate_rank'  => $navigateRank,
            'route'          => $routeTxt,
            'remark'         => $remark,
        ];

        return ActionPrivilege::create($addArr);
    }

    /**
     * 根据ID取得权限信息
     *
     * @see \App\Repositories\Contracts\PrivilegeRepository::getPrivilegeById()
     * @param int   $parentId  权限ID
     * @param array $statusArr 需满足的状态 如：[1,2]
     * @return object
     */
    public function getPrivilegeById($parentId, $statusArr = [ActionPrivilege::NORMAL_STATUS])
    {
        return ActionPrivilege::where('id', $parentId)
            ->whereIn('status', $statusArr)
            ->first();
    }

    /**
     * 根据权限标记取得权限信息
     *
     * @see \App\Repositories\Contracts\PrivilegeRepository::getPrivilegeByTag()
     * @param string $privilegeTag 权限标记
     * @return object
     */
    public function getPrivilegeByTag($privilegeTag)
    {
        return ActionPrivilege::where('privilege_tag', $privilegeTag)
            ->first();
    }

    /**
     * 获取满足状态的所有权限信息
     *
     * @see \App\Repositories\Contracts\PrivilegeRepository::getPrivilegeList()
     * @param array $privilegeStatusArr 可获取的权限状态
     * @return object
     */
    public function getPrivilegeList($privilegeStatusArr)
    {
        $dataObj = ActionPrivilege::whereIn('status', $privilegeStatusArr)
            ->orderBy('navigate_rank', 'asc')
            ->orderBy('sort', 'asc')
            ->get();

        return $dataObj ? $dataObj : collect([]);
    }

    /**
     * 权限状态修改
     *
     * @see \App\Repositories\Contracts\PrivilegeRepository::updatePrivilegeStatus()
     * @param object $privilegeInfoObj 待修改记录的对象
     * @param int    $id               权限ID
     * @param int    $status           待修改的状态
     * @return void
     */
    public function updatePrivilegeStatus($privilegeInfoObj, $id, $status)
    {
        DB::transaction(
            function () use (
                $privilegeInfoObj,
                $id,
                $status
            ) {
                $privilegeInfoObj->status = $status;
                $privilegeInfoObj->save();
                // 所有下级权限状态的更改
                ActionPrivilege::where(DB::raw("FIND_IN_SET('{$privilegeInfoObj->id}',nodes)"), '>', 0)
                    ->update(['status' => $status]);
            }
        );
    }

    /**
     * 权限信息修改
     *
     * @see \App\Repositories\Contracts\PrivilegeRepository::updatePrivilege()
     * @param object $privilegeInfoObj 待修改记录的对象
     * @param string $privilegeName    权限名称
     * @param string $routeTxt         权限路由
     * @param string $remark           备注
     * @return void
     */
    public function updatePrivilege($privilegeInfoObj, $privilegeName, $routeTxt, $remark)
    {
        $privilegeInfoObj->privilege_name = $privilegeName;
        $privilegeInfoObj->route = $routeTxt;
        $privilegeInfoObj->remark = $remark;
        $privilegeInfoObj->save();
    }

    /**
     * 根据userId 取得会员所有权限
     *
     * @see \App\Repositories\Contracts\PrivilegeRepository::getUserNavigate()
     * @param int $userId 操作员ID
     * @return object
     */
    public function getUserNavigate($userId)
    {
        $query = DB::table('user_action_maps as map');
        $query = $query->join('action_privileges as action', 'map.privilege_tag', '=', 'action.privilege_tag');
        $query = $query->select(
            'action.id',
            'action.parent_id',
            'action.nodes',
            'action.privilege_name',
            'action.privilege_tag',
            'action.navigate_rank',
            'action.route'
        );
        $query = $query->where('map.user_id', $userId);
        $query = $query->where('action.status', ActionPrivilege::NORMAL_STATUS);
        $query = $query->orderBy('action.navigate_rank', 'asc');
        $query = $query->orderBy('action.sort', 'asc');
        $query = $query->orderBy('action.id', 'asc');
        $dataObj = $query->get();

        return $dataObj ? $dataObj : [];
    }
}
