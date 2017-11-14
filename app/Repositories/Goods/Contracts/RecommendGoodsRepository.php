<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/12/16
 * Time: 4:51 PM
 */

namespace App\Repositories\Goods\Contracts;

use App\Models\DpRecommendGoods;

interface RecommendGoodsRepository
{
    /**
     * 添加推荐商品
     *
     * @param  $areaId      integer 片区id
     * @param  $goodsId     integer 商品id
     * @param  $putOnTime   string 上架时间
     * @param  $pullOffTime string 下架时间
     *
     * @return DpRecommendGoods
     */
    public function addGoods($areaId, $goodsId, $putOnTime, $pullOffTime);

    /**
     * 获取片区id下的推荐商品列表
     *
     * @param  $areaId     integer 片区id
     *
     * @return mixed
     */
    public function getGoodsList($areaId);

    /**
     * 下架推荐商品
     *
     * @param $recommendId
     *
     * @return integer
     */
    public function pullOffGoods($recommendId);
}