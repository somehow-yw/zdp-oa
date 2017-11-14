<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 16-12-15
 * Time: 上午10:05
 */

namespace App\Services\OperationManage\IndexManage\Traits;

use App\Exceptions\AppException;
use App\Models\DpHighQualitySupplier;
use App\Models\DpNewGoods;
use App\Models\DpRecommendGoods;
use Carbon\Carbon;
use DB;

/**
 * 交换两个model的排序值
 * 只针对 DpRecommendGoods|DpHighQualitySupplier|DpNewGoods
 * Class SwapItem
 * @package app\Services\OperationManage\IndexManage\Traits
 */
trait SwapItem
{
    /**
     * 交换两个model的排序值
     *
     * @param $current DpRecommendGoods|DpHighQualitySupplier|DpNewGoods 需要交换的model
     * @param $next    DpRecommendGoods|DpHighQualitySupplier|DpNewGoods 被交换的model
     */
    public function swap($current, $next)
    {
        DB::transaction(function () use ($current, $next) {
            $currentSortValue = $current->sort_value;
            $nextSortValue = $next->sort_value;
            $tempSortValue = $currentSortValue;
            $current->sort_value = $nextSortValue;
            $current->save();
            $next->sort_value = $tempSortValue;
            $next->save();
        });
    }

    /**
     * 判断两个对象是否能够交换
     *
     * @param $current DpRecommendGoods|DpHighQualitySupplier|DpNewGoods 需要交换的model
     * @param $next    DpRecommendGoods|DpHighQualitySupplier|DpNewGoods 被交换的model
     *
     * @throws AppException
     */
    public function canSwap($current, $next)
    {
        //大区不同不能交换
        if ($current->area_id != $next->area_id) {
            throw  new AppException("两条记录所在的大区不同");
        }
        $nowCarbon = Carbon::now();
        $cPutOnAt = new Carbon($current->put_on_at);
        $nPutOnAt = new Carbon($next->put_on_at);
        $cPullOffAt = new Carbon($current->pull_off_at);
        $nPullOffAt = new Carbon($next->pull_off_at);
        if ($cPullOffAt->lt($nowCarbon)) {
            throw  new AppException("需要排序的记录已经下架");
        }
        if ($nPullOffAt->lt($nowCarbon)) {
            throw  new AppException("与之交换的记录已经下架");
        }
        if (($cPutOnAt->gt($nowCarbon) && $nPutOnAt->lte($nowCarbon))) {
            throw  new AppException("需要排序的记录处于待上架而与之交换的记录处于上架");
        }

        if (($nPutOnAt->gt($nowCarbon) && $cPutOnAt->lte($nowCarbon))) {
            throw  new AppException("需要排序的记录处于上架而与之交换的记录处于待上架");
        }
    }
}