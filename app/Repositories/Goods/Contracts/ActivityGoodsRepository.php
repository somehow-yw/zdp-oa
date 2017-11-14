<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/29
 * Time: 16:51
 */

namespace App\Repositories\Goods\Contracts;

interface ActivityGoodsRepository
{
    /**
     * 活动商品添加
     *
     * @param int    $activityId 活动ID
     * @param int    $goodsId    商品ID
     * @param int    $rule       限购数量
     * @param double $reduction  优惠金额
     *
     * @return object
     */
    public function addActivityGoods($activityId, $goodsId, $rule, $reduction = 0);

    /**
     * 活动商品列表
     *
     * @param     $activityTypeId integer 活动类型id
     * @param int $areaId         片区ID
     * @param int $size           获取数量
     *
     * @return \App\Models\DpActivityGoods Eloquent collect
     */
    public function getActivityGoodsList($activityTypeId, $areaId, $size);

    /**
     * 活动商品删除
     *
     * @param int $id 数据ID
     *
     * @return void
     */
    public function delActivityGoods($id);

    /**
     * 活动商品清空
     *
     * @param $activityTypeId integer 活动类型id
     *
     * @return void
     */
    public function clearActivityGoods($activityTypeId);

    /**
     * 活动商品排序
     *
     * @param int $currentId  需更改排序的记录ID
     * @param int $nextId     更改排序后下一个记录的ID
     * @param int $activityId 活动ID
     *
     * @return void
     */
    public function sortActivityGoods($currentId, $nextId, $activityId);
}
