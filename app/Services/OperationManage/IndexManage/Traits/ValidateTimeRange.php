<?php
namespace App\Services\OperationManage\IndexManage\Traits;

use App\Exceptions\AppException;
use App\Models\DpBannerInfo;
use App\Models\DpBrandsHouse;
use App\Models\DpHighQualitySupplier;
use App\Models\DpNewGoods;
use App\Models\DpPopupAds;
use App\Models\DpRecommendGoods;

/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/12/16
 * Time: 4:13 PM
 */
trait ValidateTimeRange
{
    /**
     * 验证新推荐商品是否有效
     *
     * @param $goodsId   integer 商品id
     * @param $putOnAt   string  上架展示时间
     * @param $pullOffAt string  下架时间
     *
     * @throws AppException
     */
    public function validateRecommendGoodsTimeRange($goodsId, $putOnAt, $pullOffAt)
    {
        $now = time_now();

        $existsRows = DpRecommendGoods::where('goods_id', $goodsId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->where('pull_off_at', '>', $now)
            ->count();

        if ($existsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt}商品id:{$goodsId}与它已有的推荐好货上架时间段重叠");
        }
    }

    /**
     * 验证新优质供应商是否重叠
     *
     * @param $areaId    integer  大区id
     * @param $shopId    integer  店铺id
     * @param $putOnAt   string   上架展示时间
     * @param $pullOffAt string   下架时间
     * @param $position  integer  展示位置
     *
     * @throws AppException
     */
    public function validateSupplierOverlap($areaId, $shopId, $putOnAt, $pullOffAt, $position)
    {
        $now = time_now();
        $existsRows = DpHighQualitySupplier::where('area_id', $areaId)
            ->where('shop_id', $shopId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->where('pull_off_at', '>', $now)
            ->count();

        if ($existsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt}店铺id:{$shopId}与它已有的优质供应商上架时间段重叠");
        }

        $existsRows = DpHighQualitySupplier::where('area_id', $areaId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->where('pull_off_at', '>', $now)
            ->where('position', $position)
            ->count();

        if ($existsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt} 展示位置:{$position}已经有优质供应商了");
        }
    }

    /**
     * 验证品牌是否有重叠
     *
     * @param $areaId     integer  大区id
     * @param $brandId    integer  品牌id
     * @param $putOnAt    string   上架展示时间
     * @param $pullOffAt  string   下架时间
     * @param $position   integer  展示位置
     *
     * @throws AppException
     */
    public function validateBrandsOverlap($areaId, $brandId, $putOnAt, $pullOffAt, $position)
    {
        $now = time_now();

        $existsRows = DpBrandsHouse::where('area_id', $areaId)
            ->where('brand_id', $brandId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->where('pull_off_at', '>', $now)
            ->count();

        if ($existsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt}品牌id:{$brandId}与它已有的品牌上架时间段重叠");
        }

        $existsRows = DpBrandsHouse::where('area_id', $areaId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->where('pull_off_at', '>', $now)
            ->where('position', $position)
            ->count();

        if ($existsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt} 展示位置:{$position}已经有品牌了");
        }
    }

    /**
     * 校验banner是否重叠
     *
     * @param $areaId    integer   大区id
     * @param $putOnAt   string   上架展示时间
     * @param $pullOffAt string   下架时间
     * @param $position  integer  展示位置
     *
     * @throws AppException
     */
    public function validateBannerOverlap($areaId, $putOnAt, $pullOffAt, $position)
    {
        $now = time_now();

        $existsRows = DpBannerInfo::where('area_id', $areaId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->where('pull_off_at', '>', $now)
            ->where('position', $position)
            ->count();

        if ($existsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt} 展示位置:{$position}已经有banner了");
        }
    }

    /**
     * 验证新上好货是否有效
     *
     * @param $goodsId   integer 商品id
     * @param $putOnAt   string  上架展示时间
     * @param $pullOffAt string  下架时间
     *
     * @throws AppException
     */
    public function validateNewGoodsTimeRange($goodsId, $putOnAt, $pullOffAt)
    {
        $now = time_now();

        $existsRows = DpNewGoods::where('goods_id', $goodsId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->where('pull_off_at', '>', $now)
            ->count();
        if ($existsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt}商品id:{$goodsId}与已有的新上好货上架时间段重叠");
        }
    }

    /**
     * 校验弹窗广告的时间是否合法
     * 即同一时间同一大区只能有一个弹窗广告
     *
     * @param $areaId     integer  大区id
     * @param $putOnAt    string   上架展示时间
     * @param $pullOffAt  string   下架时间
     *
     * @throws AppException
     */
    public function validatePopupAdsTimeRange($areaId, $putOnAt, $pullOffAt)
    {
        $exitsRows = DpPopupAds::where('area_id', $areaId)
            ->where('put_on_at', '<=', $pullOffAt)
            ->where('pull_off_at', '>', $putOnAt)
            ->count();

        if ($exitsRows > 0) {
            throw  new AppException("时间段{$putOnAt}--{$pullOffAt}已经有弹窗广告了");
        }
    }
}
