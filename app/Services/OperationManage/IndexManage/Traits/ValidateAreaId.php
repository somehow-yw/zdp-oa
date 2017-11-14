<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/14/16
 * Time: 1:52 PM
 */

namespace App\Services\OperationManage\IndexManage\Traits;

use App\Models\DpGoodsInfo;
use App\Models\DpShopInfo;
use App\Exceptions\AppException;

trait ValidateAreaId
{
    /**
     * 验证areaId是否是shopId对应的areaId
     *
     * @param $areaId
     * @param $shopId
     *
     * @throws AppException
     */
    public function validateShopAreaId($areaId, $shopId)
    {
        $shopAreaId = DpShopInfo::find($shopId)->market->area->id;
        if ($shopAreaId != $areaId) {
            throw  new AppException("你添加的店铺id不属于大区id:{$areaId}");
        }
    }

    /**
     * 验证areaId是否是goodsId对应的areaId
     *
     * @param $areaId
     * @param $goodsId
     *
     * @throws AppException
     */
    public function validateGoodsAreaId($areaId, $goodsId)
    {
        $goodsAreaId = DpGoodsInfo::find($goodsId)->shop->market->area->id;
        if ($areaId != $goodsAreaId) {
            throw new AppException("你所添加的商品不属于大区id:{$areaId}");
        }
    }
}