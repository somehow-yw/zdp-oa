<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/30
 * Time: 14:25
 */

namespace App\Repositories\DailyNews\Contracts;


interface DailyNewsLogRepository
{
    /**
     * 每日推文日志查询
     *
     * @param int    $areaId    大区ID
     * @param string $queryDate 查询年月 格式：2016-08
     *
     * @return object
     */
    public function getDailyNewsSendLog($areaId, $queryDate);
}