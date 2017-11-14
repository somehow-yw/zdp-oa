<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RequestTraits\ActivityRequestTrait;
use App\Services\Goods\ActivityService;
use Illuminate\Http\Request;


class ActivityController extends Controller
{
    use ActivityRequestTrait;

    /**
     * 获取活动列表
     *
     * @param Request         $request
     * @param ActivityService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getActivitiesList(Request $request, ActivityService $service)
    {
        $this->validate(
            $request,
            [
                'area_id'          => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'activity_type_id' => 'required|integer|min:2',
            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'activity_type_id.required' => '活动类型id不能为空',
                'activity_type_id.integer'  => '活动类型id必须为一个整数',
                'activity_type_id.min'      => '活动类型id不能小于:min',
            ]
        );

        $reData = $service->getActivitiesList($request->input('activity_type_id'), $request->input('area_id'));

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 添加活动
     *
     * @param Request         $request 请求
     * @param ActivityService $service 活动服务
     *
     * @return \Illuminate\Http\Response 回复
     */
    public function addActivity(Request $request, ActivityService $service)
    {
        $this->validateActivity($request);

        $service->addActivity(
            $request->user()->id,
            $request->input('activity_type_id'),
            $request->input('area_id'),
            $request->input('start_time'),
            $request->input('end_time'),
            $request->input('shop_type_ids')
        );
        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 更新活动
     *
     * @param Request         $request 请求
     * @param ActivityService $service 活动服务
     *
     * @return \Illuminate\Http\Response
     */
    public function updateActivity(Request $request, ActivityService $service)
    {
        $startTime = $request->input('data.start_time');
        $this->validate(
            $request,
            [
                'activity_id'   => 'required|integer|exists:mysql_zdp_main.dp_activities,id',
                'start_time'    => 'required|date_format:Y-m-d H:i:s',
                'end_time'      => "required|date_format:Y-m-d H:i:s|after:$startTime",
                'shop_type_ids' => 'required|array',
            ],
            [
                'activity_id.required' => '活动id不能为空',
                'activity_id.integer'  => '活动id必须是整形',
                'activity_id.exists'   => '活动id不存在',

                'start_time.required'    => '活动开始时间不能为空',
                'start_time.date_format' => '活动开始时间格式必须满足Y-m-d H:i:s',

                'end_time.required'    => '活动结束时间不能为空',
                'end_time.date_format' => '活动结束时间格式必须满足Y-m-d H:i:s',
                'end_time.after'       => '活动结束时间必须晚于开始时间',

                'shop_type_ids.required' => '参加商铺类型id数组不能为空',
                'shop_type_ids.array'    => '参加商铺类型id数组必须是个数组',
            ]
        );

        $service->updateActivity(
            $request->user()->id,
            $request->input('activity_id'),
            $request->input('start_time'),
            $request->input('end_time'),
            $request->input('shop_type_ids')
        );

        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 获取活动类型
     *
     * @param ActivityService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getActivitiesTypes(ActivityService $service)
    {
        $types = $service->getActivitiesTypes();
        $data = [];
        foreach ($types as $key => $type) {
            $data[] = ["id" => $key, "name" => $type];
        }
        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ];

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}
