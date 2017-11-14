<?php

namespace App\Repositories\Shops;

use App\Models\DpAppraiseDisposeLog;
use App\Models\DpAppraiseImgs;
use App\Models\DpCartInfo;
use App\Models\DpGoodsAppraises;
use App\Models\DpGoodsInfo;
use App\Models\DpOpderForm;
use App\Models\DpServiceAppraises;
use App\Models\DpShopInfo;
use App\Repositories\Shops\Contracts\AppraiseRepository as RepositoriesContract;
use Carbon\Carbon;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Auth;

class AppraiseRepository implements RepositoriesContract
{
    public function getList(
        $shop_name,
        $goods_name,
        $orderIds,
        $start_time,
        $end_time,
        $size,
        $page
    ) {
        $total = DpOpderForm::query()->count();


        $results = DpOpderForm
            ::query()
            ->leftJoin(
                'dp_shopInfo as a',
                'a.shopId',
                '=',
                'dp_opder_form.shopid'
            )
            ->leftJoin(
                'dp_cart_info as b',
                'b.coid',
                '=',
                'dp_opder_form.order_code'
            )
            ->leftJoin(
                'dp_goods_info as c',
                'c.id',
                '=',
                'b.goodid'
            )
            ->with(['goodsAppraise', 'appraiseLog'])
            ->where(
                function ($query) use ($shop_name) {
                    if (!empty($shop_name)) {
                        $query->where(
                            'a.dianPuName',
                            'like',
                            '%' . $shop_name . '%'
                        );
                    }
                }
            )
            ->where(
                function ($query) use ($goods_name) {
                    if (!empty($goods_name)) {
                        $query->where(
                            'c.gname',
                            'like',
                            '%' . $goods_name . '%'
                        );
                    }
                }
            )
            ->where(
                function ($query) use ($orderIds) {
                    if (!empty($orderIds)) {
                        $query->where(
                            'dp_opder_form.order_code',
                            $orderIds
                        );
                    }
                }
            )
            ->where(
                function ($query) use ($start_time) {
                    if (!empty($start_time)) {
                        $query->where(
                            'dp_opder_form.addtime',
                            '>',
                            $start_time
                        );
                    }
                }
            )
            ->where(
                function ($query) use ($end_time) {
                    if (!empty($end_time)) {
                        $query->where(
                            'dp_opder_form.addtime',
                            '<',
                            $end_time
                        );
                    }
                }
            )
            ->whereIn('dp_opder_form.orderact', [
                DpOpderForm::TAKE_ORDER,
                DpOpderForm::WITHDRAW_BEING_PROCESSED_ORDER,
                DpOpderForm::WITHDRAW_ACCOMPLISH_ORDER,
                DpOpderForm::HAVE_EVALUATION
            ])
            ->groupBy('dp_opder_form.id')
            ->orderBy('dp_opder_form.id', 'DESC')
            ->forPage($page, $size)
            ->get([
                'dp_opder_form.order_code',
                'dp_opder_form.good_num',
                'dp_opder_form.orderact',
                'dp_opder_form.addtime',
                'dp_opder_form.good_num',
                'a.shopId',
                'a.dianPuName',
                'b.goodid',
                'c.id',
                'c.gname',
            ]);

        return new LengthAwarePaginator($results, $total, $size, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => null,
        ]);
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function getAppraiseStatus($goodsIdToArray)
    {

        return DpGoodsAppraises::query()
                               ->whereIn('sub_order_no', $goodsIdToArray)
                               ->get();
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function getAppraiseStatusLog($appraiseId)
    {
        return DpAppraiseDisposeLog::query()
                                   ->whereIn('sub_order_no', $appraiseId)
                                   ->get();
    }

    /**
     * 评价数据统计（店铺）
     *
     * @param Contracts\data $startTime
     * @param Contracts\data $endTime
     * @param string         $province
     * @param string         $city
     * @param string         $district
     * @param string         $seek
     * @param string         $seekVal
     * @param integer        $pageSize
     * @param integer        $pageNum
     * @param integer        $type
     * @param integer        $sortType
     * @param string         $sortTypeWay
     *
     * @return mixed
     */
    public function appraiseShopInfo(
        $startTime,
        $endTime,
        $province,
        $city,
        $district,
        $seek,
        $seekVal,
        $pageSize,
        $pageNum,
        $type,
        $sortType,
        $sortTypeWay
    ) {
        $query = DpShopInfo::query();

        //判断商家还是买家
        if ($type == DpShopInfo::SUPPLY_SHOP) {
            $query->whereIn('dp_shopInfo.trenchnum', DpShopInfo::$supplyShop);
        } elseif ($type == DpShopInfo::PURCHASE_SHOP) {
            $query->whereIn('dp_shopInfo.trenchnum', DpShopInfo::$purchaseShop);
        }

        //添加地区过滤
        if (!empty($province)) {
            $query = $query->where('dp_shopInfo.province', $province);
        }
        if (!empty($city)) {
            $query = $query->where('dp_shopInfo.city', $city);
        }
        if (!empty($district)) {
            $query = $query->where('dp_shopInfo.county', $district);
        }
        if (!empty($seekVal) && $seek == 'dianPuName') {
            $query->where($seek, $seekVal);
        }
        $total = $query->count('dp_shopinfo.shopId');
        $query->with([
            'serAppraise' => function ($query) use ($startTime, $endTime) {
                $query->where('created_at', '>=', $startTime)
                    ->where('created_at', '<=', $endTime)
                    ->groupBy('sell_shop_id')
                    ->select(
                        DB::raw('sum(delivery_speed)/count(delivery_speed) as deliverySpeed'),
                        DB::raw('sum(service_platform)/count(service_platform) as servicePlatform'),
                        'sell_shop_id'
                    );
            }
        ])
            ->leftjoin('dp_goods_appraises as g', function ($join) use ($startTime, $endTime) {
                $join->on('dp_shopInfo.shopId', '=', 'g.sell_shop_id')
                    ->where('g.created_at', '>=', $startTime)
                    ->where('g.created_at', '<=', $endTime);
            })
            ->leftjoin('dp_opder_form as d', function ($join) use ($startTime, $endTime) {
                $join->on('dp_shopInfo.shopId', '=', 'd.shopid')
                    ->where('d.method_datetime', '>=', $startTime)
                    ->where('d.method_datetime', '<=', $endTime);
            })
            ->groupBy('dp_shopInfo.shopId')
            ->select(
                'dp_shopInfo.date as ZhuCe',
                'dp_shopInfo.shopId',
                'dp_shopInfo.dianPuName as name',
                DB::raw(
                    'count(g.sell_shop_id) as appraiseNum'
                ),
//                DB::raw(
//                    'count(gd.sell_shop_id)/count(g.sell_shop_id) as goodAppraiseNum'
//                ),
                DB::raw(
                    'sum(d.total_price) as all_price'
                )
            );

        if ($sortType == DpShopInfo::ZHUCETIME) {
            $query->orderBy('dp_shopInfo.date', $sortTypeWay);
        } elseif ($sortType == DpShopInfo::JIAOYIMONEY) {
            $query->orderBy('all_price', $sortTypeWay);
        } elseif ($sortType == DpShopInfo::APPRAISENUM) {
            $query->orderBy('appraiseNum', $sortTypeWay);
        } elseif ($sortType == DpShopInfo::GOODAPPRAISE) {
            return DpShopInfo::query()
                ->with([
                    'serAppraise' => function ($query) use ($startTime, $endTime) {
                        $query->where('created_at', '>=', $startTime)
                            ->where('created_at', '<=', $endTime)
                            ->groupBy('sell_shop_id')
                            ->select(
                                DB::raw('sum(delivery_speed)/count(delivery_speed) as deliverySpeed'),
                                DB::raw('sum(service_platform)/count(service_platform) as servicePlatform'),
                                'sell_shop_id'
                            );
                    }
                ])
                ->from(DB::raw("({$query->toSql()}) as t"))
                ->mergeBindings($query->getQuery())
                ->where('t.appraiseNum', '>=', 100)
                ->orderBy('goodAppraise', $sortTypeWay)
                ->paginate($pageSize, ['*'], null, $pageNum)
                ->toArray();
        }
        $query->forPage($pageNum, $pageSize);
        $re = new LengthAwarePaginator($query->get(), $total, $pageSize, $pageNum);
        return $re;
    }

    /**平台所有服务统计
     * @return array
     */
    public function meanAppraise()
    {
        $allOrderCount = DpOpderForm::query()->count('id');
        $hasAppraiseCount = DpOpderForm::query()->where('has_appraise', 1)->count('id');
        $appraiseOrderRatio = round($hasAppraiseCount / $allOrderCount, 4) * 100 . '%';

        $allAppraiseNum = DpGoodsAppraises::query()->count('id');
        $goodAppraiseNum = DpGoodsAppraises::query()->whereIn('quality', DpGoodsAppraises::$goodAppraiseArr)->count('id');
        $mediumAppraiseNum = DpGoodsAppraises::query()->whereIn('quality', DpGoodsAppraises::$mediumAppraiseArr)->count('id');
        $poorAppraiseNum = DpGoodsAppraises::query()->whereIn('quality', DpGoodsAppraises::$poorAppraiseArr)->count('id');
        $allGoodAppraiseRatio = round($goodAppraiseNum / $allAppraiseNum, 4) * 100 . '%';
        $allMediumAppraiseRatio = round($mediumAppraiseNum / $allAppraiseNum, 4) * 100 . '%';
        $allPoorAppraiseRatio = round($poorAppraiseNum / $allAppraiseNum, 4) * 100 . '%';
        $hasImgAppraise = DpGoodsAppraises::query()->where('hasImg', DpGoodsAppraises::IMG_APPRAISE)->count('id');
        //发货速度
        $allDeliverySpeed = round(
            DpServiceAppraises::query()->sum('delivery_speed')
            /
            DpServiceAppraises::query()->count('delivery_speed'),
            2
        );

        //服务星级
        $allSellService = round(
            DpServiceAppraises::query()->sum('sell_service')
            /
            DpServiceAppraises::query()->count('sell_service'),
            2
        );

        return [
            'hasAppraiseCount' => $hasAppraiseCount,
            'appraiseOrderRatio' => $appraiseOrderRatio,
            'allGoodAppraiseRatio' => $allGoodAppraiseRatio,
            'allMediumAppraiseRatio' => $allMediumAppraiseRatio,
            'allPoorAppraiseRatio' => $allPoorAppraiseRatio,
            'hasImgAppraise' => $hasImgAppraise,
            'allDeliverySpeed' => $allDeliverySpeed,
            'allSellService' => $allSellService,
        ];
    }

    /***
     * 评价数据统计（货物）
     *
     * @param data    $startTime
     * @param data    $endTime
     * @param string  $province
     * @param string  $city
     * @param string  $district
     * @param string  $seekVal
     * @param integer $pageSize
     * @param integer $pageNum
     * @param integer $sortType
     * @param string  $sortTypeWay
     *
     * @return mixed
     */
    public function appraiseGoodsInfo(
        $startTime,
        $endTime,
        $province,
        $city,
        $district,
        $seekVal,
        $pageSize,
        $pageNum,
        $sortType,
        $sortTypeWay
    ) {
        $query = DpGoodsInfo::query();
        $query->whereIn('shenghe_act', DpGoodsInfo::$statistics)
              ->where('gname', 'like', "%$seekVal%");
        if (!empty($province)) {
            $query = $query->where('province', $province);
        }
        if (!empty($city)) {
            $query = $query->where('city', $city);
        }
        if (!empty($district)) {
            $query = $query->where('county', $district);
        }
        $total = $query->count('id');
        $query->leftjoin('dp_goods_basic_attributes as m', 'dp_goods_info.id',
            '=', 'm.goodsid')
              ->leftjoin(
                  'dp_shopInfo as s',
                  'dp_goods_info.shopid',
                  '=',
                  's.shopId'
              )
              ->leftjoin('dp_goods_appraises as a',
                  function ($join) use ($startTime, $endTime) {
                      $join->on('dp_goods_info.id', '=', 'a.goods_id')
                           ->where('a.created_at', '>=', $startTime)
                           ->where('a.created_at', '<=', $endTime);
                  })
              ->leftjoin('dp_goods_appraises as ga',
                  function ($join) use ($startTime, $endTime) {
                      $join->on('dp_goods_info.id', '=', 'ga.goods_id')
                           ->where('ga.quality', '=', DpGoodsAppraises::FIVE)
                           ->where('ga.created_at', '>=', $startTime)
                           ->where('ga.created_at', '<=', $endTime);
                  })
              ->groupBy('dp_goods_info.id')
              ->select(
                  'dp_goods_info.gname',
                  'dp_goods_info.addtime',
                  'dp_goods_info.id',
                  'dp_goods_info.shopid',
                  's.dianPuName',
                  'm.goods_price',
                  DB::raw('count(a.goods_id) as appraiseNum'),
                  DB::raw('count(ga.goods_id)/count(a.goods_id)*100 as appraiseGood')
              );
        if ($sortType == DpShopInfo::GOODAPPRAISE) {
            $query->orderBy('appraiseGood', $sortTypeWay);

            return DB::connection('mysql_zdp_main')
                ->table(DB::raw("({$query->toSql()}) as t"))
                ->mergeBindings($query->getQuery())
                ->where('t.appraiseNum', '>=', 100)
                ->paginate($pageSize, ['*'], null, $pageNum)
                ->toArray();
        } elseif ($sortType == DpShopInfo::APPRAISENUM) {
            $query->orderBy('appraiseNum', $sortTypeWay);
        }
        $query->forPage($pageNum, $pageSize);
        $re = new LengthAwarePaginator($query->get(), $total, $pageSize,
            $pageNum);

        return $re->toArray();
    }

    /*
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function getAppraiseDetails($orderIds)
    {
        return DpOpderForm::query()
                          ->with('orderGoods')
                          ->with('orderGoods.goods')
                          ->with('orderAppraise')
                          ->with('goodsAppraise.appraiseImg')
                          ->where('order_code', $orderIds)
                          ->first();
    }


    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function getAppraiseLog($orderIds)
    {
        return DpAppraiseDisposeLog::query()
                                   ->where('sub_order_no', $orderIds)
                                   ->orderBy("id", 'desc')
                                   ->get();
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function updateAppraiseLog($orderIds, $resObj, $remark, $status)
    {
        //获取登录用户的信息
        $user = Auth::user();
        $admin_id = $user->id;
        $admin_name = $user->user_name;
        /*$admin_id = '111';
        $admin_name = '11123';*/

        //根据订单号获取评价信息
        $appraiseData = DpGoodsAppraises::query()
                                        ->where("sub_order_no", $orderIds)
                                        ->withTrashed()
                                        ->first()
                                        ->toArray();
        //根据订单号获取订单商品信息
        $goodsData = DpCartInfo::query()
                               ->where('coid', $orderIds)
                               ->first()
                               ->toArray();

        $resObj = \GuzzleHttp\json_encode($resObj);
        $createData = [
            'appraise_id'    => $appraiseData['id'],
            'sell_shop_id'   => $appraiseData['sell_shop_id'],
            'goods_id'       => $goodsData['goodid'],
            'order_goods_id' => $goodsData['id'],
            'sub_order_no'   => $orderIds,
            'admin_id'       => $admin_id,
            'admin_name'     => $admin_name,
            'content'        => $resObj,
            'status'         => $status,
            'remark'         => $remark,
        ];

        DpAppraiseDisposeLog::create($createData);
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function deleteGoodsAppraise($orderIds)
    {
        $res = DpGoodsAppraises::query()
                               ->where("sub_order_no", $orderIds)
                               ->first();
        if (!empty($res)) {
            DpGoodsAppraises::query()
                            ->where("sub_order_no", $orderIds)
                            ->delete();
        }
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function deleteShopAppraise($orderIds)
    {
        $res = DpServiceAppraises::query()
                                 ->where("sub_order_no", $orderIds)
                                 ->first();
        if (!empty($res)) {
            DpServiceAppraises::query()
                              ->where("sub_order_no", $orderIds)
                              ->delete();
        }
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function updateShopAppraise($subOrderNo, $shopAppraises)
    {

        $updateData = [
            'sell_service'   => $shopAppraises['sell_service'],
            'delivery_speed' => $shopAppraises['delivery_speed'],
        ];

        DpServiceAppraises::query()
                          ->where("sub_order_no", $subOrderNo)
                          ->update($updateData);
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function updateGoodsAppraise($appraiseId, $quality, $content)
    {
        $updateData = [
            'quality' => $quality,
            'content' => $content,
        ];

        return DpGoodsAppraises::query()
                               ->where("id", $appraiseId)
                               ->update($updateData);
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function updateGoodsAppraisePic($appraiseId, $imgUrl, $type)
    {
        $updateData = [
            'appraise_id' => $appraiseId,
            'img_url'     => $imgUrl,
            'type'        => $type,
        ];

        DpAppraiseImgs::create($updateData);
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function deleteGoodsAppraisePic($appraiseId)
    {
        DpAppraiseImgs::query()
                      ->where("appraise_id", $appraiseId)
                      ->delete();
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function resetGoodsAppraise($orderIds)
    {
        $res = DpGoodsAppraises::query()
                               ->where("sub_order_no", $orderIds)
                               ->first();
        if (!empty($res)) {
            $time = Carbon::now();
            $createData = [
                'reseted_at' => $time,
            ];
            // 更新重置时间
            DpGoodsAppraises::query()
                            ->where("sub_order_no", $orderIds)
                            ->update($createData);

            DpGoodsAppraises::query()
                            ->where("sub_order_no", $orderIds)
                            ->delete();
        }
    }

    /**
     * @see \App\Repositories\Shops\Contracts\AppraiseRepository
     */
    public function resetShopAppraise($orderIds)
    {
        $res = DpServiceAppraises::query()
                                 ->where("sub_order_no", $orderIds)
                                 ->first();
        if (!empty($res)) {
            $time = Carbon::now();
            $createData = [
                'reseted_at' => $time,
            ];
            // 更新重置时间
            DpServiceAppraises::query()
                              ->where("sub_order_no", $orderIds)
                              ->update($createData);
            DpServiceAppraises::query()
                              ->where("sub_order_no", $orderIds)
                              ->delete();
        }
    }
}