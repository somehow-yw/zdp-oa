<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/29
 * Time: 16:51
 */

namespace App\Repositories\Goods;

use App\Exceptions\AppException;
use App\Exceptions\Goods\ActivityGoodsExceptionCode;
use App\Models\DpActivity;
use App\Models\DpActivityGoods;
use App\Models\DpGoodsBasicAttribute;
use App\Repositories\Goods\Contracts\ActivityGoodsRepository as RepositoriesContract;
use DB;

class ActivityGoodsRepository implements RepositoriesContract
{
    /**
     * 活动商品添加
     *
     * @see \App\Repositories\Goods\Contracts\ActivityGoodsRepository::addActivityGoods()
     *
     * @param int    $activityId 活动ID
     * @param int    $goodsId    商品ID
     * @param int    $rule       限购数量
     * @param double $reduction  折扣金额
     *
     * @return object
     */
    public function addActivityGoods($activityId, $goodsId, $rule, $reduction = 0)
    {
        $activityObj = $this->getActivityGoodsByActivityIdAndGoodsId($activityId, $goodsId);
        if (is_null($activityObj)) {
            $createArr = [
                'activity_id' => $activityId,
                'goods_id'    => $goodsId,
                'reduction'   => $reduction,
                'rule'        => $rule,
            ];

            $activityGoods = DpActivityGoods::create($createArr);
            $sortValue = $activityGoods->id * 1000000;
            $activityGoods->sort_value = $sortValue;
            $activityGoods->save();
        }
    }

    /**
     * 活动商品列表
     *
     * @see \App\Repositories\Goods\Contracts\ActivityGoodsRepository::getActivityGoodsList()
     *
     * @param     $activityTypeId integer 活动类型id
     * @param int $areaId         片区ID
     * @param int $size           获取数量
     *
     * @return \App\Models\DpActivityGoods Eloquent collect
     */
    public function getActivityGoodsList($activityTypeId, $areaId, $size)
    {
        $goodsInfoObjs = DpActivity::where('activity_type_id', $activityTypeId)->with(
            [
                'activityGoods' => function ($query) {
                    $query->with(
                        [
                            'goods' => function ($query) {
                                $query->with(
                                    [
                                        'shop'           => function ($query) {
                                            $query->select(['shopId', 'dianPuName as seller_shop_name']);
                                        },
                                        'goodsAttribute' => function ($query) {
                                            $query->select(['goodsid', 'goods_price']);
                                        },
                                    ]
                                )->select('id', 'shopid', 'gname');
                            },
                        ]
                    )->select(['id', 'goods_id', 'activity_id', 'reduction', 'rule'])
                        ->orderBy('sort_value', 'asc');
                },
            ]
        )->where('area_id', $areaId)
            ->select(['id', 'shop_type_ids', 'start_time', 'end_time', 'shop_type_ids', 'activity_type_id'])
            ->orderBy('start_time', 'asc')
            ->paginate($size);

        return $goodsInfoObjs;
    }

    /**
     * 活动商品删除
     *
     * @see \App\Repositories\Goods\Contracts\ActivityGoodsRepository::delActivityGoods()
     *
     * @param int $id 数据ID
     *
     * @return void
     */
    public function delActivityGoods($id)
    {
        /** @var DpActivityGoods $goods */
        $goods = DpActivityGoods::where('id', $id)->first();
        $goods->delete();
    }

