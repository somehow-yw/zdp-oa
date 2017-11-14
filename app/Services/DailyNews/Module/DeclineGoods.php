<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 15:28
 */

namespace App\Services\DailyNews\Module;

use App\Services\DailyNews\Contracts\AbstractGoodsPriceRiseOrDecline;

use App\Repositories\DailyNews\Contracts\DailyNewsDeclineGoodsRepository;

class DeclineGoods extends AbstractGoodsPriceRiseOrDecline
{
    private $declineGoodsRepo;

    public function __construct(
        DailyNewsDeclineGoodsRepository $declineGoodsRepo
    ) {
        $this->declineGoodsRepo = $declineGoodsRepo;
    }

    /**
     * 获取涨跌榜的降价商品信息
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数据量
     *
     * @return object
     */
    public function getGoodsInfo($areaId, $size)
    {
        return $this->declineGoodsRepo->getGoodsList($areaId, $size);
    }

    /**
     * 涨跌榜中降价商品屏蔽操作
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return void
     */
    public function shieldGoods($id, $goodsId, $shieldStatus)
    {
        $this->declineGoodsRepo->shieldGoods($id, $goodsId, $shieldStatus);
    }
}