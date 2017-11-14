<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 15:55
 */

namespace App\Repositories\DailyNews;

use Carbon\Carbon;

use App\Repositories\DailyNews\Contracts\DailyNewsDeclineGoodsRepository as RepositoriesContract;

use App\Models\DpFallRecord;
use App\Models\DpDailyNewsGoodsInfo;

use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;

class DailyNewsDeclineGoodsRepository implements RepositoriesContract
{
    /**
     * 取得商品信息
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsDeclineGoodsRepository::getGoodsList()
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数据量
     *
     * @return object
     */
    public function getGoodsList($areaId, $size)
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $goodsInfoObjs = DpFallRecord::where('divide_id', $areaId)
            ->where('created_date', $todayDate)
            ->orderBy('range', 'desc')
            ->withTrashed()
            ->paginate($size);

        return $goodsInfoObjs;
    }

    /**
     * 商品屏蔽操作
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsDeclineGoodsRepository::shieldGoods()
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return void
     * @throws AppException
     */
    public function shieldGoods($id, $goodsId, $shieldStatus)
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $goodsInfoObj = $this->getGoodsInfo($id, $goodsId, $todayDate);
        if ( ! $goodsInfoObj) {
            throw new AppException('操作的推送商品不存在', DailyNewsExceptionCode::NOT_RECORD);
        }
        if ($goodsInfoObj->trashed() && $shieldStatus == DpDailyNewsGoodsInfo::NOT_DELETE) {
            $goodsInfoObj->restore();
        } elseif ( ! $goodsInfoObj->trashed() && $shieldStatus == DpDailyNewsGoodsInfo::DELETE) {
            $goodsInfoObj->delete();
        }
    }

    /**
     * 取得今日推文商品信息
     *
     * @param int    $id        操作ID
     * @param int    $goodsId   商品ID
     * @param string $todayDate 今日日期
     */
    private function getGoodsInfo($id, $goodsId, $todayDate)
    {
        $goodsInfoObj = DpFallRecord::where('id', $id)
            ->where('goods_id', $goodsId)
            ->where('created_date', $todayDate)
            ->withTrashed()
            ->first();

        return $goodsInfoObj;
    }
}