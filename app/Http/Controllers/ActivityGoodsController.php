<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/29
 * Time: 15:26
 */

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Exceptions\Goods\ActivityException;
use App\Http\Controllers\RequestTraits\ActivityRequestTrait;
use App\Models\DpGoodsBasicAttribute;
use App\Services\Goods\ActivityService;
use App\Workflows\AddActivityGoodsWorkflow;
use Illuminate\Http\Request;

use App\Services\Goods\ActivityGoodsService;

class ActivityGoodsController extends Controller
{
    use ActivityRequestTrait;

    /**
     * 活动商品添加
     *
     * @param Request              $request
     * @param ActivityGoodsService $activityGoodsService
     * @param ActivityService      $activityService
     *
     * @return \Illuminate\Http\Response
     *
     * @throws AppException
     */
    public function addActivityGoods(
        Request $request,
        AddActivityGoodsWorkflow $activityGoodsWorkflow,
        ActivityService $activityService
    ) {
        if (!$request->has('activity_id')) {
            $this->validateActivity($request);
        }

        $this->validate(
            $request,
            [
                'activity_id' => 'integer|exists:mysql_zdp_main.dp_activities,id',
                'goods_id'    => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
            ],
            [
                'activity_id.integer' => '活动id必须是整形',
                'activity_id.exists'  => '活动id不存在',

                'goods_id.required' => '商品ID必须有',
                'goods_id.integer'  => '商品ID应该是一个整型',
                'goods_id.exists'   => '商品id不存在',
            ]
        );
        if ($request->has('activity_id')) {
            $activityColumns =
                $activityService->getActivityByActivityId($request->input('activity_id'), ['activity_type_id']);
            $activityTypeId = $activityColumns['activity_type_id'];
        } else {
            $activityTypeId = $request->input('activity_type_id');
        }
        //团购活动
        if (DpGoodsBasicAttribute::GOODS_TAG_GROUP_BUY == $activityTypeId) {
            $this->validate(
                $request,
                [
                    'reduction'   => 'required|numeric|between:0,1000',
                    'description' => 'required|string|max:1000',

                ],
                [
                    'reduction.required' => '团购必须填写优惠金额',
                    'reduction.numeric'  => '团购优惠金额必须是个数字',
                    'reduction.between'  => '团购优惠金额必须在:min到:max',

                    'description.required' => '参加团购的活动商品描述必须填写',
                    'description.string'   => '参加团购的活动商品描述必须为字符串',
                    'description.max'      => '参加团购的活动商品描述不能超过:max',
                ]
            );
            $activityGoodsWorkflow->addGroupBuyGoods(
                $request->input('goods_id'),
                $request->input('reduction'),
                $request->input('description'),
                $request->input('activity_id'),
                $request->user()->id,
                $request->input('area_id'),
                $request->input('start_time'),
                $request->input('end_time'),
                $request->input('shop_type_ids')
            );
            //秒杀活动
        } elseif (DpGoodsBasicAttribute::GOODS_TAG_SECKILL == $activityTypeId) {
            $this->validate(
                $request,
                [
                    'restrict_buy_num' => 'required|integer|between:1,32766',
                ],
                [
                    'restrict_buy_num.required' => '秒杀活动限购数量必须有',
                    'restrict_buy_num.integer'  => '秒杀活动限购数量应该是一个整型',
                    'restrict_buy_num.between'  => '秒杀活动限购数量必须是:min到:max的整数',
                ]
            );
            $activityGoodsWorkflow->addSecKillGoods(
                $request->input('goods_id'),
                $request->input('restrict_buy_num'),
                $request->input('activity_id'),
                $request->user()->id,
                $request->input('area_id'),
                $request->input('start_time'),
                $request->input('end_time'),
                $request->input('shop_type_ids')
            );
            //买赠活动
        } elseif (DpGoodsBasicAttribute::GOODS_TAG_BUY_GET_FREE == $activityTypeId) {
            $this->validate(
                $request,
                [
                    'buy'  => 'required|integer|between:1,32766',
                    'free' => 'required|integer|between:1,32766',
                ],
                [
                    'buy.required' => '买赠的购买数量不能为空',
                    'buy.integer'  => '买赠的购买数量必须是一个整数',
                    'buy.between'  => '买赠的购买数量必须是:min到:max的整数',

                    'free.required' => '买赠的赠送数量不能为空',
                    'free.integer'  => '买赠的赠送数量必须是一个整数',
                    'free.between'  => '买赠的赠送数量必须是:min到:max的整数',
                ]
            );
            $activityGoodsWorkflow->addBuyGetFreeGoods(
                $request->input('goods_id'),
                $request->input('buy'),
                $request->input('free'),
                $request->input('activity_id'),
                $request->user()->id,
                $request->input('area_id'),
                $request->input('start_time'),
                $request->input('end_time'),
                $request->input('shop_type_ids')
            );
        } else {
            throw  new AppException("没有当前活动类型id", ActivityException::ACTIVITY_TYPE_ID_NOT_FOUND);
        }


        $reData = [
            'data'    => [],
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 活动商品列表
     *
     * @param Request              $request
     * @param ActivityGoodsService $activityGoodsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getActivityGoodsList(Request $request, ActivityGoodsService $activityGoodsService)
    {
        $this->validate(
            $request,
            [
                'activity_type_id' => 'required|integer|min:1',
                'area_id'          => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'page'             => 'required|integer|min:1',
                'size'             => 'required|integer|between:1,1000',
            ],
            [
                'activity_type_id.required' => '活动类型id不能为空',
                'activity_type_id.integer'  => '活动类型id必须为一个整数',
                'activity_type_id.min'      => '活动类型id不能小于:min',

                'area_id.required' => '片区ID必须有',
                'area_id.integer'  => '片区ID应该是一个整型',
                'area_id.exists'   => '片区id不存在',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数应该是一个整型',
                'page.min'      => '当前页数不可小于:min',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量应该是一个整型',
                'size.between'  => '获取数量必须是:min到:max的整数',
            ]
        );

        $reData = $activityGoodsService->getActivityGoodsList(
            $request->input('activity_type_id'),
            $request->input('area_id'),
            $request->input('page'),
            $request->input('size')
        );

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 活动商品删除
     *
     * @param Request              $request
     * @param ActivityGoodsService $activityGoodsService
     *
     * @return \Illuminate\Http\Response
     */
    public function delActivityGoods(Request $request, ActivityGoodsService $activityGoodsService)
    {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|exists:mysql_zdp_main.dp_activitys_goods,id',
            ],
            [
                'id.required' => '数据ID必须有',
                'id.integer'  => '数据ID应该是一个整型',
                'id.exists'   => '数据ID已经被删除',
            ]
        );

        $reData = $activityGoodsService->delActivityGoods($request->input('id'));

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 活动商品清空
     *
     * @param Request              $request
     * @param ActivityGoodsService $activityGoodsService
     *
     * @return \Illuminate\Http\Response
     */
    public function clearActivityGoods(Request $request, ActivityGoodsService $activityGoodsService)
    {
        $this->validate(
            $request,
            [
                'activity_type_id' => 'required|integer|min:1',
            ],
            [
                'activity_type_id.required' => '活动类型id不能为空',
                'activity_type_id.integer'  => '活动类型id必须为一个整数',
                'activity_type_id.min'      => '活动类型id不能小于:min',
            ]
        );

        $reData = $activityGoodsService->clearActivityGoods($request->input('activity_type_id'));

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 活动商品排序
     *
     * @param Request              $request
     * @param ActivityGoodsService $activityGoodsService
     *
     * @return \Illuminate\Http\Response
     */
    public function sortActivityGoods(Request $request, ActivityGoodsService $activityGoodsService)
    {
        $this->validate(
            $request,
            [
                'current_id'  => 'required|integer|min:1|exists:mysql_zdp_main.dp_activitys_goods,id',
                'next_id'     => 'required|integer|min:0',
                'activity_id' => 'required|integer|exists:mysql_zdp_main.dp_activities,id',
            ],
            [
                'current_id.required' => '需调整的记录ID必须有',
                'current_id.integer'  => '需调整的记录ID应该是一个整型',
                'current_id.min'      => '需调整的记录ID不可小于:min',
                'current_id.exists'   => '需调整的记录不存在',

                'next_id.required' => '调整后下一记录ID必须有',
                'next_id.integer'  => '调整后下一记录ID应该是一个整型',
                'next_id.min'      => '调整后下一记录ID不可小于:min',

                'activity_id.required' => '活动ID必须有',
                'activity_id.integer'  => '活动ID应该是一个整型',
                'activity_id.exists'   => '活动id不存在',
            ]
        );

        $reData = $activityGoodsService->sortActivityGoods(
            $request->input('current_id'),
            $request->input('next_id'),
            $request->input('activity_id')
        );

        return $this->render(
            'activity.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}