<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/27
 * Time: 19:49
 */

namespace App\Services;

use Illuminate\Contracts\Auth\Guard;

use App\Repositories\Contracts\DepartmentRepository;

use App\Models\Department;

use App\Exceptions\AppException;
use App\Exceptions\Department\DepartmentExceptionCode;

class DepartmentService
{
    private $departmentRepo;

    private $auth;

    public function __construct(
        DepartmentRepository $departmentRepo,
        Guard $auth
    ) {
        $this->departmentRepo = $departmentRepo;
        $this->auth = $auth;
    }

    /**
     * 部门(组)添加
     *
     * @param string $departmentName 部门(组)名称
     * @return array
     * @throws AppException
     */
    public function addDepartment($departmentName)
    {
        // 查询部门是否已存在
        $departmentInfoObj = $this->departmentRepo->getDepartmentInfoByName($departmentName);
        if ($departmentInfoObj) {
            throw new AppException('部门(组)名称已存在', DepartmentExceptionCode::DEPARTMENT_NAME_EXIST);
        }
        $loginUserInfoArr = $this->auth->user()->toArray();
        $remark = "{$loginUserInfoArr['user_name']}({$loginUserInfoArr['login_name']})添加部门(组)";
        $this->departmentRepo->addDepartment($departmentName, $remark);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 部门(组)列表
     *
     * @param int $page 当前页数
     * @param int $size 需返回的数据量
     * @return array
     */
    public function getDepartmentList($page, $size)
    {
        $statusArr = [
            Department::NORMAL_STATUS,
            Department::CLOSE_STATUS,
        ];
        $departmentInfoObj = $this->departmentRepo->getDepartmentList($size, $statusArr);
        $reDataArr = [
            'page'        => (int)$page,
            'total'       => $departmentInfoObj->total(),
            'departments' => [],
        ];
        if ( ! $departmentInfoObj->isEmpty()) {
            $for_n = 0;
            foreach ($departmentInfoObj as $item) {
                $reDataArr['departments'][$for_n]['id'] = $item->id;
                $reDataArr['departments'][$for_n]['name'] = $item->department_name;
                $reDataArr['departments'][$for_n]['status'] = $item->status;
                $reDataArr['departments'][$for_n]['create_time'] = $item->created_at->format('Y-m-d H:i:s');
                $for_n++;
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }

    /**
     * 部门(组)信息获取
     *
     * @param int $departmentId 部门(组)ID
     * @return array
     * @throws AppException
     */
    public function getDepartmentInfo($departmentId)
    {
        $departmentInfoObj = $this->departmentRepo->getDepartmentInfoById($departmentId);
        if ( ! $departmentInfoObj) {
            throw new AppException('部门(组)不存在', DepartmentExceptionCode::DEPARTMENT_NOT);
        }
        $reDataArr = [];
        $reDataArr['department_infos']['id'] = $departmentInfoObj->id;
        $reDataArr['department_infos']['name'] = $departmentInfoObj->department_name;

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }

    /**
     * 部门(组)信息修改
     *
     * @param int    $departmentId   部门(组)ID
     * @param string $departmentName 修改后的部门(组)名称
     * @return array
     * @throws AppException
     */
    public function updateDepartmentInfo($departmentId, $departmentName)
    {
        $departmentInfoObj = $this->departmentRepo->getDepartmentInfoById($departmentId);
        if ( ! $departmentInfoObj) {
            throw new AppException('部门(组)不存在', DepartmentExceptionCode::DEPARTMENT_NOT);
        } elseif ($departmentInfoObj->department_name == '系统管理组') {
            throw new AppException('此部门(组)信息不可修改', DepartmentExceptionCode::DEPARTMENT_UPDATE_NOT);
        }
        $this->departmentRepo->updateDepartmentInfo($departmentInfoObj, $departmentName);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 部门(组)状态更改操作
     *
     * @param int $departmentId 部门(组)ID
     * @param int $status       修改后的部门(组)状态
     * @return array
     * @throws AppException
     */
    public function updateDepartmentStatus($departmentId, $status)
    {
        $departmentInfoObj = $this->departmentRepo->getDepartmentInfoById($departmentId);
        if ( ! $departmentInfoObj) {
            throw new AppException('部门(组)不存在', DepartmentExceptionCode::DEPARTMENT_NOT);
        } elseif ($departmentInfoObj->department_name == '系统管理组'
            || $departmentInfoObj->status == Department::DELETE_STATUS
        ) {
            throw new AppException('此部门(组)信息不可修改', DepartmentExceptionCode::DEPARTMENT_UPDATE_NOT);
        }
        $this->departmentRepo->updateDepartmentStatus($departmentInfoObj, $status);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }
}