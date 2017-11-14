<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 11:17
 */

namespace App\Services\DailyNews\Module;

use App\Services\DailyNews\Contracts\AbstractGoodsPriceRiseOrDecline;

use App\Repositories\DailyNews\Contracts\DailyNewsRiseGoodsRepository;

class RiseGoods extends AbstractGoodsPriceRiseOrDecline
{
    private $riseGoodsRepo;

    public function __construct(
        DailyNewsRiseGoodsRepository $riseGoodsRepo
    ) {
        $this->riseGoodsRepo = $riseGoodsRepo;
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
        return $this->riseGoodsRepo->getGoodsList($areaId, $size);
    }

    /**
     * 涨跌榜中涨价商品屏蔽操作
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return void
     */
    public function shieldGoods($id, $goodsId, $shieldStatus)
    {
        $this->riseGoodsRepo->shieldGoods($id, $goodsId, $shieldStatus);
    }
}