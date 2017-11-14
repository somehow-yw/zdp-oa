<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 15:55
 */

namespace App\Repositories\DailyNews\Contracts;

interface DailyNewsDeclineGoodsRepository
{
    /**
     * 取得商品信息
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数据量
     *
     * @return object
     */
    public function getGoodsList($areaId, $size);

    /**
     * 商品屏蔽操作
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return void
     */
    public function shieldGoods($id, $goodsId, $shieldStatus);
}