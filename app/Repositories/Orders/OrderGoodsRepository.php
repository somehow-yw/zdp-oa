<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 11:09
 */

namespace App\Repositories\Orders;

use App\Models\DpShopInfo;
use Carbon\Carbon;
use App\Repositories\Orders\Contracts\OrderGoodsRepository as RepositoriesContract;
use DB;
use Zdp\Main\Data\Models\DpOpderForm;
use Zdp\Main\Data\Models\DpOrderCancelReason;
use Zdp\Main\Data\Models\DpOrderRefund;
use Zdp\Main\Data\Models\DpCartInfo;

class OrderGoodsRepository implements RepositoriesContract
{
    /**
     * 取得商品昨日销量
     *
     * @param int $goodsId 商品ID
     *
     * @see \App\Repositories\Orders\Contracts\OrderGoodsRepository::getYesterdaySales()
     *
     * @return int
     */
    public function getYesterdaySales($goodsId)
    {
        $todayStartDate = Carbon::now()->subDay(1)->startOfDay();
        $todayEndDate = Carbon::now()->subDay(1)->endOfDay();
        $statusArr = [
            DpCartInfo::ORDER_GOODS,
            DpCartInfo::CONFIRM_ORDER_GOODS,
            DpCartInfo::DELIVERY_ORDER_GOODS,
            DpCartInfo::TAKE_ORDER_GOODS,
            DpCartInfo::WITHDRAW_BEING_PROCESSED_ORDER,
            DpCartInfo::WITHDRAW_ACCOMPLISH_ORDER,
            DpCartInfo::HAVE_EVALUATION,
            DpCartInfo::DEPOSIT_BEING_PROCESSED_ORDER,
        ];
        $buyNumber = DpCartInfo::where('goodid', $goodsId)
            ->where(
                function ($query) use ($todayStartDate, $todayEndDate) {
                    $query->where('addtime', '>=', $todayStartDate)
                        ->where('addtime', '<=', $todayEndDate);
                }
            )
            ->whereIn('good_act', $statusArr)
            ->sum('buy_num');

        return $buyNumber;
    }

    /**
     * \App\Repositories\Orders\Contracts\OrderGoodsRepository::getList()
     */
    public function getList(
        $order_id,
        $order_num,
        $orderState,
        $buy_shop,
        $seller_shop,
        $buy_phone,
        $seller_phone,
        $page,
        $size
    ) {
        $result = DpOpderForm::query()
            ->orderBy('id', 'desc')
            ->join(
                'dp_shopInfo as a',
                'a.shopId',
                '=',
                'dp_opder_form.shopid'
            )
            ->join(
                'dp_shangHuInfo as b',
                'b.shId',
                '=',
                'dp_opder_form.uid'
            )
            ->join(
                'dp_shopInfo as c',
                'c.shopId',
                '=',
                'b.shopId'
            );

        if (!empty($seller_shop)) {
            $result = $result->where('a.dianPuName', 'like', '%' . $seller_shop . '%');
        }

        if (!empty($seller_phone)) {
            $result = $result->where('a.jieDanTel', $seller_phone);
        }

        if (!empty($buy_shop)) {
            $result = $result->where('c.dianPuName', 'like', '%' . $buy_shop . '%');
        }

        if (!empty($orderState)) {
            $result = $result->whereIn('orderact', $orderState);
        }

        if (!empty($order_id)) {
            $result = $result->where('id', $order_id);
        }

        if (!empty($order_num)) {
            $result = $result->where(function ($query) use ($order_num) {
                $query->where('order_code', $order_num)
                      ->orWhere('codenumber', $order_num);
            });
        }

        if (!empty($buy_phone)) {
            $result = $result->where('user_tel', $buy_phone);
        }

        $waitPay = [];
        $noPayType = true;
        $countNum = 0;
        if (empty($orderState) || $orderState == [
                DpOpderForm::NEW_ORDER,
                DpOpderForm::COMMUNICATE_ORDER,
                DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER
            ]) {
            $waitPayObj = clone $result;
            $waitPay = $waitPayObj->groupBy('dp_opder_form.codenumber')
                                  ->whereIn('dp_opder_form.orderact', [
                                      DpOpderForm::NEW_ORDER,
                                      DpOpderForm::COMMUNICATE_ORDER,
                                      DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER,
                                  ])
                                  ->paginate($size, [
                                      'dp_opder_form.id',
                                      'dp_opder_form.codenumber',
                                      'dp_opder_form.uid',
                                      'dp_opder_form.shopid',
                                      'dp_opder_form.order_code',
                                      'dp_opder_form.good_num',
                                      'dp_opder_form.good_count',
                                      'dp_opder_form.total_price',
                                      'dp_opder_form.addtime',
                                      'dp_opder_form.buy_realpay',
                                      'dp_opder_form.addtime',
                                      'dp_opder_form.user_tel',
                                      'dp_opder_form.orderact',
                                      'a.shopId',
                                      'a.dianPuName',
                                      'a.jieDanTel',
                                      'c.dianPuName as buyShopName',
                                      DB::raw('sum(dp_opder_form.good_count) as good_count'),
                                      DB::raw('sum(dp_opder_form.buy_realpay) as buy_realpay'),
                                  ], null, $page);

            $noPayNum = $waitPay->total();
            $countNum += $noPayNum;
            $noPayPage = ceil($noPayNum / $size);
            if ($page < $noPayPage) {
                $noPayType = false;
            } elseif ($page == $noPayPage) {
                $size = $size - ($noPayNum % $size);
                $page = 1;
            } elseif ($page > $noPayPage) {
                $waitPay = [];
                $page = $page - $noPayPage;
            }
        }


        $result = $result->whereNotIn('dp_opder_form.orderact', [
            DpOpderForm::NEW_ORDER,
            DpOpderForm::COMMUNICATE_ORDER,
            DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER,
        ])
            ->paginate($size, [
                'dp_opder_form.id',
                'dp_opder_form.codenumber',
                'dp_opder_form.uid',
                'dp_opder_form.shopid',
                'dp_opder_form.order_code',
                'dp_opder_form.good_num',
                'dp_opder_form.good_count',
                'dp_opder_form.total_price',
                'dp_opder_form.addtime',
                'dp_opder_form.buy_realpay',
                'dp_opder_form.addtime',
                'dp_opder_form.user_tel',
                'dp_opder_form.orderact',
                'a.shopId',
                'a.dianPuName',
                'a.jieDanTel',
                'c.dianPuName as buyShopName',
            ], null, $page);

        $countNum += $result->total();
        if (!$noPayType) {
            $result = [];
        }
        return [
            'data'       => $result,
            'waitPay'    => $waitPay,
            'countNum'   => $countNum
        ];
    }

