<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/24
 * Time: 17:48
 */

namespace App\Workflows;

use App;
use App\Models\DpGoodsInfo;
use App\Services\Shops\MarketService;
use App\Services\ShopService;

/**
 * Class ShopWorkflow.
 * 店铺处理
 *
 * @package App\Workflows
 */
class ShopWorkflow
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * 有待审核商品的市场列表（包括店铺信息）
     *
     * @param $areaId int 片区ID
     *
     * @return array
     */
    public function getNewGoodsMarketList($areaId)
    {
        // 取得片区下面的所有可发货市场
        /** @var  $marketService MarketService */
        $marketService = App::make(MarketService::class);
        $marketInfoArr = $marketService->getAreaShipmentMarketList($areaId);

        $reArr = [];
        if (count($marketInfoArr) > 0) {
            $key = 0;
            foreach ($marketInfoArr as $market) {
                // 取得有待审核商品的店铺及待审核商品数量
                $shopInfoArr = $this->shopService->getShopInfoByMarket($market['market_id']);
                if (count($shopInfoArr)) {
                    $reArr['markets'][$key] = [
                        'market_id'   => $market['market_id'],
                        'market_name' => $market['market_name'],
                        'shops'       => [],
                    ];
                    foreach ($shopInfoArr as $shop) {
                        // 获取指定店铺的需审核商品数量
                        $notAuditGoodsNumber = DpGoodsInfo::getNotAuditGoodsNum($shop->shop_id);
                        $reArr['markets'][$key]['shops'][] = [
                            'shop_id'          => $shop->shop_id,
                            'shop_name'        => $shop->shop_name,
                            'new_goods_number' => $notAuditGoodsNumber,
                        ];
                    }
                    $key++;
                }
            }
        }

        return $reArr;
    }
}
