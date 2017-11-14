<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/29/16
 * Time: 1:30 PM
 */

namespace App\Repositories\Goods;

use Carbon\Carbon;
use DB;
use App\Models\DpActivity;
use App\Repositories\Goods\Contracts\ActivityRepository as RepositoryContracts;

class ActivityRepository implements RepositoryContracts
{
    /**
     * @see \App\Repositories\Goods\Contracts\ActivityRepository::getActivitiesByActivityTypeId()
     */
    public function getActivitiesByActivityTypeId($activity_type_id, $area_id)
    {
        $activities = DpActivity::select(DB::raw('id as activity_id'), 'start_time', 'end_time', 'shop_type_ids')
            ->where('area_id', '=', $area_id)
            ->where('activity_type_id', '=', $activity_type_id)
            ->where('end_time', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->orderBy('start_time')
            ->get()
            ->toArray();

        return $activities;
    }

    /**
     * @see \App\Repositories\Goods\Contracts\ActivityRepository::getActivityByActivityId()
     */
    public function getActivityByActivityId($activity_id)
    {
        $activity = DpActivity::select('start_time', 'end_time', 'shop_type_ids')
            ->where('id', $activity_id)
            ->first();
        if (is_null($activity)) {
            return [];
        }
        $activity = $activity->toArray();

        return $activity;
    }

    /**
     * @see \App\Repositories\Goods\Contracts\ActivityRepository::addActivity()
     */
    public function addActivity($starter_id, $activity_type_id, $area_id, $start_time, $end_time, $shop_type_ids)
    {
        //将shop_type_ids 转换成,分隔的字符串
        $shop_type_ids = implode(',', $shop_type_ids);
        $attributes = compact('starter_id', 'activity_type_id', 'area_id', 'start_time', 'end_time', 'shop_type_ids');
        $activity = new DpActivity($attributes);
        $activity->save();

        return $activity;
    }

    /**
     * @see \App\Repositories\Goods\Contracts\ActivityRepository::updateActivity()
     */
    public function updateActivity($starter_id, $activity_id, $start_time, $end_time, $shop_type_ids)
    {
        //将shop_type_ids 转换成,分隔的字符串
        $shop_type_ids = implode(',', $shop_type_ids);
        /** @var DpActivity $activity */
        $activity = DpActivity::where('id', '=', $activity_id)->first();
        $attributes = compact('starter_id', 'start_time', 'end_time', 'shop_type_ids');
        $activity->update($attributes);
        $activity->save();

        return $activity;
    }

    /**
     * @see \App\Repositories\Goods\Contracts\ActivityRepository::getActivityColumnsByActivityId()
     */
    public function getActivityColumnsByActivityId($activityId, array $columns = ['*'])
    {
        $activity = DpActivity::select($columns)
            ->where('id', $activityId)
            ->first();
        if (is_null($activity)) {
            return [];
        }
        $activity = $activity->toArray();

        return $activity;
    }


}
