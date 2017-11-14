<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/28
 * Time: 15:31
 */

namespace App\Repositories;

use App\Repositories\Contracts\UserLogRepository as RepositoriesContract;

use App\Models\UserLog;

class UserLogRepository implements RepositoriesContract
{
    /**
     * 记录会员操作日志
     *
     * @see \App\Repositories\Contracts\UserLogRepository::genUserLog()
     * @param int    $userId    操作员ID
     * @param string $userName  操作员名称
     * @param string $loginName 操作员登录名
     * @param string $routeUses 操作路由
     * @param string $userIp    操作者IP
     * @param string $logDate   操作日期
     */
    public function genUserLog($userId, $userName, $loginName, $routeUses, $userIp, $logDate)
    {
        $logInfo = UserLog::where('user_id', $userId)
            ->where('route_uses', $routeUses)
            ->where('statistical_date', $logDate)
            ->where('user_ip', $userIp)
            ->first();
        if ($logInfo) {
            $logInfo->statistical_time = $logInfo->statistical_time + 1;
            $logInfo->save();
        } else {
            $addArr = [
                'user_id'          => $userId,
                'user_name'        => $userName,
                'login_name'       => $loginName,
                'route_uses'       => $routeUses,
                'statistical_date' => $logDate,
                'user_ip'          => $userIp,
            ];
            UserLog::create($addArr);
        }
    }
}
