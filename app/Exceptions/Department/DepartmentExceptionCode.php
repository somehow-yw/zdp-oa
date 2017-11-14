<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/27
 * Time: 20:17
 */

namespace App\Exceptions\Department;

/**
 * Department exception code definitions
 */
final class DepartmentExceptionCode
{
    /**
     * 部门(组)名称已经存在
     */
    const DEPARTMENT_NAME_EXIST = 101;

    /**
     * 部门(组)不存在
     */
    const DEPARTMENT_NOT = 102;

    /**
     * 部门(组)信息不可修改
     */
    const DEPARTMENT_UPDATE_NOT = 103;

    /**
     * 部门(组)下面还有操作员，不可删除
     */
    const DEPARTMENT_DELETE_NOT = 104;
}