<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/25
 * Time: 13:36
 */

namespace App\Repositories\Goods;

use App\Models\DpGoodsBasicAttribute;
use Zdp\Main\Data\Models\DpGoodsBasicAttribute as GoodsBasicAttribute;
use App\Models\DpGoodsOperationLog;
use DB;
use Carbon\Carbon;
use App\Repositories\Goods\Contracts\GoodsListRepository as RepositoriesContract;
use App\Models\DpGoodsInfo;
use Zdp\Main\Data\Models\DpGoodsInfo as GoodsInfo;
use App\Models\DpGoodsType;
use Zdp\Main\Data\Models\DpGoodsType as GoodsType;
use App\Models\DpGoodsPriceChange;

/**
 * Class GoodsListRepository.
 * 商品列表
 *
 * @package App\Repositories\Goods
 */
class GoodsListRepository implements RepositoriesContract
{
    /**
     * 获取普通商品列表
     *
     * @see \App\Repositories\Goods\Contracts\GoodsListRepository::getOrdinaryGoodsList()
     *
     */
    public function getOrdinaryGoodsList(
        $areaId,
        $size,
        $goodsTypeId,
        $marketId = null,
        $onSaleStatus = null,
        $auditStatus = null,
        $unit = null,
        $priceStatus = null,
        $keyWords = null,
        $queryBy = 'goods_id',
        $orderBy = 'goods_id',
        $aesc = false,
        $signing = false
    ) {
        $order = $aesc ? "asc" : "desc";
        $query = GoodsInfo::select(
            'dp_goods_info.id as goods_id',     //商品id
            //商品类型名称
            'types.sort_name as goods_type_name',
            //商品名称
            'dp_goods_info.gname as goods_name',
            //商品标题
            'dp_goods_info.goods_title as title',
            //供应商id
            'shop_info.shopId as supplier_id',
            //供应商名称
            'shop_info.dianPuName as supplier_name',
            //商品价格
            'attributes.goods_price as price',
            //商品单位
            'attributes.meter_unit as unit',
            //市场
            'market.pianqu as market',
            //商品价格最后更新于
            'attributes.end_price_change_time as price_updated_at',
            //商品自动下架时间
            'attributes.auto_soldout_time as sold_out_time',
            //商品上下架状态
            'dp_goods_info.on_sale as on_sale_status',
            //商品审核状态
            'dp_goods_info.shenghe_act as audit_status'
        )
            ->whereIn(
                'dp_goods_info.shenghe_act',
                [GoodsInfo::STATUS_NORMAL, GoodsInfo::STATUS_DEL]
            )
            ->join('dp_brands as brands', 'dp_goods_info.brand_id', '=', 'brands.id')
            ->join('dp_goods_basic_attributes as attributes', 'dp_goods_info.id', '=', 'attributes.goodsid')
            ->join('dp_shopInfo as shop_info', 'dp_goods_info.shopid', '=', 'shop_info.shopId')
            ->join('dp_pianqu as market', 'shop_info.pianquId', '=', 'market.pianquId')
            ->whereIn(
                'dp_goods_info.goods_type_id',
                GoodsType::select('id')->whereRaw("FIND_IN_SET({$goodsTypeId},nodeid)")->get()
            )
            ->join('dp_goods_types as types', 'dp_goods_info.goods_type_id', '=', 'types.id')
            ->where('market.divideid', $areaId);
        if ($signing) {
            // 只查询签约的商品
            $query = $query->leftJoin('dp_goods_signings as signing', 'signing.goods_id', '=', 'goods.id')
                ->whereNotNull('signing.signing_name')
                ->select([
                    'signing.id as signing_id',
                    'signing.signing_name',
                ]);
        }
        if (!is_null($marketId)) {
            $query->where('market.pianquId', $marketId);
        }
        if (!is_null($onSaleStatus)) {
            $query->where('dp_goods_info.on_sale', $onSaleStatus);
        }
        if (!is_null($auditStatus)) {
            $query->where('dp_goods_info.shenghe_act', $auditStatus);
        }
        if (!is_null($unit)) {
            $query->where('attributes.meter_unit', $unit);
        }
        if (!is_null($priceStatus)) {
            //1未过期
            $now = Carbon::now()->format('Y-m-d H:i:s');
            if (1 == $priceStatus) {
                $query->where('auto_soldout_time', '>', $now);
                //2过期
            } elseif (2 == $priceStatus) {
                $query->where('auto_soldout_time', '<=', $now);
            }
        }
        if (!is_null($keyWords)) {
            if ($queryBy == "goods_name") {
                $query->where(
                    function ($query) use ($keyWords) {
                        $query->where('gname', 'like', "{$keyWords}%")
                            ->orWhere('gname', 'like', "%{$keyWords}");
                    }
                );
            } elseif ($queryBy == "supplier_name") {
                $query->where(
                    function ($query) use ($keyWords) {
                        $query->where('shop_info.dianPuName', 'like', "{$keyWords}%")
                            ->orWhere('shop_info.dianPuName', 'like', "%{$keyWords}");
                    }
                );

            } elseif ($queryBy == "brand") {
                $query->where(
                    function ($query) use ($keyWords) {
                        $query->where('brands.brand', 'like', "{$keyWords}%")
                            ->orWhere('brands.brand', 'like', "%{$keyWords}");
                    }
                );
            }
        }
        $goodsList = $query->orderBy($orderBy, $order)->paginate($size);
        foreach ($goodsList as &$goods) {
            $goods->unit = GoodsBasicAttribute::getGoodsUnitName($goods->unit);
            //未过期
            if ((new Carbon($goods->sold_out_time))->gt(Carbon::now())) {
                $goods->price_status = 1;
            } else {
                //过期
                $goods->price_status = 2;
            }
            unset($goods->sold_out_time);
            // 是否签约
            $goods->on_signing = !$goods->signing->isEmpty();
        }

        return $goodsList;
    }

