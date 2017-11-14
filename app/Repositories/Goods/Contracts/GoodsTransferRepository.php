<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/12
 * Time: 14:16
 */

namespace App\Repositories\Goods\Contracts;

interface GoodsTransferRepository
{
    /**
     * 有商品转移的店铺列表
     *
     * @param       $areaId           int 片区ID
     * @param array $selectArr        需获取的表字段
     *
     *                          [
     *                          'shop'=>[...],,
     *                          'market'=>[...],
     *                          ]
     *
     * @return array
     */
    public function getShopList($areaId, array $selectArr);

    /**
     * 需转移的商品列表
     *
     * @param       $shopId      integer 店铺ID
     * @param       $goodsStatus integer 商品状态
     * @param array $selectArr   array   需获取字段 格式：
     *                           [
     *                           'goods'=>[...],
     *                           ]
     *
     * @return array
     */
    public function getGoodsList($shopId, $goodsStatus, array $selectArr);

    /**
     * 屏蔽旧商品的转移
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function transferShielding($goodsId);

    /**
     * 删除待转移的旧商品
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function delOldGoods($goodsId);
}
