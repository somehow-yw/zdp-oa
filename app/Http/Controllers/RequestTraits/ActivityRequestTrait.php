<?php
namespace App\Http\Controllers\RequestTraits;

use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/10/16
 * Time: 5:04 PM
 */
trait ActivityRequestTrait
{
    public function validateActivity(Request $request)
    {
        $startTime = $request->input('start_time');
        $this->validate(
            $request,
            [
                'area_id'          => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'activity_type_id' => 'required|integer|min:2',
                'start_time'       => 'required|date_format:Y-m-d H:i:s',
                'end_time'         => "required|date_format:Y-m-d H:i:s|after:$startTime",
                'shop_type_ids'    => 'required|array',
            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'activity_type_id.required' => '活动类型id不能为空',
                'activity_type_id.integer'  => '活动类型id必须为一个整数',
                'activity_type_id.min'      => '活动类型id不能小于:min',

                'start_time.required'    => '活动开始时间不能为空',
                'start_time.date_format' => '活动开始时间格式必须满足Y-m-d H:i:s',

                'end_time.required'    => '活动结束时间不能为空',
                'end_time.date_format' => '活动结束时间格式必须满足Y-m-d H:i:s',
                'end_time.after'       => '活动结束时间必须晚于开始时间',

                'shop_type_ids.required' => '参加商铺类型id数组不能为空',
                'shop_type_ids.array'    => '参加商铺类型id数组必须是个数组',
            ]
        );
    }
}