    /**
     * \App\Repositories\Orders\Contracts\OrderGoodsRepository::getDetail()
     */
    public function getDetail($order_num)
    {
        $result = DpOpderForm::query()
                             ->with([
                                 'orderLog'   => function ($query) {
                                     $query->with(['orderOperation.aboutOrder'])
                                           ->orderBy('id', 'desc');
                                 },
                                 'orderGoods' => function ($query) {
                                     $query->with('goods')
                                           ->whereNotIn('good_act', [
                                               DpCartInfo::REFUND_ORDER_GOODS,
                                               DpCartInfo::REFUND_GOODS,
                                               DpCartInfo::INVALID_ORDER_GOODS,
                                           ]);
                                 },
                                 'user.shop',
                                 'shop'       => function ($query) {
                                     $query->with(['market', 'boss']);
                                 },
                             ])
                             ->where('order_code', $order_num)
                             ->orWhere('codenumber', $order_num)
                             ->get();

        return $result;
    }

    /**
     * \App\Repositories\Orders\Contracts\OrderGoodsRepository::getFinanceRefund()
     */
    public function getFinanceRefund($order_num)
    {
        $result = DpOrderRefund::query()
            ->where('sub_order_no', $order_num)
            ->orderBy('id', 'desc')
            ->first();
        return $result;
    }

    /**
     * \App\Repositories\Orders\Contracts\OrderGoodsRepository::getRefundList()
     */
    public function getRefundList(
        $order_id,
        $order_num,
        $orderState,
        $buy_shop,
        $seller_shop,
        $buy_phone,
        $seller_phone,
        $page,
        $size
    ) {
        $result = DpOrderRefund::query()
                               ->with([
                                   'order' => function ($query) use (
                                       $buy_shop,
                                       $seller_shop,
                                       $buy_phone,
                                       $seller_phone
                                   ) {
                                       if (!empty($buy_phone)) {
                                           $query->where('user_tel', $buy_phone);
                                       }
                                       $query->with([
                                           'shop.boss'      => function ($query) use
                                           (
                                               $seller_shop,
                                               $seller_phone
                                           ) {
                                               if (!empty($seller_shop)) {
                                                   $query->where('dianPuName', 'like', '%' . $seller_shop . '%');
                                               }

                                               if (!empty($seller_phone)) {
                                                   $query->where('jieDanTel', $seller_phone);
                                               }
                                           },
                                           'user.shop' => function ($query) use
                                           (
                                               $buy_shop
                                           ) {
                                               if (!empty($buy_shop)) {
                                                   $query->where('dianPuName', 'like', '%' . $buy_shop . '%');
                                               }
                                           },
                                       ]);
                                   },
                               ]);
        if (!empty($order_num)) {
            $result = $result->where('sub_order_no', $order_num);
        }
        if (!empty($order_id)) {
            $result = $result->where('sub_order_id', $order_id);
        }
        if (!empty($orderState)) {
            $result = $result->where('status', $orderState);
        }

        $result = $result->orderBy('id', 'desc')
                         ->paginate($size, ['*'], null, $page);

        return $result;
    }

    /**
     * @see \App\Repositories\Orders\Contracts\OrderGoodsRepository::getReasonList()
     */
    public function getReasonList($type)
    {
        $result = DpOrderCancelReason::query()
                                     ->where('reason_status', $type)
                                     ->get();

        return $result;
    }
}