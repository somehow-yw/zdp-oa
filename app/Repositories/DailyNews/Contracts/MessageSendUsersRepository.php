<?php

/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/8/27
 * Time: 18:25
 */

namespace App\Repositories\DailyNews\Contracts;

interface MessageSendUsersRepository
{
    /**
     * 获取可接收客服消息推送的用户
     *
     * @param int $areaId 大区ID
     * @param int $size   数据获取量
     *
     * @return object
     */
    public function getDailyNewsReceiveUsers($areaId, $size);
}