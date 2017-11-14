<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/1
 * Time: 10:40
 */

namespace App\Repositories\DailyNews;

use App\Repositories\DailyNews\Contracts\NewsManageRepository as RepositoriesContract;

use App\Models\DpNewsManageConfiguration;

use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;

class NewsManageRepository implements RepositoriesContract
{
    /**
     * 获取每日推文管理信息
     *
     * @see \App\Repositories\DailyNews\Contracts\NewsManageRepository::getDailyNewsManageInfo()
     *
     * @param int $areaId 大区ID
     *
     * @return object
     */
    public function getDailyNewsManageInfo($areaId)
    {
        return DpNewsManageConfiguration::where('area_id', $areaId)
            ->first();
    }

    /**
     * 修改每日推文管理信息
     *
     * @see \App\Repositories\DailyNews\Contracts\NewsManageRepository::updateDailyNewsManageInfo()
     *
     * @param int    $id           操作ID
     * @param int    $areaId       大区ID
     * @param int    $editUserId   编辑员ID
     * @param int    $reviewUserId 审核员ID
     * @param string $sendTime     发送时间 格式：H:i:s
     *
     * @return void
     * @throws AppException
     */
    public function updateDailyNewsManageInfo($id, $areaId, $editUserId, $reviewUserId, $sendTime)
    {
        $manageInfoObj = $this->getDailyNewsManageInfoById($id);
        if ( ! $manageInfoObj) {
            throw new AppException('此推文管理信息不存在', DailyNewsExceptionCode::MANAGE_NOT_RECORD);
        }
        $manageInfoObj->edit_user_id = $editUserId;
        $manageInfoObj->review_user_id = $reviewUserId;
        $manageInfoObj->send_time = $sendTime;
        $manageInfoObj->save();
    }

    /**
     * 添加每日推文管理信息
     *
     * @see \App\Repositories\DailyNews\Contracts\NewsManageRepository::addDailyNewsManageInfo()
     *
     * @param int    $areaId       大区ID
     * @param int    $editUserId   编辑员ID
     * @param int    $reviewUserId 审核员ID
     * @param string $sendTime     发送时间 格式：H:i:s
     *
     * @return void
     */
    public function addDailyNewsManageInfo($areaId, $editUserId, $reviewUserId, $sendTime)
    {
        $addArr = [
            'area_id'        => $areaId,
            'edit_user_id'   => $editUserId,
            'review_user_id' => $reviewUserId,
            'send_time'      => $sendTime,
        ];
        DpNewsManageConfiguration::create($addArr);
    }

    /**
     * 获取所有每日推文管理信息
     *
     * @see \App\Repositories\DailyNews\Contracts\NewsManageRepository::getAllDailyNewsManageInfo()
     *
     * @return object
     */
    public function getAllDailyNewsManageInfo()
    {
        return DpNewsManageConfiguration::get();
    }

    /**
     * 获取每日推文管理信息,根据ID
     *
     * @param int $id ID
     *
     * @return object
     */
    private function getDailyNewsManageInfoById($id)
    {
        return DpNewsManageConfiguration::where('id', $id)
            ->first();
    }
}