<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/30
 * Time: 17:15
 */

namespace App\Repositories\DailyNews;

use DB;
use Carbon\Carbon;

use App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository as RepositoriesContract;

use App\Models\DpGoodsBasicAttribute;
use App\Models\DpGoodsInfo;
use App\Models\DpDailyNewsGoodsInfo;
use App\Models\DpDailyNewsRecommendGoods;

use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;
use App\Exceptions\Goods\GoodsExceptionCode;

class DailyNewsGodsRepository implements RepositoriesContract
{
    /**
     * 今日推送团购商品查询
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::getBulkPurchasingGoods()
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数量
     *
     * @return object
     */
    public function getBulkPurchasingGoods($areaId, $size)
    {
        $dayEndDate = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
        $endDate = Carbon::now()->format('Y-m-d H:i:s');
        $selectArr = [
            'goods.id', 'goods.gname',
            'basic_attr.goods_price', 'basic_attr.previous_price', 'basic_attr.end_price_change_time',
            'shop.dianPuName',
            'market.divideid',
        ];
        $goodsInfoObjs = DB::connection('mysql_zdp_main')
            ->table('dp_goods_info as goods')
            ->join('dp_goods_basic_attributes as basic_attr', 'goods.id', '=', 'basic_attr.goodsid')
            ->join('dp_shopInfo as shop', 'goods.shopid', '=', 'shop.shopId')
            ->join('dp_pianqu as market', 'shop.pianquId', '=', 'market.pianquId')
            ->select($selectArr)
            ->where('goods.shenghe_act', DpGoodsInfo::STATUS_NORMAL)
            ->where('basic_attr.tag', DpGoodsBasicAttribute::GOODS_TAG_GROUP_BUY)
            ->where('market.divideid', $areaId)
            ->where(
                function ($query) use ($dayEndDate, $endDate) {
                    $query->where('basic_attr.auto_shelves_time', '<', $dayEndDate)
                        ->where('basic_attr.auto_soldout_time', '>', $endDate);
                }
            )
            ->paginate($size);

        return $goodsInfoObjs;
    }

    /**
     * 今日推送新品或热门商品信息
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::getNewProductOrHotSaleGoods()
     *
     * @param int $areaId        大区ID
     * @param int $articleTypeId 文章类型
     * @param int $size          获取数量
     *
     * @return object
     */
    public function getNewProductOrHotSaleGoods($areaId, $articleTypeId, $size)
    {
        $todayDate = Carbon::now()->format('Y-m-d');

        $goodsInfoObjs = DpDailyNewsGoodsInfo::with(
            [
                'goods' => function ($query) {
                    $query->with(
                        [
                            'goodsAttribute' => function ($query) {
                                $query->select(['goodsid', 'goods_price']);
                            },
                            'shop'           => function ($query) {
                                $query->select(['shopId', 'dianPuName']);
                            },
                        ]
                    )
                        ->select(['id', 'gname', 'shopid', 'shenghe_act']);
                },
            ]
        )
            ->whereHas(
                'goods',
                function ($query) {
                    $query->where('shenghe_act', DpGoodsInfo::STATUS_NORMAL);
                }
            )
            ->withTrashed()
            ->where('area_id', $areaId)
            ->where('show_type', $articleTypeId)
            ->where('show_date', $todayDate)
            ->orderBy('yesterday_sales_num', 'desc')
            ->paginate($size);

        return $goodsInfoObjs;
    }

    /**
     * 每日推文推荐商品列表
     *
     * @param int $areaId 大区ID
     * @param int $size   获取数量
     *
     * @return object
     */
    public function getRecommendGoods($areaId, $size)
    {
        $goodsInfoObjs = DpDailyNewsRecommendGoods::with(
            [
                'goods' => function ($query) {
                    $query->with(
                        [
                            'goodsAttribute' => function ($query) {
                                $query->select(['goodsid', 'goods_price', 'end_price_change_time']);
                            },
                        ]
                    )
                        ->select(['id', 'gname', 'shenghe_act']);
                },
            ]
        )
            ->whereHas('goods',
                function ($query) {
                    $query->whereIn('shenghe_act', [DpGoodsInfo::STATUS_NORMAL, DpGoodsInfo::STATUS_CLOSE]);
                }
            )
            ->where('area_id', $areaId)
            ->orderBy('sort_value', 'asc')
            ->paginate($size);

        return $goodsInfoObjs;
    }

