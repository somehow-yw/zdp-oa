<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/29
 * Time: 16:44
 */

namespace App\Services\Goods;

use App\Models\DpGoodsBasicAttribute;
use App\Repositories\Goods\Contracts\ActivityGoodsRepository;
use App\Repositories\Goods\Contracts\ActivityRepository;
use App\Repositories\Goods\Contracts\GoodsRepository;
use App\Repositories\Goods\Contracts\GoodsOperationRepository;
use DB;

class ActivityGoodsService
{
    private $activityGoodsRepo;
    private $activityRepo;
    private $goodsOperationRepo;

    public function __construct(
        ActivityGoodsRepository $activityGoodsRepo,
        ActivityRepository $activityRepo,
        GoodsOperationRepository $goodsOperationRepo
    ) {
        $this->activityGoodsRepo = $activityGoodsRepo;
        $this->activityRepo = $activityRepo;
        $this->goodsOperationRepo = $goodsOperationRepo;
    }

    /**
     * 添加秒杀活动商品
     *
     * @param $goodsId     integer 商品id
     * @param $activityId  integer 活动id
     * @param $restrictNum integer 限购数量
     */
    public function addSecKillGoods($goodsId, $activityId, $restrictNum)
    {
        $rule = json_encode(['restrict_buy_num' => $restrictNum]);
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $activityId, $goodsId, $rule) {
                $self->activityGoodsRepo->addActivityGoods($activityId, $goodsId, $rule);
                $self->goodsOperationRepo->updateGoodsTag($goodsId, DpGoodsBasicAttribute::GOODS_TAG_SECKILL);
            }
        );
    }

    /**
     * 添加团购活动商品
     *
     * @param $goodsId     integer 商品id
     * @param $activityId  integer 活动id
     * @param $description string 团购商品描述
     * @param $reduction   double 优惠金额
     */
    public function addGroupBuyGoods($goodsId, $activityId, $description, $reduction)
    {
        $rule = json_encode(['description' => $description]);
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $activityId, $goodsId, $rule, $reduction) {
                $this->activityGoodsRepo->addActivityGoods($activityId, $goodsId, $rule, $reduction);
                $self->goodsOperationRepo->updateGoodsTag($goodsId, DpGoodsBasicAttribute::GOODS_TAG_GROUP_BUY);
            }
        );
    }

    /**
     * 添加买赠活动商品
     *
     * @param $goodsId    integer 商品id
     * @param $activityId integer 活动id
     * @param $buy        integer 购买数量
     * @param $free       integer 赠送数量
     */
    public function addBuyGetFreeGoods($goodsId, $activityId, $buy, $free)
    {
        $rule = json_encode(['buy' => $buy, 'free' => $free]);
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $activityId, $goodsId, $rule) {
                $this->activityGoodsRepo->addActivityGoods($activityId, $goodsId, $rule);
                $self->goodsOperationRepo->updateGoodsTag($goodsId, DpGoodsBasicAttribute::GOODS_TAG_BUY_GET_FREE);
            }
        );
    }

    /**
     * 活动商品列表
     *
     * @param int $activityTypeId 活动类型id
     * @param int $areaId         片区ID
     * @param int $page           当前页数
     * @param int $size           获取数量
     *
     * @return array
     */
    public function getActivityGoodsList($activityTypeId, $areaId, $page, $size)
    {
        $activityGoodsObjs = $this->activityGoodsRepo->getActivityGoodsList($activityTypeId, $areaId, $size);

        $reActivityGoodsArr = [
            'page'       => (int)$page,
            'total'      => $activityGoodsObjs->total(),
            'activities' => [],
        ];
        if (!$activityGoodsObjs->isEmpty()) {
            $activityArrs = [];
            foreach ($activityGoodsObjs as $item) {
                $goodsArr = $this->goodsList($item->activityGoods);
                $activityArrs[] = [
                    'activity_id'      => $item->id,
                    'activity_type_id' => $item->activity_type_id,
                    'goods_lists'      => $goodsArr,
                ];
            }
            $reActivityGoodsArr['activities'] = $activityArrs;
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reActivityGoodsArr,
        ];
    }

    /**
     * 活动商品删除
     *
     * @param $id integer 数据ID
     *
     * @return array
     */
    public function delActivityGoods($id)
    {
        $this->activityGoodsRepo->delActivityGoods($id);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 活动商品清空
     *
     * @param $activityTypeId integer 活动类型id
     *
     * @return array
     */
    public function clearActivityGoods($activityTypeId)
    {
        $this->activityGoodsRepo->clearActivityGoods($activityTypeId);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 活动商品排序
     *
     * @param $currentId  integer 需更改排序的记录ID
     * @param $nextId     integer  更改排序后下一个记录的ID
     * @param $activityId integer 活动ID
     *
     * @return array
     */
    public function sortActivityGoods($currentId, $nextId, $activityId)
    {
        $this->activityGoodsRepo->sortActivityGoods($currentId, $nextId, $activityId);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    private function goodsList($goodsObj)
    {
        $goodsArr = [];
        foreach ($goodsObj as $item) {
            $goodsArr[] = [
                'id'               => $item->id,
                'goods_id'         => $item->goods_id,
                'goods_name'       => $item->goods->gname,
                'goods_price'      => (double)$item->goods->goodsAttribute->goods_price,
                'reduction'        => (double)$item->reduction,
                'rule'             => json_decode($item->rule),
                'seller_shop_name' => $item->goods->shop->seller_shop_name,
            ];
        }

        return $goodsArr;
    }
}