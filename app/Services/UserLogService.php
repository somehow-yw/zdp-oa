<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/28
 * Time: 17:09
 */

namespace App\Services;

use Carbon\Carbon;

use App\Repositories\Contracts\UserLogRepository;

class UserLogService
{
    private $userLogRepo;

    public function __construct(
        UserLogRepository $userLogRepo
    ) {
        $this->userLogRepo = $userLogRepo;
    }

    /**
     * 记录会员操作日志
     *
     * @param int    $userId    操作员ID
     * @param string $userName  操作员名称
     * @param string $loginName 操作员登录名
     * @param string $routeUses 操作路由
     * @param string $userIp    操作者IP
     */
    public function genUserLog($userId, $userName, $loginName, $routeUses, $userIp)
    {
        $logDate = Carbon::now()->format('Y-m-d');
        $this->userLogRepo->genUserLog($userId, $userName, $loginName, $routeUses, $userIp, $logDate);
    }
}