    /**
     * 每日推文新品或热门商品屏蔽操作
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::shieldNewProductOrHotSaleGoods()
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return void
     * @throws AppException
     */
    public function shieldNewProductOrHotSaleGoods($id, $goodsId, $shieldStatus)
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $goodsInfoObj = $this->getGoodsInfo($id, $goodsId, $todayDate);
        if ( ! $goodsInfoObj) {
            throw new AppException('操作的推送商品不存在', DailyNewsExceptionCode::NOT_RECORD);
        }
        if ($goodsInfoObj->trashed() && $shieldStatus == DpDailyNewsGoodsInfo::NOT_DELETE) {
            $goodsInfoObj->restore();
        } elseif ( ! $goodsInfoObj->trashed() && $shieldStatus == DpDailyNewsGoodsInfo::DELETE) {
            $goodsInfoObj->delete();
        }
    }

    /**
     * 根据商品ID获取推荐商品
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::getRecommendGoodsByGoodsId()
     *
     * @param int $goodsId 商品ID
     *
     * @return object|null
     */
    public function getRecommendGoodsByGoodsId($goodsId)
    {
        return DpDailyNewsRecommendGoods::where('goods_id', $goodsId)
            ->select('id')
            ->first();
    }

    /**
     * 推荐商品添加
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::addRecommendGoods()
     *
     * @param int    $goodsId        商品ID
     * @param string $sellerShopName 卖家店铺名称
     * @param int    $areaId         大区ID
     *
     * @return void
     */
    public function addRecommendGoods($goodsId, $sellerShopName, $areaId)
    {
        $dateTimeInt = time();
        $addArr = [
            'goods_id'         => $goodsId,
            'area_id'          => $areaId,
            'seller_shop_name' => $sellerShopName,
            'sort_value'       => $dateTimeInt,
        ];

        DpDailyNewsRecommendGoods::create($addArr);
    }

    /**
     * 删除单个推荐商品
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::delRecommendGoods()
     *
     * @param int $id 记录ID
     *
     * @return void
     */
    public function delRecommendGoods($id)
    {
        DpDailyNewsRecommendGoods::destroy($id);
    }

    /**
     * 删除所有推荐榜商品
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::delRecommendGoodsAll()
     *
     * @return void
     */
    public function delRecommendGoodsAll()
    {
        DpDailyNewsRecommendGoods::truncate();
    }

    /**
     * 调整当前推荐榜商品排序
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository::sortRecommendGoods()
     *
     * @param int $currentId 调整记录的ID
     * @param int $nextId    调整后下一记录ID
     *
     * @return void
     */
    public function sortRecommendGoods($currentId, $nextId)
    {
        $updateArr = [
            'sort_value' => time(),
        ];
        if ( ! empty($nextId)) {
            // 取得下一记录的排序值
            $goodsInfoObj = $this->getRecommendGoodsInfoById($nextId);
            $updateArr['sort_value'] = $goodsInfoObj->sort_value - 1;
        }
        DpDailyNewsRecommendGoods::where('id', $currentId)
            ->update($updateArr);
    }

    private function getRecommendGoodsInfoById($id)
    {
        $goodsInfoObj = DpDailyNewsRecommendGoods::where('id', $id)
            ->select('sort_value')
            ->first();

        if ( ! $goodsInfoObj) {
            throw new AppException('排序后下一个商品ID不存在', GoodsExceptionCode::GOODS_NOT);
        }

        return $goodsInfoObj;
    }

    /**
     * 取得今日推文商品信息（新品、热销）
     *
     * @param int    $id        操作ID
     * @param int    $goodsId   商品ID
     * @param string $todayDate 今日日期
     */
    private function getGoodsInfo($id, $goodsId, $todayDate)
    {
        $goodsInfoObj = DpDailyNewsGoodsInfo::where('id', $id)
            ->where('goods_id', $goodsId)
            ->where('show_date', $todayDate)
            ->withTrashed()
            ->first();

        return $goodsInfoObj;
    }
}