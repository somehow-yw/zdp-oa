<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/14/16
 * Time: 11:44 AM
 */

namespace App\Repositories\Goods;

use App\Models\DpNewGoods;
use App\Repositories\Goods\Contracts\NewGoodsRepository as Contract;
use Carbon\Carbon;
use App\Exceptions\AppException;

class NewGoodsRepository implements Contract
{
    /**
     * @inheritDoc
     */
    public function addNewGoods($areaId, $goodsId, $putOnAt, $pullOffAt)
    {
        return DpNewGoods::create(
            [
                'area_id'     => $areaId,
                'goods_id'    => $goodsId,
                'put_on_at'   => $putOnAt,
                'pull_off_at' => $pullOffAt,
                'sort_value'  => time(),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getGoodsList($areaId)
    {
        return DpNewGoods::from('dp_new_goods as new_goods')->select(
            'new_goods.put_on_at',
            'new_goods.pull_off_at',
            'new_goods.pv',
            'new_goods.goods_id',
            'goods.gname as goods_name',
            'shop.dianPuName as shop_name'
        )
            ->where('new_goods.area_id', $areaId)
            ->join('dp_goods_info as goods', 'new_goods.goods_id', '=', 'goods.id')
            ->join('dp_shopinfo as shop', 'goods.shopid', '=', 'shop.shopId')
            ->orderBy('new_goods.sort_value')
            ->paginate();
    }

    /**
     * 下架新上好货
     *
     * @param $id integer 新上好货记录自增id
     *
     * @return bool
     * @throws AppException
     */
    public function pullOffGoods($id)
    {
        /** @var DpRecommendGoods $newGoods */
        $newGoods = DpNewGoods::find($id);
        $carbonNow = Carbon::now();
        $putOnAt = new Carbon($newGoods->put_on_at);
        $pullOffAt = new Carbon($newGoods->pull_off_at);
        //还没开始展示 直接删除该条记录
        if ($putOnAt->gt($carbonNow)) {
            return $newGoods->delete();
        }
        //已经自动下架
        if ($pullOffAt->lte($carbonNow)) {
            throw new AppException("该推荐商品已经自动下架");
        }

        $newGoods->pull_off_at = time_now();

        return $newGoods->save();
    }
}