    /**
     * 待审核商品列表获取
     *
     * @see \App\Repositories\Goods\Contracts\GoodsListRepository::getNewGoodsList()
     *
     * @param       $shopId          int 店铺ID
     * @param       $goodsStatus     int 商品状态
     * @param       $size            int 获取数量
     * @param array $columnSelectArr array 需要的列信息
     *
     *                               [
     *                                  'goods'=>[...],  商品
     *                                  'goodsAttribute'=>[...],  商品基本属性
     *                                  'shop'=>[...],  店铺
     *                                  'market'=>[...],  市场
     *                               ]
     *
     * @param       $sortField       string 排序字段
     * @param       $marketId        int 市场ID
     * @param       $areaId          int 大区ID
     * @param       $signing         boolean 是否只查询签约商品
     *
     * @return object
     */
    public function getNewGoodsList(
        $shopId,
        $goodsStatus,
        $size,
        array $columnSelectArr,
        $sortField,
        $marketId,
        $areaId,
        $signing
    ) {
        if (!$signing) {
            unset($columnSelectArr['signing']);
        }
        $mainSelectArr = ['goods.id', 'goods.shopid'];
        foreach ($columnSelectArr as $key => $fields) {
            if ('goodsAttribute' != $key) {
                foreach ($fields as $field) {
                    $mainSelectArr[] = "{$key}.{$field}";
                }
            }
        }
        $goodsAttributeSelectArr = array_merge($columnSelectArr['goodsAttribute'], ['goodsid']);
        //$shopSelectArr = array_merge($columnSelectArr['shop'], ['shopId', 'pianquId']);
        //$marketSelectArr = array_merge($columnSelectArr['market'], ['pianquId']);
        if (0 == $goodsStatus) {
            $goodsStatusArr = [
                GoodsInfo::STATUS_AUDIT,
                GoodsInfo::STATUS_REJECT,
                GoodsInfo::STATUS_MODIFY_AUDIT,
            ];
        } else {
            $goodsStatusArr = [$goodsStatus];
        }
        if ('updated_at' == $sortField) {
            $sortField = 'gengxin_time';
        }
        $query = GoodsInfo::from('dp_goods_info as goods')
            ->with([
                'goodsAttribute' => function ($query) use ($goodsAttributeSelectArr) {
                    $query->select($goodsAttributeSelectArr);
                },
                /*'shop'           => function ($query) use ($shopSelectArr, $marketSelectArr) {
                    $query->with(
                        [
                            'market' => function ($query) use ($marketSelectArr) {
                                $query->select($marketSelectArr);
                            },
                        ]
                    )
                        ->select($shopSelectArr);
                },*/
            ])->join('dp_shopInfo as shop', 'goods.shopid', '=', 'shop.shopId')
            ->join('dp_pianqu as market', 'shop.pianquId', '=', 'market.pianquId');
        if ($signing) {
            // 只查询签约的商品
            $query = $query->leftJoin('dp_goods_signings as signing', 'signing.goods_id', '=', 'goods.id')
                ->whereNotNull('signing.signing_name');
        }
        $query = $query->whereIn('goods.shenghe_act', $goodsStatusArr)
            ->where('market.divideid', $areaId)
            ->select($mainSelectArr)
            ->orderBy($sortField, 'desc');
        if (!empty($shopId)) {
            $query = $query->where('goods.shopid', $shopId);
        }
        if (!empty($marketId)) {
            $query = $query->where('shop.pianquId', $marketId);
        }
        $goodsInfoObjs = $query->paginate($size);

        return $goodsInfoObjs;
    }

    /**
     * 获取商品历史价格列表
     *
     * @param       $goodsId          int 商品ID
     * @param array $columnSelectArr  array 需要的列信息 格式：['...','...']
     *
     * @return object
     */
    public function getHistoryPricesList($goodsId, array $columnSelectArr)
    {
        $selectDate = DB::raw('DATE_FORMAT(edit_time,"%Y-%m-%d") as date');
        $columnSelectArr[] = $selectDate;
        $historyPriceObjs = DpGoodsPriceChange::where('goodid', $goodsId)
            ->select($columnSelectArr)
            ->orderBy('id', 'desc')
            ->get();

        return $historyPriceObjs;
    }

    /**
     * 获取商品操作日志列表
     *
     * @param $goodsId integer 商品id
     * @param $size    integer 获取的数据大小
     *
     * @return array
     */
    public function getGoodsOperationLogs($goodsId, $size)
    {
        $logs = DpGoodsOperationLog::where('goods_id', $goodsId)
            ->orderBy('created_at', 'desc')
            ->paginate($size);

        return $logs;
    }

}
