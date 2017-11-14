<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/10/16
 * Time: 5:33 PM
 */

namespace App\Workflows;

use App\Exceptions\AppException;
use App\Exceptions\Goods\ActivityException;
use App\Exceptions\Goods\ActivityGoodsExceptionCode;
use App\Models\DpActivityGoods;
use App\Models\DpGoodsBasicAttribute;
use App\Repositories\Goods\Contracts\ActivityRepository;
use App\Services\Goods\ActivityGoodsService;
use App\Services\Goods\ActivityService;
use Carbon\Carbon;
use DB;

class AddActivityGoodsWorkflow
{
    protected $activityGoodsService;
    protected $activityRepository;


    /**
     * AddActivityGoodsWorkflow constructor.
     *
     * @param $activityGoodsService ActivityGoodsService  活动商品service
     * @param $activityService      ActivityService 活动service
     */
    public function __construct(ActivityGoodsService $activityGoodsService, ActivityRepository $activityRepository)
    {
        $this->activityGoodsService = $activityGoodsService;
        $this->activityRepository = $activityRepository;
    }

    /**
     * 添加秒杀商品
     *
     * @param      $goodsId         integer 商品id
     * @param      $restrictBuyNum  integer 限购数量
     * @param null $activityId      integer 活动数量
     * @param null $starterId       integer 活动发起者id
     * @param null $areaId          integer 区域id
     * @param null $startTime       string  活动开始时间
     * @param null $endTime         string 活动结束时间
     * @param null $shopTypeIds     array 可参见活动的店铺ids
     *
     * @throws AppException
     */
    public function addSecKillGoods(
        $goodsId,
        $restrictBuyNum,
        $activityId = null,
        $starterId = null,
        $areaId = null,
        $startTime = null,
        $endTime = null,
        array $shopTypeIds = null
    ) {
        //验证该商品是否参加了活动
        $this->hasParticipatedInActivities($goodsId);
        //没有活动id 则创建活动
        if (is_null($activityId)) {
            $activityId = $this->validateTimeThenAddActivity(
                $starterId,
                DpGoodsBasicAttribute::GOODS_TAG_SECKILL,
                $areaId,
                $startTime,
                $endTime,
                $shopTypeIds
            );
        }
        //将当前商品id添加到活动id中
        $this->activityGoodsService->addSecKillGoods($goodsId, $activityId, $restrictBuyNum);
    }

    /**
     * 添加团购商品
     *
     * @param            $goodsId         integer    商品id
     * @param            $reduction       double     优惠金额
     * @param null       $description     string     团购商品介绍
     * @param null       $activityId      integer    活动id
     * @param null       $starterId       integer    发起者id
     * @param null       $areaId          integer    区域id
     * @param null       $startTime       string     开始时间
     * @param null       $endTime         string     结束时间
     * @param array|null $shopTypeIds     array      可参加活动的店铺类型ids
     */
    public function addGroupBuyGoods(
        $goodsId,
        $reduction,
        $description,
        $activityId = null,
        $starterId = null,
        $areaId = null,
        $startTime = null,
        $endTime = null,
        array $shopTypeIds = null
    ) {
        //验证该商品是否参加了活动
        $this->hasParticipatedInActivities($goodsId);
        if (is_null($activityId)) {
            $activityId = $this->validateTimeThenAddActivity(
                $starterId,
                DpGoodsBasicAttribute::GOODS_TAG_GROUP_BUY,
                $areaId,
                $startTime,
                $endTime,
                $shopTypeIds
            );
        }
        //将当前商品id添加到活动id中
        $this->activityGoodsService->addGroupBuyGoods($goodsId, $activityId, $description, $reduction);
    }