    /**
     * 活动商品清空及其所属活动清空
     *
     * @see \App\Repositories\Goods\Contracts\ActivityGoodsRepository::clearActivityGoods()
     *
     * @return void
     */
    public function clearActivityGoods($activityTypeId)
    {
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($activityTypeId) {
                //清空活动商品表对应商品
                DpActivityGoods::whereHas(
                    'activity',
                    function ($query) use ($activityTypeId) {
                        $query->select('activity_id')->where('activity_type_id', $activityTypeId);
                    }
                )->delete();
                //清空对应活动类型id的活动表
                DpActivity::where('activity_type_id', $activityTypeId)->delete();
            }
        );
    }

    /**
     * 活动商品排序
     *
     * @see \App\Repositories\Goods\Contracts\ActivityGoodsRepository::sortActivityGoods()
     *
     * @param int $currentId  需更改排序的活动商品记录ID
     * @param int $nextId     更改排序后下一个活动商品记录ID
     * @param int $activityId 活动ID
     *
     * @throws AppException
     * @return void
     */
    public function sortActivityGoods($currentId, $nextId, $activityId)
    {
        //如果nextId==0,则表明排序到最后一个记录
        if ($this->isInsertIntoLast((int)$nextId)) {
            $this->insertIntoLast($currentId);
        } else {
            if (!$this->isExistsActivityGoodsId($nextId)) {
                throw new AppException("排序下一个记录id不存在", ActivityGoodsExceptionCode::SORT_NEXT_ID_NOT_EXISTS);
            }
            $this->validateCanSort($currentId, $nextId);

            //条件满足更改数据
            /** @var DpActivityGoods $nextActivityGoods */
            $currentActivityGoods = DpActivityGoods::find($currentId);
            $nextActivityGoods = DpActivityGoods::find($nextId);
            $beforeActivityGoods =
                DpActivityGoods::where('sort_value', '<', $nextActivityGoods->sort_value)
                    ->orderBy('sort_value', 'desc')
                    ->first();
            $nextSortValue = $nextActivityGoods->sort_value;
            //如果下一id已经最小,则下一id排序值折半 , +1防止出现0当被除数的情况
            if (is_null($beforeActivityGoods)) {
                $sortValue = ($nextSortValue + 1) / 2;
                //取一前一后排序值的平均数
            } else {
                $beforeSortValue = $beforeActivityGoods->sort_value;
                $sortValue = ($nextSortValue - $beforeSortValue + 1) / 2 + $beforeSortValue;
            }
            $currentActivityGoods->sort_value = $sortValue;
            $currentActivityGoods->save();
        }
    }

    /**
     * 判定是否能排序(秒杀不能跨活动批次排序而团购可以)
     *
     * @param $currentId integer 需要排序的活动商品id
     * @param $nextId    integer 更改排序后下一个活动商品记录ID
     *
     * @throws AppException
     */
    private function validateCanSort($currentId, $nextId)
    {
        $currentBuilder = DpActivityGoods::find($currentId);
        $nextBuilder = DpActivityGoods::find($nextId);
        if (DpGoodsBasicAttribute::GOODS_TAG_SECKILL === $currentBuilder
                ->activity()->first()->activity_type_id
            &&
            $currentBuilder->activity_id !== $nextBuilder->activity_id
        ) {
            throw new AppException(
                "秒杀活动排序的两个id不在同一个活动",
                ActivityGoodsExceptionCode::SORT_ACTIVITY_GOODS_NOT_IN_SAME_ACTIVITY
            );
        }
    }

    /**
     * 是否排序到最后
     *
     * @param $nextId
     *
     * @return boolean
     */
    private function isInsertIntoLast($nextId)
    {
        return 0 === $nextId;
    }

    /**
     * 该活动商品是否存在
     *
     * @param $id integer 活动商品id
     *
     * @return boolean
     */
    private function isExistsActivityGoodsId($id)
    {
        return DpActivityGoods::where('id', $id)->exists();
    }

    /**
     * 将当前活动商品id排序到最后一位
     *
     * @param $currentId integer 需要排序的记录id
     */
    private function insertIntoLast($currentId)
    {
        $lastActivityGoods = DpActivityGoods::orderBy('sort_value', 'desc')->first();
        if (!is_null($lastActivityGoods) && $lastActivityGoods->id != $currentId) {
            /** @var DpActivityGoods $activityGood */
            $sortValue = $lastActivityGoods->sort_value + 1000;
            $activityGood = DpActivityGoods::where('id', $currentId)->first();
            $activityGood->update(['sort_value' => $sortValue]);
            $activityGood->save();
        }
    }

    /**
     * 根据活动ID和商品ID取得活动商品
     *
     * @param int $activityId 活动类型ID
     * @param int $goodsId    商品ID
     *
     * @return object
     */
    private function getActivityGoodsByActivityIdAndGoodsId($activityId, $goodsId)
    {
        return DpActivityGoods::where('activity_id', $activityId)
            ->where('goods_id', $goodsId)
            ->first();
    }
}
