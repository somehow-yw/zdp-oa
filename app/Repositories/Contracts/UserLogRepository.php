<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/28
 * Time: 15:31
 */

namespace App\Repositories\Contracts;

interface UserLogRepository
{
    /**
     * 记录会员操作日志
     *
     * @param int    $userId    操作员ID
     * @param string $userName  操作员名称
     * @param string $loginName 操作员登录名
     * @param string $routeUses 操作路由
     * @param string $userIp    操作者IP
     * @param string $logDate   操作日期
     */
    public function genUserLog($userId, $userName, $loginName, $routeUses, $userIp, $logDate);
}