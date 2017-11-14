<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/1
 * Time: 10:40
 */

namespace App\Repositories\DailyNews\Contracts;

interface NewsManageRepository
{
    /**
     * 获取每日推文管理信息
     *
     * @param int $areaId 大区ID
     *
     * @return object
     */
    public function getDailyNewsManageInfo($areaId);

    /**
     * 修改每日推文管理信息
     *
     * @param int    $id           操作ID
     * @param int    $areaId       大区ID
     * @param int    $editUserId   编辑员ID
     * @param int    $reviewUserId 审核员ID
     * @param string $sendTime     发送时间 格式：H:i:s
     *
     * @return void
     */
    public function updateDailyNewsManageInfo($id, $areaId, $editUserId, $reviewUserId, $sendTime);

    /**
     * 添加每日推文管理信息
     *
     * @param int    $areaId       大区ID
     * @param int    $editUserId   编辑员ID
     * @param int    $reviewUserId 审核员ID
     * @param string $sendTime     发送时间 格式：H:i:s
     *
     * @return void
     */
    public function addDailyNewsManageInfo($areaId, $editUserId, $reviewUserId, $sendTime);

    /**
     * 获取所有每日推文管理信息
     *
     * @return object
     */
    public function getAllDailyNewsManageInfo();
}