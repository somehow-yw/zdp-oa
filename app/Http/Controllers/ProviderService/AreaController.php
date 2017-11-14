<?php

namespace App\Http\Controllers\ProviderService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Models\Area;
use Zdp\ServiceProvider\Data\Models\SpArea;
use Zdp\ServiceProvider\Data\Services\SpAreaService;

class AreaController extends Controller
{

    /**
     * 添加服务商区域信息
     *
     * @param Request       $request
     * @param SpAreaService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, SpAreaService $service)
    {
        $this->validate(
            $request,
            [
                'sp_id'       => 'required|exists:mysql_service_provider.service_providers,zdp_user_id',
                'province_id' => 'required|exists:mysql_service_provider.area,id,level,' .
                                 Area::LEVEL_PROVINCE,
                'city_id'     => 'exists:mysql_service_provider.area,id,level,' .
                                 Area::LEVEL_CITY,
                'county_id'   => 'exists:mysql_service_provider.area,id,level,' .
                                 Area::LEVEL_DISTRICT,
            ],
            [
                'sp_id.required'       => '服务商ID不能为空',
                'sp_id.exists'         => '服务商不存在',
                'province_id.required' => '省ID不能为空',
                'province_id.exists'   => '省不存在',
                'city_id.exists'       => '市不存在',
                'county_id.exists'     => '区县不存在',
            ]
        );

        $service->add(
            $request->input('sp_id'),
            $request->input('province_id'),
            $request->input('city_id'),
            $request->input('county_id')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 修改服务商区域信息
     *
     * @param Request       $request
     * @param SpAreaService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, SpAreaService $service)
    {
        $this->validate(
            $request,
            [
                'area_id'     => 'required',
                'province_id' => 'required|exists:mysql_service_provider.area,id,level,' .
                                 Area::LEVEL_PROVINCE,
                'city_id'     => 'exists:mysql_service_provider.area,id,level,' .
                                 Area::LEVEL_CITY,
                'county_id'   => 'exists:mysql_service_provider.area,id,level,' .
                                 Area::LEVEL_DISTRICT,
            ],
            [
                'area_id.required'     => '区域信息ID不能为空',
                'province_id.required' => '省ID不能为空',
                'province_id.exists'   => '省不存在',
                'city_id.exists'       => '市不存在',
                'county_id.exists'     => '区县不存在',
            ]
        );

        $service->update(
            $request->input('area_id'),
            $request->input('province_id'),
            $request->input('city_id'),
            $request->input('county_id')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 删除服务商区域信息
     *
     * @param Request       $request
     * @param SpAreaService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, SpAreaService $service)
    {
        $this->validate(
            $request,
            [
                'area_id' => 'required',
            ],
            [
                'area_id.required' => '区域信息ID不能为空',
            ]
        );

        $service->delete($request->input('area_id'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 获取服务商区域信息
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        $this->validate(
            $request,
            [
                'sp_id' => 'required|exists:mysql_service_provider.service_providers,zdp_user_id',
            ],
            [
                'sp_id.required' => '服务商ID不能为空',
                'sp_id.exists'   => '服务商不存在',
            ]
        );

        $areas = SpArea::query()
                       ->where('sp_id', $request->input('sp_id'))
                       ->get();

        $strs = [];

        foreach ($areas as $area) {
            $strs[$area->id] = $area->asString();
        }

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $strs,
        ]);
    }

}
