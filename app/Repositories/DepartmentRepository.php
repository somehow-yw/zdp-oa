<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/27
 * Time: 19:51
 */

namespace App\Repositories;

use DB;

use App\Repositories\Contracts\DepartmentRepository as RepositoriesContract;

use App\Models\Department;

class DepartmentRepository implements RepositoriesContract
{
    /**
     * 获取部门信息 根据部门名称
     *
     * @see \App\Repositories\Contracts\DepartmentRepository::getDepartmentInfoByName()
     * @param string $departmentName 部门名称
     * @return object
     */
    public function getDepartmentInfoByName($departmentName)
    {
        return Department::where('department_name', $departmentName)
            ->first();
    }

    /**
     * 添加部门(组)
     *
     * @see \App\Repositories\Contracts\DepartmentRepository::addDepartment()
     * @param string $departmentName 部门名称
     * @param string $remark         备注信息
     * @return void
     */
    public function addDepartment($departmentName, $remark)
    {
        $addArr = [
            'department_name' => $departmentName,
            'remark'          => $remark,
        ];
        Department::create($addArr);
    }

    /**
     * 部门(组)列表
     *
     * @see \App\Repositories\Contracts\DepartmentRepository::getDepartmentList()
     * @param int   $size      返回数据量
     * @param array $statusArr 需满足的部门(组)状态 格式：[1,2]
     * @return object
     */
    public function getDepartmentList($size, $statusArr)
    {
        $departmentInfoObj = Department::whereIn('status', $statusArr)
            ->orderBy('id', 'asc')
            ->paginate($size);

        return $departmentInfoObj;
    }

    /**
     * 部门(组)信息获取 根据ID
     *
     * @see \App\Repositories\Contracts\DepartmentRepository::getDepartmentInfoById()
     * @param int $departmentId 部门(组)ID
     * @return object
     */
    public function getDepartmentInfoById($departmentId)
    {
        return Department::where('id', $departmentId)
            ->first();
    }

    /**
     * 部门(组)信息修改
     *
     * @see \App\Repositories\Contracts\DepartmentRepository::updateDepartmentInfo()
     * @param object $departmentInfoObj 数据对象 Department Model Object
     * @param string $departmentName    修改后的部门(组)名称
     * @return void
     */
    public function updateDepartmentInfo($departmentInfoObj, $departmentName)
    {
        $departmentInfoObj->department_name = $departmentName;
        $departmentInfoObj->save();
    }

    /**
     * 部门(组)状态更改操作
     *
     * @see \App\Repositories\Contracts\DepartmentRepository::updateDepartmentStatus()
     * @param object $departmentInfoObj 数据对象 Department Model Object
     * @param int    $status            修改后的部门(组)状态值 Status In Department Model
     * @return void
     */
    public function updateDepartmentStatus($departmentInfoObj, $status)
    {
        $departmentInfoObj->status = $status;
        $departmentInfoObj->save();
    }
}
