<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/14
 * Time: 13:29
 */

namespace App\Repositories\Goods;

use Carbon\Carbon;
use DB;
use Event;
use App;
use Zdp\Search\Services\ElasticService;
use App\Repositories\Goods\Contracts\GoodsOperationRepository as RepositoriesContract;
use App\Models\DpGoodsBasicAttribute;
use App\Models\DpGoodsInfo;
use App\Models\DpGoodsInspectionReport;
use App\Models\DpGoodsPic;

/**
 * Interface GoodsOperationRepository.
 * 商品操作的数据处理 不包括商品的添加及修改
 *
 * @package App\Repositories\Goods\Contracts
 */
class GoodsOperationRepository implements RepositoriesContract
{
    /**
     * 商品图片的删除
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::delGoodsPicture()
     *
     * @param $pictureId int 图片ID
     *
     * @return void
     */
    public function delGoodsPicture($pictureId)
    {
        DpGoodsPic::where('picid', $pictureId)
            ->delete();
    }

    /**
     * 商品检验报告图片的删除
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::delGoodsInspectionReport()
     *
     * @param $pictureId int 图片ID
     *
     * @return void
     */
    public function delGoodsInspectionReport($pictureId)
    {
        DpGoodsInspectionReport::where('id', $pictureId)
            ->delete();
    }

    /**
     * 下架普通商品
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::soldOutOrdinaryGoods()
     *
     * @param $goodsId integer 商品id
     *
     * @return void
     *
     */
    public function soldOutOrdinaryGoods($goodsId)
    {
        $goods = DpGoodsInfo::find($goodsId);
        $goods->on_sale = DpGoodsInfo::GOODS_NOT_ON_SALE;
        $goods->save();
    }

    /**
     * 刷新普通商品价格
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::refreshOrdinaryGoodsPrice()
     *
     * @param $goodsId integer 商品id
     *
     * @return void
     */
    public function refreshOrdinaryGoodsPrice($goodsId)
    {
        /** @var DpGoodsInfo $goods */
        $goods = DpGoodsInfo::find($goodsId);
        /** @var DpGoodsBasicAttribute $attribute */
        $attribute = $goods->goodsAttribute()
            ->where('goods_price', '>', DpGoodsBasicAttribute::GOODS_MIN_PRICE)
            ->first();
        if (is_null($attribute)) {
            return;
        }
        $priceAdjustFrequency = $attribute->price_adjust_frequency;
        if (0 == $priceAdjustFrequency) {
            $priceAdjustFrequency = DpGoodsBasicAttribute::PRICE_OVERDUE_MAX_TIME;
        }
        $today = Carbon::now();
        $today = $today->addDays($priceAdjustFrequency)->format('Y-m-d');
        $attribute->auto_soldout_time = $today . ' ' . DpGoodsBasicAttribute::PRICE_OVERDUE_TIME_SPECIFY;
        $attribute->save();
    }

    /**
     * 删除普通商品id
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::deleteOrdinaryGoods()
     *
     * @param $goodsId integer 商品id
     *
     * @return  void
     */
    public function deleteOrdinaryGoods($goodsId)
    {
        $goods = DpGoodsInfo::find($goodsId);
        $goods->shenghe_act = DpGoodsInfo::STATUS_DEL;

        $goods->save();
    }

    /**
     * 上架普通商品
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::onSaleOrdinaryGoods()
     *
     * @param int $goodsId 商品id
     */
    public function onSaleOrdinaryGoods($goodsId)
    {
        $goods = DpGoodsInfo::find($goodsId);
        $goods->on_sale = DpGoodsInfo::GOODS_SALE;
        $goods->save();
    }

