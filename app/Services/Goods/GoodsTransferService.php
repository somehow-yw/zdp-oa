<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/12
 * Time: 14:14
 */

namespace App\Services\Goods;

use App\Repositories\Goods\Contracts\GoodsTransferRepository;

use App\Models\DpGoodsInfo;

class GoodsTransferService
{
    private $goodsTransferRepo;

    public function __construct(GoodsTransferRepository $goodsTransferRepo)
    {
        $this->goodsTransferRepo = $goodsTransferRepo;
    }

    /**
     * 有商品转移的店铺列表
     *
     * @param $areaId int 片区ID
     *
     * @return array
     */
    public function getShopList($areaId)
    {
        $selectArr = [
            'shop'   => [
                'shopId as shop_id', 'dianPuName as shop_name',
            ],
            'market' => ['divideid as area_id', 'pianqu as market_name'],
        ];

        $shopInfoObj = $this->goodsTransferRepo->getShopList($areaId, $selectArr);

        return $shopInfoObj;
    }

    /**
     * 需转移的商品列表
     *
     * @param $shopId      int 店铺ID
     * @param $goodsStatus int 商品状态
     *
     * @return array
     */
    public function getGoodsList($shopId, $goodsStatus = DpGoodsInfo::WAIT_PERFECT)
    {
        $selectArr = [
            'goods' => [
                'id as goods_id', 'gname as goods_name',
            ],
        ];

        $shopInfoObj = $this->goodsTransferRepo->getGoodsList($shopId, $goodsStatus, $selectArr);

        return $shopInfoObj;
    }

    /**
     * 屏蔽旧商品的转移
     *
     * @param $goodsId      int 商品ID
     *
     * @return array
     */
    public function transferShielding($goodsId)
    {
        $this->goodsTransferRepo->transferShielding($goodsId);

        return [];
    }

    /**
     * 删除待转移的旧商品
     *
     * @param $goodsId      int 商品ID
     *
     * @return array
     */
    public function delOldGoods($goodsId)
    {
        $this->goodsTransferRepo->delOldGoods($goodsId);

        return [];
    }
}
