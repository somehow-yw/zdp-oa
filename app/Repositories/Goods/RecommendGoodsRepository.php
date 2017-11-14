<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/12/16
 * Time: 4:57 PM
 */

namespace App\Repositories\Goods;

use App\Exceptions\AppException;
use App\Models\DpRecommendGoods;
use App\Repositories\Goods\Contracts\RecommendGoodsRepository as Contract;
use Carbon\Carbon;

class RecommendGoodsRepository implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function addGoods($areaId, $goodsId, $putOnTime, $pullOffTime)
    {
        return DpRecommendGoods::create(
            [
                'area_id'     => $areaId,
                'goods_id'    => $goodsId,
                'put_on_at'   => $putOnTime,
                'pull_off_at' => $pullOffTime,
                'sort_value'  => time(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGoodsList($areaId)
    {
        return DpRecommendGoods::from('dp_recommend_goods as recommend_goods')->select(
            'recommend_goods.id',
            'recommend_goods.goods_id',
            'goods.gname as goods_name',
            'shop.dianPuName as shop_name',
            'recommend_goods.put_on_at',
            'recommend_goods.pull_off_at',
            'recommend_goods.pv'
        )
            ->where('recommend_goods.area_id', $areaId)
            ->join('dp_goods_info as goods', 'recommend_goods.goods_id', '=', 'goods.id')
            ->join('dp_shopinfo as shop', 'goods.shopid', '=', 'shop.shopId')
            ->orderBy('recommend_goods.sort_value')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function pullOffGoods($recommendId)
    {
        /** @var DpRecommendGoods $recommendGoods */
        $recommendGoods = DpRecommendGoods::find($recommendId);
        $carbonNow = Carbon::now();
        $putOnAt = new Carbon($recommendGoods->put_on_at);
        $pullOffAt = new Carbon($recommendGoods->pull_off_at);
        //该推荐商品还没开始展示,该条记录无用直接删除
        if ($putOnAt->gt($carbonNow)) {
            return $recommendGoods->delete();
        }
        //如果已经下架无意义
        if ($pullOffAt->lte($carbonNow)) {
            throw  new  AppException("该推荐商品已经自动下架");
        }
        //下架正在上架的商品，将其下架时间置于当前时间
        $recommendGoods->pull_off_at = time_now();

        return $recommendGoods->save();
    }
}