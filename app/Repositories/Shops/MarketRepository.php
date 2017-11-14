<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/21
 * Time: 19:46
 */

namespace App\Repositories\Shops;

use App\Repositories\Shops\Contracts\MarketRepository as RepositoriesContract;

use App\Models\DpMarketInfo;

/**
 * Class MarketRepository.
 * 市场信息处理
 * @package App\Repositories\Shops
 */
class MarketRepository implements RepositoriesContract
{
    /**
     * 更改市场下的商品数量
     *
     * @see \App\Repositories\Shops\Contracts\MarketRepository::updateGoodsNumber()
     *
     * @param $marketId int 市场ID
     * @param $goodsNum int 更改的数量
     *
     * @return void
     */
    public function updateGoodsNumber($marketId, $goodsNum = 0)
    {
        if ($goodsNum > 0) {
            DpMarketInfo::where('pianquId', $marketId)
                ->increment('goods_number', $goodsNum);
        } elseif ($goodsNum < 0) {
            $goodsNum = abs($goodsNum);
            DpMarketInfo::where('pianquId', $marketId)
                ->decrement('goods_number', $goodsNum);
        }
    }

    /**
     * 所在片区的发货市场获取
     *
     * @see \App\Repositories\Shops\Contracts\MarketRepository::getAreaShipmentMarketList()
     *
     * @param       $customAreaId    int 自定义片区ID
     * @param array $columnSelectArr array 字段筛选 格式：['XXX', 'CCC',...]
     *
     * @return array
     */
    public function getAreaShipmentMarketList($customAreaId, array $columnSelectArr)
    {
        $marketObjs = DpMarketInfo::where('divideid', $customAreaId)
            ->where('yipishang', DpMarketInfo::TYPE_YIPI)
            ->select($columnSelectArr)
            ->get();

        return $marketObjs->isEmpty() ? [] : $marketObjs->toArray();
    }
}
