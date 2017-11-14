<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/30
 * Time: 17:15
 */

namespace App\Repositories\DailyNews\Contracts;

interface DailyNewsGodsRepository
{
    /**
     * 今日推送团购商品查询
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数量
     *
     * @return object
     */
    public function getBulkPurchasingGoods($areaId, $size);

    /**
     * 今日推送新品或热门商品信息
     *
     * @param int $areaId        大区ID
     * @param int $articleTypeId 文章类型
     * @param int $size          获取数量
     *
     * @return object
     */
    public function getNewProductOrHotSaleGoods($areaId, $articleTypeId, $size);

    /**
     * 每日推文推荐商品列表
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数量
     *
     * @return object
     */
    public function getRecommendGoods($areaId, $size);

    /**
     * 每日推文新品或热门商品屏蔽操作
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return void
     */
    public function shieldNewProductOrHotSaleGoods($id, $goodsId, $shieldStatus);

    /**
     * 获取推荐商品
     *
     * @param int $goodsId 商品ID
     *
     * @return object|null
     */
    public function getRecommendGoodsByGoodsId($goodsId);

    /**
     * 推荐商品添加
     *
     * @param int    $goodsId        商品ID
     * @param string $sellerShopName 卖家店铺名称
     * @param int    $areaId         大区ID
     *
     * @return void
     */
    public function addRecommendGoods($goodsId, $sellerShopName, $areaId);

    /**
     * 删除单个推荐商品
     *
     * @param int $id 记录ID
     *
     * @return void
     */
    public function delRecommendGoods($id);

    /**
     * 删除所有推荐榜商品
     *
     * @return void
     */
    public function delRecommendGoodsAll();

    /**
     * 调整当前推荐榜商品排序
     *
     * @param int $currentId 调整记录的ID
     * @param int $nextId    调整后下一记录ID
     *
     * @return void
     */
    public function sortRecommendGoods($currentId, $nextId);
}