<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/27
 * Time: 19:45
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\DepartmentService;

use App\Workflows\DepartmentWorkflow;

class DepartmentController extends Controller
{
    /**
     * 部门(组)添加
     *
     * @param Request           $request
     * @param DepartmentService $departmentService
     * @return \Illuminate\Http\Response
     */
    public function addDepartment(
        Request $request,
        DepartmentService $departmentService
    ) {
        $this->validate(
            $request,
            [
                'department_name' => 'required|string|between:1,50',
            ],
            [
                'department_name.required' => '部门(组)名称必须有',
                'department_name.string'   => '部门(组)名称必须是一个字符串',
                'department_name.between'  => '部门(组)名称长度必须在:min, 到:max之间',
            ]
        );

        $reData = $departmentService->addDepartment($request->input('department_name'));

        return $this->render(
            'department.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 部门(组)列表
     *
     * @param Request           $request
     * @param DepartmentService $departmentService
     * @return \Illuminate\Http\Response
     */
    public function getDepartmentList(
        Request $request,
        DepartmentService $departmentService
    ) {
        $this->validate(
            $request,
            [
                'page' => 'required|integer|between:1,99999',
                'size' => 'required|integer|between:1,100',
            ],
            [
                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',
            ]
        );
        $reData = $departmentService->getDepartmentList($request->input('page'), $request->input('size'));

        return $this->render(
            'department.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 部门(组)信息获取
     *
     * @param Request           $request
     * @param DepartmentService $departmentService
     * @return \Illuminate\Http\Response
     */
    public function getDepartmentInfo(
        Request $request,
        DepartmentService $departmentService
    ) {
        $this->validate(
            $request,
            [
                'department_id' => 'required|integer|between:1,99999',
            ],
            [
                'department_id.required' => '部门(组)ID必须有',
                'department_id.integer'  => '部门(组)ID必须是一个整型',
                'department_id.between'  => '部门(组)ID必须是:min, 到:max的整数',
            ]
        );
        $reData = $departmentService->getDepartmentInfo($request->input('department_id'));

        return $this->render(
            'department.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 部门(组)信息修改
     *
     * @param Request           $request
     * @param DepartmentService $departmentService
     * @return \Illuminate\Http\Response
     */
    public function updateDepartmentInfo(
        Request $request,
        DepartmentService $departmentService
    ) {
        $this->validate(
            $request,
            [
                'department_id'   => 'required|integer|between:1,99999',
                'department_name' => 'required|string|between:1,50',
            ],
            [
                'department_id.required' => '部门(组)ID必须有',
                'department_id.integer'  => '部门(组)ID必须是一个整型',
                'department_id.between'  => '部门(组)ID必须是:min, 到:max的整数',

                'department_name.required' => '部门(组)名称必须有',
                'department_name.string'   => '部门(组)名称必须是一个字符串',
                'department_name.between'  => '部门(组)名称长度必须在:min, 到:max之间',
            ]
        );
        $reData = $departmentService->updateDepartmentInfo(
            $request->input('department_id'),
            $request->input('department_name')
        );

        return $this->render(
            'department.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 部门(组)状态更改操作
     *
     * @param Request            $request
     * @param DepartmentWorkflow $departmentWorkflow
     * @return \Illuminate\Http\Response
     */
    public function updateDepartmentStatus(
        Request $request,
        DepartmentWorkflow $departmentWorkflow
    ) {
        $this->validate(
            $request,
            [
                'department_id' => 'required|integer|between:1,99999',
                'status'        => 'required|string|between:1,3',
            ],
            [
                'department_id.required' => '部门(组)ID必须有',
                'department_id.integer'  => '部门(组)ID必须是一个整型',
                'department_id.between'  => '部门(组)ID必须是:min, 到:max的整数',

                'status.required' => '修改状态必须有',
                'status.string'   => '状态必须是一个字符串',
                'status.between'  => '状态必须在:min, 到:max之间',
            ]
        );
        $reData = $departmentWorkflow->updateDepartmentStatus(
            $request->input('department_id'),
            $request->input('status')
        );

        return $this->render(
            'department.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}