    /**
     * 添加买赠活动商品
     *
     * @param              $goodsId     integer   商品id
     * @param              $buy         integer   买赠活动购买数量
     * @param              $free        integer    买赠活动赠送数量
     * @param null|integer $activityId  活动id
     * @param null|integer $starterId   活动发起者id
     * @param null|integer $areaId      片区id
     * @param null|string  $startTime   活动开始时间
     * @param null|string  $endTime     活动结束时间
     * @param array|null   $shopTypeIds 可参见活动的店铺类型ids
     */
    public function addBuyGetFreeGoods(
        $goodsId,
        $buy,
        $free,
        $activityId = null,
        $starterId = null,
        $areaId = null,
        $startTime = null,
        $endTime = null,
        array $shopTypeIds = null
    ) {
        //验证该商品是否参加了活动
        $this->hasParticipatedInActivities($goodsId);
        if (is_null($activityId)) {
            $activityId = $this->validateTimeThenAddActivity(
                $starterId,
                DpGoodsBasicAttribute::GOODS_TAG_BUY_GET_FREE,
                $areaId,
                $startTime,
                $endTime,
                $shopTypeIds
            );
        }
        //将当前商品id添加到活动id中
        $this->activityGoodsService->addBuyGetFreeGoods($goodsId, $activityId, $buy, $free);
    }


    /**
     * 校验活动时间是否合法 如果合法则添加活动
     *
     * @param $starterId       integer 活动发起者id
     * @param $activityTypeId  integer 活动类型id
     * @param $areaId          integer 区域id
     * @param $startTime       string 开始时间
     * @param $endTime         string 结束时间
     * @param $shopTypeIds     array 可参加活动的店铺ids
     *
     * @throws AppException|ActivityException
     *
     * @return integer 添加的活动id
     */
    private function validateTimeThenAddActivity(
        $starterId,
        $activityTypeId,
        $areaId,
        $startTime,
        $endTime,
        array $shopTypeIds
    ) {
        //如果活动已经结束,不能新建活动

        $now = Carbon::now()->format('Y-m-d H:i:s');
        if ($endTime < $now) {
            throw new AppException('活动已结束', ActivityGoodsExceptionCode::ACTIVITY_END);
        }
        //如果该活动是秒杀则判断是否有活动时间与其重叠

        if (DpGoodsBasicAttribute::GOODS_TAG_SECKILL === $activityTypeId) {
            $sql = /** @lang MySQL */
                <<<SQL
                SELECT COUNT(*) AS `count` FROM `dp_activities`
            WHERE `area_id`=? AND `activity_type_id`=? AND (
            (`start_time`>=? AND `end_time`<?) OR
            (`start_time`>=? AND `start_time`<?) OR
            (`end_time`>? AND `end_time`<?)
        )
SQL;
            $result = DB::connection('mysql_zdp_main')->select(
                $sql,
                [
                    $areaId, $activityTypeId, $startTime, $endTime, $startTime, $endTime, $startTime, $endTime,
                ]
            );
            $count = $result[0]->count;
            if ($count > 0) {
                throw  new AppException(
                    ActivityException::ACTIVITY_TIME_OVERLAP_MSG,
                    ActivityException::ACTIVITY_TIME_OVERLAP_CODE
                );
            }
        }

        //创建活动
        $activity = $this->activityRepository->addActivity(
            $starterId,
            $activityTypeId,
            $areaId,
            $startTime,
            $endTime,
            $shopTypeIds
        );

        return $activity->id;
    }

    /**
     * 判断该商品是否已经参加了还未结束的活动
     *
     * @param $goodsId integer 商品id
     *
     * @throws  AppException
     */
    private function hasParticipatedInActivities($goodsId)
    {
        $count = DpActivityGoods::where('goods_id', $goodsId)
            ->whereHas(
                'activity',
                function ($query) {
                    $query->where(
                        'end_time',
                        '>',
                        Carbon::now()->format('Y-m-d H:i:s')
                    );
                }
            )->count();
        if (0 !== $count) {
            throw new AppException(
                "该商品已经参加了其他活动",
                ActivityGoodsExceptionCode::GOODS_HAS_PARTICIPATED_OTHER_ACTIVITY
            );
        }
    }
}