<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/4
 * Time: 19:53
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ShopService;
use App\Services\Shops\MarketService;
use App\Workflows\ShopWorkflow;

class ShopController extends Controller
{
    /**
     * 店铺类型列表
     *
     * @param ShopService $shopService
     *
     * @return \Illuminate\Http\Response
     */
    public function getShopTypeList(
        ShopService $shopService
    ) {
        $reData = $shopService->getShopTypeList();

        return $this->renderTxt(
            'shop.list-txt',
            $reData
        );
    }

    /**
     * 店铺类型信息（和店铺类型列表一样，只是键名变更了）
     *
     * @param ShopService $shopService
     *
     * @return \Illuminate\Http\Response
     */
    public function getShopTypeInfo(
        ShopService $shopService
    ) {
        $reData = $shopService->getShopTypeInfo();

        return $this->renderTxt(
            'shop.list-txt',
            $reData
        );
    }

    /**
     * 所在片区的发货市场获取
     *
     * @param Request       $request
     * @param MarketService $marketService
     *
     * @return \Illuminate\Http\Response
     */
    public function getAreaShipmentMarketList(
        Request $request,
        MarketService $marketService
    ) {
        $this->validate(
            $request,
            [
                'custom_area_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_pianqu_divide,id',
            ],
            [
                'custom_area_id.required' => '片区ID必须有',
                'custom_area_id.integer'  => '片区ID必须是一个整数',
                'custom_area_id.min'      => '片区ID不可小于:min',
                'custom_area_id.exists'   => '片区ID不存在',
            ]
        );

        $reData = $marketService->getAreaShipmentMarketList($request->input('custom_area_id'));
        $reDataArr = [
            'data'    => $reData,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'others.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }

    /**
     * 有待审核商品的市场列表（包括店铺信息）
     *
     * @param Request      $request
     * @param ShopWorkflow $shopWorkflow
     *
     * @return \Illuminate\Http\Response
     */
    public function getNewGoodsMarketList(Request $request, ShopWorkflow $shopWorkflow)
    {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_pianqu_divide,id',
            ],
            [
                'area_id.required' => '片区ID必须有',
                'area_id.integer'  => '片区ID必须是一个整数',
                'area_id.min'      => '片区ID不可小于:min',
                'area_id.exists'   => '片区ID不存在',
            ]
        );

        $reData = $shopWorkflow->getNewGoodsMarketList($request->input('area_id'));
        $reDataArr = [
            'data'    => $reData,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'others.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }
}
