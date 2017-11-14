<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/29/16
 * Time: 12:57 PM
 */

namespace App\Services\Goods;

use App\Models\DpGoodsBasicAttribute;
use DB;
use App\Exceptions\AppException;
use App\Exceptions\Goods\ActivityException;
use App\Models\DpActivity;
use App\Repositories\Goods\Contracts\ActivityRepository;

class ActivityService
{
    protected $activityRepository;

    public function __construct(ActivityRepository $repository)
    {
        $this->activityRepository = $repository;
    }

    /**
     * 通过活动类型id读取活动列表
     *
     * @param $activityTypeId  integer 活动类型id
     * @param $areaId          integer 片区id
     *
     * @return array
     */
    public function getActivitiesList($activityTypeId, $areaId)
    {
        $activities = $this->activityRepository->getActivitiesByActivityTypeId($activityTypeId, $areaId);
        //循环activities数组,增加activity_type_name、shop_type_ids字段
        foreach ($activities as &$activity) {
            $activity['activity_type_name'] = DpGoodsBasicAttribute::getActivityTypeNameById($activityTypeId);
            $activity['shop_type_ids'] = array_map('intval', explode(',', $activity['shop_type_ids']));
        }
        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => ['activities' => $activities],
        ];

        return $reData;
    }

    /**
     * 添加活动
     *
     * @param $starterId       integer 活动开启者id
     * @param $activityTypeId  integer 活动类型id
     * @param $areaId          integer 片区id
     * @param $startTime       string 开始时间
     * @param $endTime         string 结束时间
     * @param $shopTypeIds     array 可参加该活动商户类型id数组
     *
     * @throws AppException
     * @return array
     */
    public function addActivity($starterId, $activityTypeId, $areaId, $startTime, $endTime, $shopTypeIds)
    {
        //先判断当前活动开始时间是否与最近一次活动时间重叠
        $this->validateActivityTime($areaId, $activityTypeId, $startTime, $endTime);
        $this->activityRepository->addActivity(
            $starterId,
            $activityTypeId,
            $areaId,
            $startTime,
            $endTime,
            $shopTypeIds
        );

        $reData = [

            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $reData;
    }

    /**
     * 更新活动
     *
     * @param $starterId   integer 活动开启者id
     * @param $activityId  integer 活动id
     * @param $startTime   string 活动开始时间
     * @param $endTime     string 活动结束时间
     * @param $shopTypeIds array 可参加活动的店铺类型ids
     *
     * @return array
     */
    public function updateActivity($starterId, $activityId, $startTime, $endTime, $shopTypeIds)
    {
        //当前更新的活动
        $activity = DpActivity::select('area_id,activity_type_id')->where('id', $activityId)->first();
        $areaId = $activity->area_id;
        $activityTypeId = $activity->activity_type_id;

        //先判断当前活动开始时间是否与最近一次活动时间重叠
        $this->validateActivityTime($areaId, $activityTypeId, $startTime, $endTime);

        $this->activityRepository->updateActivity($starterId, $activityId, $startTime, $endTime, $shopTypeIds);

        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $reData;
    }

    /**
     * @see \App\Repositories\Goods\Contracts\ActivityRepository::getActivityColumnsByActivityId()
     */
    public function getActivityByActivityId($activityId, $columns = ['*'])
    {
        return $this->activityRepository->getActivityColumnsByActivityId($activityId, $columns);
    }

    /**
     * 验证当前片区活动时间的有效性(当前片区下的活动不能互相重叠)
     *
     * @param $areaId         integer 片区id
     * @param $activityTypeId integer 活动类型id
     * @param $startTime      string 活动开始时间
     * @param $endTime        string 活动结束时间
     *
     * @return boolean
     *
     * @throws AppException
     */
    protected function validateActivityTime($areaId, $activityTypeId, $startTime, $endTime)
    {
        //时间可重叠的活动类型id
        $timeFreeActivityTypeIds = [
            //团购
            2,
        ];
        if (in_array($activityTypeId, $timeFreeActivityTypeIds)) {
            return true;
        };
        /** @lang MySQL */
        $sql = <<<SQL
            SELECT COUNT(*) as `count` FROM `dp_activities`
            WHERE `area_id`=? AND `activity_type_id`=? AND (
            (`start_time`>=? AND `end_time`<?) OR
            (`start_time`>=? AND `start_time`<?) OR
            (`end_time`>? AND `end_time`<?)
        )
SQL;
        $result = DB::connection('mysql_zdp_main')->select(
            $sql,
            [
                $areaId, $activityTypeId, $startTime, $endTime, $startTime, $endTime, $startTime, $endTime,
            ]
        );
        $count = $result[0]->count;
        if ($count > 0) {
            throw  new AppException(
                ActivityException::ACTIVITY_TIME_OVERLAP_MSG,
                ActivityException::ACTIVITY_TIME_OVERLAP_CODE
            );
        }

        return true;
    }

    /**
     * 获取活动类型数组
     *
     * @return array
     */
    public function getActivitiesTypes()
    {
        return DpGoodsBasicAttribute::$mapArray;
    }
}
