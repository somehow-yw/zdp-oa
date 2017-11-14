<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 11:15
 */

namespace App\Services\DailyNews\Contracts;

abstract class AbstractGoodsPriceRiseOrDecline
{
    /**
     * 获取涨跌榜的商品信息
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数据量
     *
     * @return object
     */
    abstract public function getGoodsInfo($areaId, $size);

    /**
     * 涨跌榜商品屏蔽操作
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return void
     */
    abstract public function shieldGoods($id, $goodsId, $shieldStatus);
}
