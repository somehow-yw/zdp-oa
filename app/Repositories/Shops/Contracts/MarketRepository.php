<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/21
 * Time: 19:46
 */

namespace App\Repositories\Shops\Contracts;

/**
 * Interface MarketRepository.
 * 市场信息处理
 * @package App\Repositories\Shops\Contracts
 */
interface MarketRepository
{
    /**
     * 更改市场下的商品数量
     *
     * @param $marketId int 市场ID
     * @param $goodsNum int 更改的数量
     *
     * @return void
     */
    public function updateGoodsNumber($marketId, $goodsNum = 0);

    /**
     * 所在片区的发货市场获取
     *
     * @param       $customAreaId    int 自定义片区ID
     * @param array $columnSelectArr array 字段筛选 格式：['XXX', 'CCC',...]
     *
     * @return array
     */
    public function getAreaShipmentMarketList($customAreaId, array $columnSelectArr);
}
