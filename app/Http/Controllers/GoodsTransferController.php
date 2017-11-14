<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/12
 * Time: 14:10
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Goods\GoodsTransferService;

/**
 * Class GoodsTransferController.
 * 待转移的旧表商品管理
 *
 * @package App\Http\Controllers
 */
class GoodsTransferController extends Controller
{
    /**
     * 有商品转移的店铺列表
     *
     * @param Request              $request
     * @param GoodsTransferService $goodsTransferService
     *
     * @return \Illuminate\Http\Response
     */
    public function getShopList(Request $request, GoodsTransferService $goodsTransferService)
    {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_pianqu_divide,id',
            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须是一个整形',
                'area_id.min'      => '片区id不能小于:min',
                'area_id.exists'   => '片区id不存在',
            ]
        );
        $data = $goodsTransferService->getShopList($request->input('area_id'));
        $reData = [
            'data'    => $data,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 需转移的商品列表
     *
     * @param Request              $request
     * @param GoodsTransferService $goodsTransferService
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsList(Request $request, GoodsTransferService $goodsTransferService)
    {
        $this->validate(
            $request,
            [
                'shop_id'      => 'required|integer|min:1|exists:mysql_zdp_main.dp_shopInfo,shopId',
            ],
            [
                'shop_id.required' => '店铺id不能为空',
                'shop_id.integer'  => '店铺id必须是一个整形',
                'shop_id.min'      => '店铺id不能小于:min',
                'shop_id.exists'   => '店铺id不存在',
            ]
        );
        $data = $goodsTransferService->getGoodsList($request->input('shop_id'));
        $reData = [
            'data'    => $data,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 屏蔽旧商品的转移
     *
     * @param Request              $request
     * @param GoodsTransferService $goodsTransferService
     *
     * @return \Illuminate\Http\Response
     */
    public function shieldingOldGoodsTransfer(Request $request, GoodsTransferService $goodsTransferService)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods,id',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是一个整形',
                'goods_id.min'      => '商品id不能小于:min',
                'goods_id.exists'   => '商品id不存在',
            ]
        );
        $data = $goodsTransferService->transferShielding($request->input('goods_id'));
        $reData = [
            'data'    => $data,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 删除待转移的旧商品
     *
     * @param Request              $request
     * @param GoodsTransferService $goodsTransferService
     *
     * @return \Illuminate\Http\Response
     */
    public function delOldGoods(Request $request, GoodsTransferService $goodsTransferService)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods,id',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是一个整形',
                'goods_id.min'      => '商品id不能小于:min',
                'goods_id.exists'   => '商品id不存在',
            ]
        );
        $data = $goodsTransferService->delOldGoods($request->input('goods_id'));
        $reData = [
            'data'    => $data,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}
