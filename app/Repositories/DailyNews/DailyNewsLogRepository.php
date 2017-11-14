<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/30
 * Time: 14:26
 */

namespace App\Repositories\DailyNews;

use App\Repositories\DailyNews\Contracts\DailyNewsLogRepository as RepositoriesContract;

use App\Models\DpDailyNewsLog;

class DailyNewsLogRepository implements RepositoriesContract
{
    /**
     * 每日推文日志查询
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsLogRepository::getDailyNewsSendLog()
     *
     * @param int    $areaId    大区ID
     * @param string $queryDate 查询年月 格式：2016-08
     *
     * @return object
     */
    public function getDailyNewsSendLog($areaId, $queryDate)
    {
        $beginDate = $queryDate . '-01';
        $endDate = $queryDate . '-31';
        $sendLogObjs = DpDailyNewsLog::where('area_id', $areaId)
            ->where(
                function ($query) use ($beginDate, $endDate) {
                    $query->where('send_date', '>=', $beginDate)
                        ->where('send_date', '<=', $endDate);
                }
            )
            ->get();
        return $sendLogObjs;
    }
}