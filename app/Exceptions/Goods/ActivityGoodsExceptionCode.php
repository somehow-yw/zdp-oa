<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/29
 * Time: 18:10
 */

namespace App\Exceptions\Goods;

/**
 * Class ActivityGoodsExceptionCode.
 * 活动商品异常码
 *
 * @package App\Exceptions\Goods
 */
final class ActivityGoodsExceptionCode
{
    /**
     * 活动不存在
     */
    const ACTIVITY_NOT = 101;

    /**
     * 活动已结束
     */
    const ACTIVITY_END = 102;

    /**
     * 排序id不存在
     */
    const SORT_NEXT_ID_NOT_EXISTS = 103;

    /**
     * 排序活动商品不在同一个活动
     */
    const SORT_ACTIVITY_GOODS_NOT_IN_SAME_ACTIVITY = 104;
    /**
     * 购买数量超过了限购上限
     */
    const BUY_NUM_OVER_LIMIT = 105;
    /**
     * 该活动商品已经参加了其他活动
     */
    const GOODS_HAS_PARTICIPATED_OTHER_ACTIVITY = 106;
}