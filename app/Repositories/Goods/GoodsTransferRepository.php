<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/12
 * Time: 14:16
 */

namespace App\Repositories\Goods;

use DB;

use App\Repositories\Goods\Contracts\GoodsTransferRepository as RepositoriesContract;
use App\Models\DpGoods;
use App\Models\DpGoodsInfo;
use App\Models\DpShopInfo;
use App\Models\DpGoodsBasicAttribute;

/**
 * Class GoodsTransferRepository.
 * 待转移商品处理
 *
 * @package App\Repositories\Goods
 */
class GoodsTransferRepository implements RepositoriesContract
{
    /**
     * 有商品转移的店铺列表
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTransferRepository::getShopList()
     *
     * @param       $areaId     int 片区ID
     * @param array $selectArr  需获取的表字段
     *                          [
     *                          'shop'=>[...],
     *                          'market'=>[...],
     *                          ]
     *
     * @return array
     */
    public function getShopList($areaId, array $selectArr)
    {
        $shopTypeArr = [
            DpShopInfo::YIPI,
            DpShopInfo::VENDOR,
        ];
        $fieldsArr = [];
        foreach ($selectArr as $key => $fields) {
            foreach ($fields as $field) {
                $fieldsArr[] = "{$key}.{$field}";
            }
        }
        $shopInfo = DB::connection('mysql_zdp_main')->table('dp_shopInfo as shop')
            ->join('dp_goods_info as goods', 'shop.shopId', '=', 'goods.shopid')
            ->join('dp_goods_basic_attributes as basic_attr', 'goods.id', '=', 'basic_attr.goodsid')
            ->join('dp_pianqu as market', 'shop.pianquId', '=', 'market.pianquId')
            ->where('goods.shenghe_act', DpGoodsInfo::WAIT_PERFECT)
            ->where('basic_attr.tag', DpGoodsBasicAttribute::GOODS_TAG_MAIN)
            ->whereIn('shop.trenchnum', $shopTypeArr)
            ->where('shop.state', DpShopInfo::STATE_NORMAL)
            ->where('market.divideid', $areaId)
            ->select($fieldsArr)
            ->groupBy('shop.shopId')
            ->orderBy('shop.shopId', 'asc')
            ->get();

        return $shopInfo;
    }

    /**
     * 需转移的商品列表
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTransferRepository::getGoodsList()
     *
     * @param       $shopId      integer 店铺ID
     * @param       $goodsStatus integer 商品状态
     * @param array $selectArr   array   需获取字段 格式：
     *                           [
     *                           'goods'=>[...],
     *                           ]
     *
     * @return array
     */
    public function getGoodsList($shopId, $goodsStatus, array $selectArr)
    {
        $fieldsArr = [];
        foreach ($selectArr as $key => $fields) {
            foreach ($fields as $field) {
                $fieldsArr[] = "{$key}.{$field}";
            }
        }
        $goodsInfo = DB::connection('mysql_zdp_main')->table('dp_goods_info as goods')
            ->join('dp_goods_basic_attributes as basic_attr', 'goods.id', '=', 'basic_attr.goodsid')
            ->where('goods.shopid', $shopId)
            ->where('goods.shenghe_act', $goodsStatus)
            ->where('basic_attr.tag', DpGoodsBasicAttribute::GOODS_TAG_MAIN)
            ->select($fieldsArr)
            ->get();

        return $goodsInfo;
    }

    /**
     * 屏蔽旧商品的转移
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTransferRepository::transferShielding()
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function transferShielding($goodsId)
    {
        $goodsStatusArr = [
            DpGoods::STATUS_CLOSE,
            DpGoods::STATUS_DEL,
        ];
        DpGoods::where('id', $goodsId)
            ->whereIn('shenghe_act', $goodsStatusArr)
            ->update(['transfer' => DpGoods::SHIELDING]);
    }

    /**
     * 屏蔽旧商品的转移
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTransferRepository::delOldGoods()
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function delOldGoods($goodsId)
    {
        $goodsStatusArr = [
            DpGoods::STATUS_NORMAL,
        ];
        DpGoods::where('id', $goodsId)
            ->whereIn('shenghe_act', $goodsStatusArr)
            ->update(['shenghe_act' => DpGoods::STATUS_DEL]);
    }
}
