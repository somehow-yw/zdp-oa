<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 10:05
 */

namespace App\Repositories\Goods\Contracts;

interface GoodsPriceChangeLogRepository
{
    /**
     * 获取商品的昨日价格
     *
     * @param int    $goodsId 商品ID
     * @param string $dayDate 当天日期
     *
     * @return float
     */
    public function getGoodsDayStartChangePrice($goodsId, $dayDate);

    /**
     * 商品价格更改日志记录（同时需更改商品前一次价格及最后改价时间）
     *
     * @param $originalGoodsPrice float 更改前的商品价格
     * @param $newGoodsPrice      float 更改后的价格
     * @param $goodsId            int 商品ID
     * @param $basicAttrId        int 特殊属性ID
     * @param $operatorId         int 操作者ID
     * @param $operatorTel        int 操作者电话
     * @param $shopId             int 商品所属店铺ID
     *
     * @return mixed
     */
    public function addGoodsPriceLog(
        $originalGoodsPrice,
        $newGoodsPrice,
        $goodsId,
        $basicAttrId,
        $operatorId,
        $operatorTel,
        $shopId
    );
}
