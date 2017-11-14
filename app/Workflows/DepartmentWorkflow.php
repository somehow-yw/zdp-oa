<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/28
 * Time: 10:41
 */

namespace App\Workflows;

use DB;

use App\Services\DepartmentService;
use App\Services\UserService;

use App\Repositories\Contracts\DepartmentRepository;

use App\Models\Department;
use App\Models\User;

use App\Exceptions\AppException;
use App\Exceptions\Department\DepartmentExceptionCode;

class DepartmentWorkflow
{
    private $departmentService;

    private $userService;

    private $departmentRepo;

    public function __construct(
        DepartmentService $departmentService,
        UserService $userService,
        DepartmentRepository $departmentRepo
    ) {
        $this->departmentService = $departmentService;
        $this->userService = $userService;
        $this->departmentRepo = $departmentRepo;
    }

    /**
     * 部门(组)状态更改操作
     *
     * @param int $departmentId 部门(组)ID
     * @param int $status       修改后的部门(组)状态
     * @return array
     */
    public function updateDepartmentStatus($departmentId, $status)
    {
        $self = $this;
        DB::transaction(
            function () use ($self, $departmentId, $status) {
                $self->departmentService->updateDepartmentStatus($departmentId, $status);
                if ($status == Department::CLOSE_STATUS || $status == Department::DELETE_STATUS) {
                    // 如果是关闭或删除，更改此部门(组)下面的成员状态。开启需要手动选择操作员进行状态修改
                    $userInfoObj = $self->userService->getUserInfoByDepartmentId($departmentId);
                    if ( ! $userInfoObj->isEmpty()) {
                        throw new AppException('部门(组)下面还有操作员，不可删除', DepartmentExceptionCode::DEPARTMENT_DELETE_NOT);
                        // 下面功能是正常操作需要修改的，但现在不使用
                        /*$notUpdateUserArr = User::notDeleteUsers();
                        foreach ($userInfoObj as $item) {
                            if ( ! in_array($item->user_name, $notUpdateUserArr)) {
                                $self->userService->updateUserStatus($item->id, $status);
                            }
                        }*/
                    }
                }
            }
        );

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }
}