<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/29/16
 * Time: 12:58 PM
 */

namespace App\Repositories\Goods\Contracts;

use App\Models\DpActivity;

interface ActivityRepository
{
    /**
     * 通过活动类型id获取活动列表
     *
     * @param $activity_type_id integer 活动类型id
     * @param $area_id          integer 片区id
     *
     * @return array
     */
    public function getActivitiesByActivityTypeId($activity_type_id, $area_id);

    /**
     * 通过活动id获取活动
     *
     * @param $activity_id integer 活动id
     *
     * @return array
     */
    public function getActivityByActivityId($activity_id);


    /**
     * 添加活动
     *
     * @param $starter_id       integer 活动开启者id
     * @param $activity_type_id integer 活动类型id
     * @param $area_id          integer 片区id
     * @param $start_time       string 开始时间
     * @param $end_time         string 结束时间
     * @param $shop_type_ids    array 可参加该活动商户类型id数组
     *
     * @return DpActivity
     */
    public function addActivity($starter_id, $activity_type_id, $area_id, $start_time, $end_time, $shop_type_ids);

    /**
     * 更新活动
     *
     * @param $starter_id    integer 活动开启者id
     * @param $activity_id   integer 活动id
     * @param $start_time    string 开始时间
     * @param $end_time      string 结束时间
     * @param $shop_type_ids array 可参加该活动商户类型id数组
     *
     * @return DpActivity
     */
    public function updateActivity($starter_id, $activity_id, $start_time, $end_time, $shop_type_ids);

    /**
     * @param       $activityId integer 活动id
     * @param array $columns    array　 需要获取的列
     *
     * @return mixed
     */
    public function getActivityColumnsByActivityId($activityId, array $columns = ['*']);
}
