<?php
/**
 * Created by PhpStorm.
 * 商品价格变动日志操作
 * User: fer
 * Date: 2016/8/31
 * Time: 10:07
 */

namespace App\Repositories\Goods;

use Carbon\Carbon;
use DB;

use App\Repositories\Goods\Contracts\GoodsPriceChangeLogRepository as RepositoriesContract;

use App\Models\DpGoodsPriceChange;
use App\Models\DpGoodsBasicAttribute;

/**
 * Class GoodsPriceChangeLogRepository.
 * 商品历史价格
 * @package App\Repositories\Goods
 */
class GoodsPriceChangeLogRepository implements RepositoriesContract
{
    /**
     * 获取商品的昨日价格
     *
     * @see \App\Repositories\Goods\Contracts\GoodsPriceChangeLogRepository::getGoodsDayStartChangePrice()
     *
     * @param int    $goodsId 商品ID
     * @param string $dayDate 当天日期
     *
     * @return float
     */
    public function getGoodsDayStartChangePrice($goodsId, $dayDate)
    {
        $goodsTodayPriceObj = DpGoodsPriceChange::where('goodid', $goodsId)
            ->where('edit_time', '>', $dayDate)
            ->select('old_price')
            ->orderBy('edit_time', 'asc')
            ->first();

        return $goodsTodayPriceObj ? $goodsTodayPriceObj->old_price : 0;
    }

    /**
     * 商品价格更改日志记录（同时需更改商品前一次价格及最后改价时间）
     *
     * @see \App\Repositories\Goods\Contracts\GoodsPriceChangeLogRepository::addGoodsPriceLog()
     *
     * @param $originalGoodsPrice float 更改前的商品价格
     * @param $newGoodsPrice      float 更改后的价格
     * @param $goodsId            int 商品ID
     * @param $basicAttrId        int 特殊属性ID
     * @param $operatorId         int 操作者ID
     * @param $operatorTel        int 操作者电话
     * @param $shopId             int 商品所属店铺ID
     *
     * @return mixed
     */
    public function addGoodsPriceLog(
        $originalGoodsPrice,
        $newGoodsPrice,
        $goodsId,
        $basicAttrId,
        $operatorId,
        $operatorTel,
        $shopId
    ) {
        $carbonObj = new Carbon();
        $dateTime = $carbonObj->format('Y-m-d H:i:s');
        $updateArr = [
            'previous_price'        => $originalGoodsPrice,
            'end_price_change_time' => $dateTime,
        ];
        $addArr = [
            'bossid'    => 0,
            'shopid'    => $shopId,
            'goodid'    => $goodsId,
            'basicid'   => $basicAttrId,
            'shid'      => $operatorId,
            'shtel'     => $operatorTel,
            'old_price' => $originalGoodsPrice,
            'new_price' => $newGoodsPrice,
        ];
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($updateArr, $addArr, $basicAttrId) {
                DpGoodsBasicAttribute::where('basicid', $basicAttrId)
                    ->update($updateArr);
                DpGoodsPriceChange::create($addArr);
            }
        );
    }
}
