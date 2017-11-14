<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/27
 * Time: 19:50
 */

namespace App\Repositories\Contracts;

interface DepartmentRepository
{
    /**
     * 获取部门信息 根据部门名称
     *
     * @param string $departmentName 部门名称
     * @return object
     */
    public function getDepartmentInfoByName($departmentName);

    /**
     * 添加部门(组)
     *
     * @param string $departmentName 部门名称
     * @param string $remark         备注信息
     * @return void
     */
    public function addDepartment($departmentName, $remark);

    /**
     * 部门(组)列表
     *
     * @param int   $size      返回数据量
     * @param array $statusArr 需满足的部门(组)状态 格式：[1,2]
     * @return object
     */
    public function getDepartmentList($size, $statusArr);

    /**
     * 部门(组)信息获取 根据ID
     *
     * @param int $departmentId 部门(组)ID
     * @return object
     */
    public function getDepartmentInfoById($departmentId);

    /**
     * 部门(组)信息修改
     *
     * @param object $departmentInfoObj 数据对象 Department Model Object
     * @param string $departmentName    修改后的部门(组)名称
     * @return void
     */
    public function updateDepartmentInfo($departmentInfoObj, $departmentName);

    /**
     * 部门(组)状态更改操作
     *
     * @param object $departmentInfoObj 数据对象 Department Model Object
     * @param int    $status            修改后的部门(组)状态值 Status In Department Model
     * @return void
     */
    public function updateDepartmentStatus($departmentInfoObj, $status);
}