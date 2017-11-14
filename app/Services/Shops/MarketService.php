<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/24
 * Time: 14:22
 */

namespace App\Services\Shops;

use App\Repositories\Shops\Contracts\MarketRepository;

/**
 * Class MarketService.
 * 市场信息处理
 * @package App\Services\Shops
 */
class MarketService
{
    private $marketRepo;

    public function __construct(MarketRepository $marketRepo)
    {
        $this->marketRepo = $marketRepo;
    }

    /**
     * 所在片区的发货市场获取
     *
     * @param       $customAreaId    int 自定义片区ID
     * @param array $columnSelectArr array 字段筛选 格式：['XXX', 'CCC',...]
     *
     * @return array
     */
    public function getAreaShipmentMarketList($customAreaId, array $columnSelectArr = [])
    {
        if (count($columnSelectArr) === 0) {
            $columnSelectArr = [
                'pianquId as market_id', 'pianqu as market_name',
            ];
        }
        $marketInfoArrs = $this->marketRepo->getAreaShipmentMarketList($customAreaId, $columnSelectArr);

        return $marketInfoArrs;
    }
}
