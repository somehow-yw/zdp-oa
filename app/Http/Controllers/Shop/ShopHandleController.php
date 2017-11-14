<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Shops\ShopHandleService;
use Illuminate\Http\Request;
use Zdp\Main\Data\Services\AreaService;

class ShopHandleController extends Controller
{
    protected $service;

    public function __construct(ShopHandleService $service)
    {
        $this->service = $service;
    }

    /**
     * 店铺列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->validate(
            $request,
            [
                'province_id' => 'integer',
                'search'      => 'string',
                'search_type' => 'string|in:mobile,user_name,shop_name',
                'shengheAct'  => 'integer|in:0,1,2,3,10',
                'page'        => 'integer|min:1|max:999',
                'size'        => 'integer|min:1|max:50',
            ]
        );
        $data = $this->service->index(
            $request->input('province_id'),
            $request->input('search'),
            $request->input('search_type'),
            $request->input('shengheAct', 0),
            $request->input('page', 1),
            $request->input('size', 20)
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 店铺详情
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $this->validate(
            $request,
            [
                'shopId' => 'required|exists:mysql_zdp_main.dp_shopInfo,shopId',
            ]
        );

        $data = $this->service->show($request->input('shopId'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [$data],
        ]);
    }

    /**
     * 店铺信息更新
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate(
            $request,
            [
                'shopId'          => 'required|integer|exists:mysql_zdp_main.dp_shopInfo,shopId', // 店铺Id
                'shId'            => 'required|integer|exists:mysql_zdp_main.dp_shangHuInfo,shId', // 用户Id
                'dianPuName'      => 'required|string', // 店铺名字
                'pianquId'        => 'required', // 所在市场
                'trenchnum'       => 'required|integer', // 商铺类型
                'xingming'        => 'required|string', // 姓名
                'xiangXiDiZi'     => 'required|string', // 详细地址
                'province_id'     => 'required|integer', // 省id
                'province'        => 'required|string', // 省份
                'city_id'         => 'required|integer', // 市id
                'city'            => 'required|string', // 市
                'county_id'       => 'integer', // 区县id
                'county'          => 'string', // 区县
                'cardPic'         => 'string', // 照片路径
                'weChatGroupId'   => 'required', // 微信分组的ID
                'main_products'   => 'string|between:1,255', // 主营业务
                'head_portrait'   => 'string|between:3,255',
            ],
            [
                'weChatGroupId.required' => '还未进行微信分组设置'
            ]
        );

        $this->service->update($request->all());

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 店铺关闭
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function close(Request $request)
    {
        $this->validate(
            $request,
            [
                'shopId' => 'required|exists:mysql_zdp_main.dp_shopInfo,shopId',
            ]
        );

        $this->service->close($request->input('shopId'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 修改店铺信息的前置信息获取(省市县、一批市场)
     *
     * @param Request     $request
     * @param AreaService $areaService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreaOrPianqu(Request $request, AreaService $areaService)
    {
        $this->validate(
            $request,
            [
                'type' => 'required|integer',
            ]
        );

        $data = $areaService->getAreaOrPianqu($request->input('type', 21));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 修改店铺信息的前置信息获取(一批根据大区id获取省市县)
     *
     * @param Request     $request
     * @param AreaService $areaService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreaOfPianqu(Request $request, AreaService $areaService)
    {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|exists:mysql_zdp_main.dp_pianqu,pianquId',
            ]
        );

        $data = $areaService->getAreaOfPianqu($request->input('id'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }
}
