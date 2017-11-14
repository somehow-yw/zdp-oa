<?php

namespace App\Http\Controllers;

use Zdp\Main\Data\Models\DpPianquDivide;
use Zdp\Main\Data\Services\AreaService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * 获取省市信息
     *
     * @param Request     $request
     * @param AreaService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCity(Request $request, AreaService $service)
    {
        $this->validate(
            $request,
            [
                'pid'    => 'integer|min:1|max:34',
                'is_add' => 'string', // 是否是开通省市接口获取省信息
            ],
            [
                'pid.min' => '数据错误',
                'pid.max' => '数据错误',
            ]
        );

        // 判断是否是开通省市接口
        $isOpenProvince = false;
        if ($request->input('is_add')) {
            $isOpenProvince = true;
        }

        $data = $service->getCity($request->input('pid'), $isOpenProvince);

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 获取区县信息
     *
     * @param Request     $request
     * @param AreaService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCounty(Request $request, AreaService $service)
    {
        $this->validate(
            $request,
            [
                'pid' => 'required|integer|min:3|max:34|not_in:9,31,32,33,34',
                'cid' => 'required|integer',
            ],
            [
                'pid.min'    => '直辖市没有区县',
                'pid.not_in' => '该区域没有区县',
                'pid.max'    => '选择省市信息错误',
            ]
        );

        $data = $service->getCounty(
            $request->input('pid'),
            $request->input('cid')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 获取已开通省的信息
     */
    public function getOpenProvince()
    {
        $data = DpPianquDivide::where('provinceidtxt', '>', 0)
                              ->select([
                                  'id as divide_id',
                                  'dividename as dividename',
                                  'provinceidtxt as province_id',
                              ])
                              ->get();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => ['detail' => $data],
        ]);
    }
}