    /**
     * 商品审核通过处理
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::auditPass()
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function auditPass($goodsId)
    {
        $dateTimeCarbon = new Carbon();
        $dateTime = $dateTimeCarbon->format('Y-m-d H:i:s');
        $goodsStatusArr = [
            DpGoodsInfo::STATUS_AUDIT,
            DpGoodsInfo::STATUS_REJECT,
            DpGoodsInfo::STATUS_MODIFY_AUDIT,
        ];
        // 商品状态更改(杨大爷要求现在直接为上架状态 2017-01-02)
        $updateArr = [
            'shenghe_act' => DpGoodsInfo::STATUS_NORMAL,
            'audit_time'  => $dateTime,
            'on_sale'     => DpGoodsInfo::GOODS_SALE,
        ];

        DB::transaction(
            function () use ($goodsId, $goodsStatusArr, $updateArr) {
                // 更改商品的状态
                DpGoodsInfo::where('id', $goodsId)
                    ->whereIn('shenghe_act', $goodsStatusArr)
                    ->update($updateArr);
                // 更改商品的过期时间
                $this->refreshOrdinaryGoodsPrice($goodsId);
                /*$goodsBasicAttribute = DpGoodsBasicAttribute::where('goodsid', $goodsId)->first();
                if (0 == $goodsBasicAttribute->price_adjust_frequency) {
                    $priceAdjustFrequency = DpGoodsBasicAttribute::PRICE_OVERDUE_MAX_TIME;
                } else {
                    $priceAdjustFrequency = $goodsBasicAttribute->price_adjust_frequency;
                }
                //$autoSoldoutTimeCarbon = new Carbon($goodsBasicAttribute->auto_soldout_time);
                $autoSoldoutTimeCarbon = new Carbon();
                $autoSoldoutTime = $autoSoldoutTimeCarbon->addDay($priceAdjustFrequency)->format('Y-m-d');
                $autoSoldoutTime .= DpGoodsBasicAttribute::PRICE_OVERDUE_TIME_SPECIFY;
                $goodsBasicAttribute->auto_soldout_time = $autoSoldoutTime;
                $goodsBasicAttribute->save();*/
            }
        );
    }

    /**
     * 审核拒绝处理
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::auditRefused()
     *
     * @param $goodsId       int 商品ID
     * @param $refusedReason string 拒绝理由
     *
     * @return int
     */
    public function auditRefused($goodsId, $refusedReason)
    {
        $goodsStatusArr = [
            DpGoodsInfo::STATUS_AUDIT,
            DpGoodsInfo::STATUS_MODIFY_AUDIT,
        ];
        $updateArr = [
            'shenghe_act' => DpGoodsInfo::STATUS_REJECT,
        ];
        $updateBasicAttrArr = [
            'remark' => $refusedReason,
        ];

        DB::transaction(
            $updateNum = function () use ($goodsId, $goodsStatusArr, $updateArr, $updateBasicAttrArr) {
                $updateNum = DpGoodsInfo::where('id', $goodsId)
                    ->whereIn('shenghe_act', $goodsStatusArr)
                    ->update($updateArr);

                if ($updateNum) {
                    DpGoodsBasicAttribute::where('goodsid', $goodsId)
                        ->update($updateBasicAttrArr);
                }

                return $updateNum;
            }
        );

        return $updateNum;
    }

    /**
     * 恢复删除为待审核
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::unDeleteOrdinaryGoods()
     *
     * @param $goodsId int 商品ID
     */
    public function unDeleteOrdinaryGoods($goodsId)
    {
        $goods = DpGoodsInfo::find($goodsId);
        $goods->shenghe_act = DpGoodsInfo::STATUS_AUDIT;
        $goods->save();
    }

    /**
     * 根据商品分类ID将商品状态更改为修改待审核
     *
     * @see \App\Repositories\Goods\Contracts\GoodsOperationRepository::updateGoodsStatusToNotAudit()
     *
     * @param $goodsTypeId int 商品分类ID
     *
     * @return int 影响的行数
     */
    public function updateGoodsStatusToNotAudit($goodsTypeId)
    {
        $updateArr = [
            'shenghe_act' => DpGoodsInfo::STATUS_MODIFY_AUDIT,
        ];

        // 获取所有影响商品的ID串
        $goodsIdsCollect = DpGoodsInfo::where('goods_type_id', $goodsTypeId)
            ->select(['id as goods_id'])
            ->get();
        $updateNum = DpGoodsInfo::where('goods_type_id', $goodsTypeId)
            ->update($updateArr);
        if (!$goodsIdsCollect->isEmpty()) {
            $goodsIdArr = $goodsIdsCollect->pluck('goods_id')->toArray();
            // 进行商品搜索索引更新
            /** @var ElasticService $elasticIndexUpdateObj */
            $elasticIndexUpdateObj = App::make(ElasticService::class);
            $elasticIndexUpdateObj->updateGoods($goodsIdArr);
        }

        return $updateNum;
    }
}
