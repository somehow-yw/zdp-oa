<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/19 0019
 * Time: 上午 9:27
 */

namespace App\Workflows;

use Illuminate\Support\Facades\Auth;
use Zdp\Main\Data\Models\DpCartInfo;
use Zdp\Main\Data\Models\DpOpderForm;
use Zdp\Main\Data\Models\DpOrderCancelReason;
use Zdp\Main\Data\Models\DpOrderLogistics;
use Zdp\Main\Data\Models\DpOrderOperation;
use Zdp\Main\Data\Models\DpOrderOperationMoneyLog;
use Zdp\Main\Data\Models\DpOrderRefund;
use Zdp\Main\Data\Models\DpOrderRefundDetailGoodsLog;
use Zdp\Main\Data\Models\DpOrderSnapshot;

use App\Utils\MoneyUnitConvertUtil;
use App\Services\Order\OrderService;

use Cache;
use Carbon\Carbon;
use DB;
use Zdp\Main\Data\Models\DpShangHuInfo;
use Zdp\Main\Data\Models\DpShopInfo;


class OrderWorkflow
{
    /**
     * @var OrderService
     */
    private $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    /**
     * 买家取消订单
     * @param $order_num string 订单号
     * @param $id int 取消理由的ID
     * @param $reason string 取消理由的补充说明
     *
     * @return array
     * @throws \Exception
     */
    public function buyerCancel($order_num, $id, $reason)
    {
        // 用大订单号查出所有订单
        $orderInfo = DpOpderForm::query()
                                ->where('codenumber', $order_num)
                                ->get();

        if ($orderInfo[0]->orderact !== DpOpderForm::NEW_ORDER) {
            throw new \Exception('当前订单不能取消');
        }

        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $orderInfo,
            $id,
            $reason
        ) {
            // 多个子订单设计涉及到的订单商品表信息系统调整
            foreach ($orderInfo as $k => $cartOrder) {
                // 写入订单日志表
                $this->service->updateOrderLog(
                    $cartOrder->order_code,
                    DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL,
                    DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL]
                );
                // 更改订单商品表信息
                DpCartInfo::query()
                          ->where('coid', $cartOrder->order_code)
                          ->update([
                              "good_act" => DpCartInfo::DEL_ORDER_GOODS,
                          ]);
            }
            // 更改订单信息
            DpOpderForm::query()
                       ->where('codenumber', $order_num)
                       ->update(
                           [
                               'reason_id' => DpOrderCancelReason::where('id', $id)->value('content'),
                               'reason'    => $reason,
                               'orderact'  => DpOpderForm::DEL_ORDER,
                           ]
                       );
        });

        return [
            'code'    => 0,
            'data'    => [],
            'message' => 'OK',
        ];
    }

    /**
     * 卖家确认发货
     * @param $order_num string 订单号
     * @param $driver_tel string 司机电话
     * @param $car_num string 车牌号
     *
     * @return array
     * @throws \Exception
     */
    public function sellerShipments($order_num, $driver_tel, $car_num)
    {
        $orderStatus = DpOrderSnapshot::ORDER_FLOW_STATUS_DELIVER_GOODS;
        $remark = DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_DELIVER_GOODS];

        $result = DpOpderForm::query()
                             ->with(['orderGoods','user'])
                             ->where('order_code', $order_num)
                             ->first();

        if ($result->orderact !== DpOpderForm::CONFIRM_ORDER) {
            throw new \Exception('订单不符合发货条件');
        }

        DB::connection('mysql_zdp_main')->transaction(function () use (
            $result,
            $order_num,
            $driver_tel,
            $car_num,
            $orderStatus,
            $remark
        ) {
            // 将操作写入流程日志
            $this->service->updateOrderLog($order_num, $orderStatus, $remark);
            // 确认收货导致订单数据变化
            if ($result->delivery == DpOpderForm::ORDER_LOGISTICS_TAKE_GOODS || empty($driver_tel) || empty($car_num)) {
                // 买家找车或者信息不完整，只更新状态
                // 更新订单信息
                $updateDpOpderFormData = [
                    'orderact'       => DpOpderForm::DELIVERY_ORDER,
                    'order_clear'    => Carbon::now()->format('Y-m-d H:i:s'),
                ];
                DpOpderForm::query()
                           ->where('order_code', $order_num)
                           ->update($updateDpOpderFormData);
                // 更新订单商品信息
                $updateData = [
                    'good_act'       => DpCartInfo::DELIVERY_ORDER_GOODS
                ];
                DpCartInfo::query()
                          ->where('coid', $order_num)
                          ->update($updateData);
            } else {
                // 卖家找车
                // 更新订单信息
                $updateDpOpderFormData = [
                    'orderact'       => DpOpderForm::DELIVERY_ORDER,
                    'license_plates' => $car_num,
                    'driver_tel'     => $driver_tel,
                    'order_clear'    => Carbon::now()->format('Y-m-d H:i:s'),
                ];
                DpOpderForm::query()
                           ->where('order_code', $order_num)
                           ->update($updateDpOpderFormData);
                // 更新订单商品信息
                $updateData = [
                    'good_act'       => DpCartInfo::DELIVERY_ORDER_GOODS,
                    'license_plates' => $car_num,
                    'driver_tel'     => $driver_tel,
                ];
                DpCartInfo::query()
                          ->where('coid', $order_num)
                          ->update($updateData);
                // 更新物流信息
                $orderGoodsArr = $result->orderGoods->pluck('id')
                                                    ->all();
                $orderGoodsStr = implode(',', $orderGoodsArr);
                $logisticsCreate = [
                    'codenumber'     => $result->codenumber,
                    'order_code'     => $result->order_code,
                    'order_goods_id' => $orderGoodsStr,
                    'driver_tel'     => $driver_tel,
                    'license_plates' => $car_num,
                ];
                DpOrderLogistics::create($logisticsCreate);
            }

            // 发送模板消息
            $templateData = [
                'first'    => ['value' => '您有订单，卖家已发货，请及时联系司机收货。', 'color' => '#173177'],
                'keyword1' => ['value' => '待收货', 'color' => '#173177'],
                'keyword2' => ['value' => Carbon::now()->format('Y-m-d H:i:s'), 'color' => '#173177'],
                'remark'   => ['value' => '检查货品无误后请确认收货。点击详情，查看订单信息。', 'color' => '#173177'],
            ];
            $this->sendWechatMsg(
                $result->user->OpenID,
                $templateData,
                'OPENTM411627058',
                config('groupon.wechat.url.buyerOrderDetail').$result->order_code
            );
        });

        return [
            'code'    => 0,
            'data'    => [],
            'message' => 'OK',
        ];
    }

    /**
     * 申请-卖家取消/买家退款/买家退货
     *
     * @param $order_num         string 订单号
     * @param $type              int 申请类型：1：卖家取消；2：买家退款；3：买家退货
     * @param $cancel_id         int 取消原因ID
     * @param $cancel_reason     int 取消原因ID
     * @param $inform_buyer      int 是否通知买家
     * @param $inform_seller     int 是否通知卖家
     * @param $buyer_cancel_info array 取消的商品信息
     * @param $ip                string IP
     *
     * @return array
     * @throws \Exception
     */
    public function saleApply(
        $order_num,
        $type,
        $cancel_id,
        $cancel_reason,
        $inform_buyer,
        $inform_seller,
        $buyer_cancel_info,
        $ip
    ) {
        $result = DpOpderForm::query()
                             ->with(['orderGoods.goods', 'user', 'shop.user'])
                             ->where('order_code', $order_num)
                             ->first();

        if (!in_array($result->orderact, [
            DpOpderForm::CONFIRM_ORDER,
            DpOpderForm::DELIVERY_ORDER,
        ])) {
            throw new \Exception('当前订单不可进行申请操作');
        }

        // 取消订单种类数量
        $kindNum = 0;
        // 取消订单商品个数总量
        $num = 0;
        // 退款商品总价
        $totalPrice = 0;
        // 实际退款金额
        $refundPrice = 0;
        foreach ($result->orderGoods as $key => $value) {
            foreach ($buyer_cancel_info as $key1 => $item) {
                if ($value->id == $item['id'] && $item['cancel_num'] > 0) {
                    $kindNum++;
                    $num += $item['cancel_num'];
                    if ($item['cancel_num'] > $value['buy_num']) {
                        $sayNum = $key1 + 1;
                        throw new \Exception("第{$sayNum}取消订单的商品数不能大于购买订单的商品数");
                    }

                    // 一种商品实际支付的金额
                    $goodsPay = MoneyUnitConvertUtil::yuanToFen($value->count_price);
                    // 退款金额的计算
                    $refundPrice += intval($item['cancel_num']) * ($goodsPay / $value->buy_num);
                    $totalPrice += MoneyUnitConvertUtil::yuanToFen($value->good_new_price) *
                                   $item['cancel_num'];
                    $buyer_cancel_info[$key1]['buy_num'] = $value->buy_num;
                    $buyer_cancel_info[$key1]['refund_price'] =
                        $value->good_new_price;
                }
            }
        }


        if ($kindNum > $result->good_num) {
            throw new \Exception('取消订单的商品种数不能大于购买订单的商品种数');
        }
        if ($num > $result->good_count) {
            throw new \Exception('取消订单的商品总数不能大于购买订单的商品总数');
        }

        // 获取需要的状态
        $useState = $this->service->getSaleApplyState($result, $type, $kindNum, $num);
        $remark = $useState['remark'];
        $orderStatus = $useState['orderStatus'];
        $orderact = $useState['orderact'];
        $good_act = $useState['good_act'];

        $data = [
            'order_code'              => $result->order_code,
            'order_status'            => $orderStatus,
            'presenter'               => DpOrderOperation::SELLER,
            'uid'                     => $result->uid,
            'license_plates'          => '',
            'vehicle_location'        => '',
            'driver_tel'              => '',
            'arrive_time'             => '',
            'shop_id'                 => $result->shopid,
            'good_num'                => $kindNum,
            'good_count'              => $num,
            'reduced_price_increment' => MoneyUnitConvertUtil::fenToYuan(0),
            'real_refund'             => MoneyUnitConvertUtil::fenToYuan($refundPrice),
            'refund'                  => MoneyUnitConvertUtil::fenToYuan($refundPrice),
            'reason'                  => DpOrderCancelReason::where('id', $cancel_id)->value('content'),
            'reason_info'             => '',
            'refuse_reason'           => '',
            'form_ip'                 => $ip,
        ];

        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $data,
            $orderStatus,
            $remark,
            $buyer_cancel_info,
            $inform_buyer,
            $inform_seller,
            $result,
            $orderact,
            $good_act,
            $type
        ) {
            // 更新退款退货表的信息
            $this->refundAndReturnUpdate($data, $orderStatus, $remark, $buyer_cancel_info);

            // 更新订单和订单商品的状态
            $this->service->refundUpdateOrder($order_num, $orderact, $good_act);

            // 是否通知
            switch ($type) {
                // 卖家取消
                case 1:
                    if (!empty($inform_buyer)) {
                        // 发送模板消息
                        $cancelType = '部分取消';
                        if ($orderStatus == DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER) {
                            $cancelType = '全部取消';
                        }
                        $templateData = [
                            'first'    => ['value' => '您有订单，卖家申请取消。', 'color' => '#173177'],
                            'keyword1' => ['value' => '待发货', 'color' => '#173177'],
                            'keyword2' => ['value' => $cancelType, 'color' => '#173177'],
                            'keyword3' => ['value' => $data['reason'], 'color' => '#173177'],
                            'remark'   => ['value' => '点击详情，查看订单并处理', 'color' => '#173177'],
                        ];
                        $this->sendWechatMsg(
                            $result->user->OpenID,
                            $templateData,
                            'OPENTM412117776',
                            config('groupon.wechat.url.buyerOrderDetail').$result->order_code
                        );
                    }
                    break;
                // 买家退款
                case 2:
                    if (!empty($inform_seller)) {
                        $cancelType = '部分退款';
                        if ($orderStatus == DpOrderOperation::ORDER_FLOW_STATUS_REFUND_ALL) {
                            $cancelType = '全部退款';
                        }
                        $data = [
                            'first'    => ['value' => '有买家申请售后。', 'color' => '#173177'],
                            'keyword1' => ['value' => $cancelType, 'color' => '#173177'],
                            'keyword2' => ['value' => $result->orderGoods[0]->goods->gname . '等', 'color' => '#173177'],
                            'keyword3' => ['value' => $result->order_code, 'color' => '#173177'],
                            'keyword4' => ['value' => Carbon::now()->format('Y-m-d H:i:s'), 'color' => '#173177'],
                            'remark'   => ['value' => '点击详情查看订单详情', 'color' => '#173177']
                        ];
                        $shopUserOpenId = [];
                        foreach ($result->shop->user as $shopUser) {
                            $shopUserOpenId[] = $shopUser->OpenID;
                        }
                        $this->sendWechatMsg(
                            $shopUserOpenId,
                            $data,
                            'OPENTM401701827',
                            config('groupon.wechat.url.afterSale').$result->order_code
                        );
                    }
                    break;
                // 买家退货
                case 3:
                    if (!empty($inform_seller)) {
                        $cancelType = '部分退货';
                        if ($orderStatus == DpOrderOperation::ORDER_FLOW_STATUS_RETURN_ALL) {
                            $cancelType = '全部退货';
                        }
                        $data = [
                            'first'    => ['value' => '有买家申请售后。', 'color' => '#173177'],
                            'keyword1' => ['value' => $cancelType, 'color' => '#173177'],
                            'keyword2' => ['value' => $result->orderGoods[0]->goods->gname . '等', 'color' => '#173177'],
                            'keyword3' => ['value' => $result->order_code, 'color' => '#173177'],
                            'keyword4' => ['value' => Carbon::now()->format('Y-m-d H:i:s'), 'color' => '#173177'],
                            'remark'   => ['value' => '点击详情查看订单详情', 'color' => '#173177']
                        ];
                        $shopUserOpenId = [];
                        foreach ($result->shop->user as $shopUser) {
                            $shopUserOpenId[] = $shopUser->OpenID;
                        }
                        $this->sendWechatMsg(
                            $shopUserOpenId,
                            $data,
                            'OPENTM401701827',
                            config('groupon.wechat.url.afterSale').$result->order_code
                        );
                    }
                    break;
            }
        });

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [],
        ];
    }

    /**
     * 取消申请-卖家取消/买家退款/买家退货
     * @param $order_num string 子订单号
     * @param $type int 申请类型：1：卖家取消；2：买家退款；3：买家退货
     * @param $ip string 操作人的ip
     *
     * @return array
     * @throws \Exception
     */
    public function cancelApply($order_num, $type, $ip)
    {
        $result = DpOrderSnapshot::query()
                                 ->with('orderOperation')
                                 ->where('sub_order_no', $order_num)
                                 ->orderBy('id', 'desc')
                                 ->first();

        if (!in_array($result->snapshot_type, [
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_NO,
        ])) {
            throw new \Exception(
                '当前订单不可取消'
            );
        }

        // 获取需要的状态
        $useState = $this->service->getCancelApplyState($result, $type);
        $remark = $useState['remark'];
        $orderStatus = $useState['orderStatus'];
        $orderact = $useState['orderact'];
        $good_act = $useState['good_act'];

        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $result,
            $orderStatus,
            $remark,
            $orderact,
            $good_act,
            $ip
        ) {
            // 更新日志和退款退货表状态
            $this->service->refundAndReturnUpdateStatus(
                $order_num,
                $result,
                $orderStatus,
                $remark,
                $ip
            );
            // 更新订单和订单商品的状态
            $this->service->refundUpdateOrder($order_num, $orderact, $good_act);
        });

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [],
        ];
    }

    /**
     * 同意-卖家取消/买家退款/买家退货
     *
     * @param $order_num string 子订单号
     * @param $type      int 申请类型：1：卖家取消；2：买家退款；3：买家退货
     * @param $ip        string 操作者ID
     *
     * @return array
     * @throws \Exception
     */
    public function agreeApply($order_num, $type, $ip)
    {
        $result = DpOrderSnapshot::query()
                                 ->with('orderOperation')
                                 ->where('sub_order_no', $order_num)
                                 ->orderBy('id', 'desc')
                                 ->first();

        if (!in_array($result->snapshot_type, [
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME,
        ])) {
            throw new \Exception('当前订单不可同意');
        }

        // 获取需要的状态
        $useState = $this->service->getAgreeApplyState($result, $type);
        $remark = $useState['remark'];
        $orderStatus = $useState['orderStatus'];
        $orderact = $useState['orderact'];
        $good_act = $useState['good_act'];

        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $result,
            $orderStatus,
            $remark,
            $orderact,
            $good_act,
            $ip,
            $type
        ) {
            if ($type == 2 || $type == 1) {
                // 当是同意退款和同意取消时，要执行退款操作
                // 将退款信息写入退款表并通知财务
                $res = $this->informFinance($order_num);
                if ($res['message'] != 'ok') {
                    throw new \Exception($res['message']);
                }
            }
            // 更新日志和退款退货表状态
            $this->service->refundAndReturnUpdateStatus(
                $order_num,
                $result,
                $orderStatus,
                $remark,
                $ip
            );
            // 退款退货导致订单和订单商品表的状态更新
            $this->service->agreeDataHandle(
                $order_num,
                $orderact,
                $good_act,
                $result
            );

            // 发送模板消息
            switch ($type) {
                // 同意退款
                case 2:
                    // 发送模板消息
                    $orderObj = DpOpderForm::query()
                                           ->with('user')
                                           ->where('order_code', $order_num)
                                           ->first();
                    $templateData = [
                        'first'    => ['value' => '您申请的退款，卖家已同意。', 'color' => '#173177'],
                        'keyword1' => ['value' => $order_num, 'color' => '#173177'],
                        'keyword2' => ['value' => $result->orderOperation->real_refund, 'color' => '#173177'],
                        'keyword3' => ['value' => '原路径返回', 'color' => '#173177'],
                        'keyword4' => ['value' => '3~7个工作日', 'color' => '#173177'],
                        'remark'   => ['value' => '如有疑问请联系客服：400-0999138。', 'color' => '#173177']
                    ];
                    $this->sendWechatMsg(
                        $orderObj->user->OpenID,
                        $templateData,
                        'OPENTM200565278'
                    );
                    break;
                // 同意退货
                case 3:
                    $orderObj = DpOpderForm::query()
                                           ->with(['user'])
                                           ->where('order_code', $order_num)
                                           ->first();

                    $goodsName = DpCartInfo::query()
                                           ->with('goods')
                                           ->where('id', $result->orderOperation->aboutOrder[0]->order_goods_id)
                                           ->first();
                    $templateData = [
                        'first'    => ['value' => '卖家同意了您的退货申请，请将货品保持原样，尽快装车退回。', 'color' => '#173177'],
                        'keyword1' => ['value' => '申请退货', 'color' => '#173177'],
                        'keyword2' => ['value' => $goodsName->goods->gname.'等', 'color' => '#173177'],
                        'keyword3' => ['value' => $orderObj->order_code, 'color' => '#173177'],
                        'keyword4' => ['value' => $result->created_at->format('Y-m-d H:i:s'), 'color' => '#173177'],
                        'remark'   => ['value' => '点击详情查看订单详细。', 'color' => '#173177'],
                    ];
                    $this->sendWechatMsg(
                        $orderObj->user->OpenID,
                        $templateData,
                        'OPENTM401701827',
                        config('groupon.wechat.url.buyerOrderDetail').$orderObj->order_code
                    );
                    break;
            }
        });

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [],
        ];
    }

    /**
     * 拒绝-卖家取消/买家退款/买家退货
     *
     * @param $order_num string 子订单号
     * @param $reason_add string 拒绝原因
     * @param $type      int 申请类型：1：卖家取消；2：买家退款；3：买家退货
     * @param $ip        string 操作者ID
     *
     * @return array
     * @throws \Exception
     */
    public function refuseApply($order_num, $reason_add, $type, $ip)
    {
        $result = DpOrderSnapshot::query()
                                 ->with('orderOperation')
                                 ->where('sub_order_no', $order_num)
                                 ->orderBy('id', 'desc')
                                 ->first();

        if (!in_array($result->snapshot_type, [
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME,
        ])) {
            throw new \Exception(
                '当前订单不可拒绝'
            );
        }

        $useState = $this->service->getRefuseApplyState($result, $type);
        $remark = $useState['remark'];
        $orderStatus = $useState['orderStatus'];
        $orderact = $useState['orderact'];
        $good_act = $useState['good_act'];

        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $result,
            $orderStatus,
            $remark,
            $orderact,
            $good_act,
            $ip,
            $type,
            $reason_add
        ) {
            // 更新日志和退款退货表状态
            $this->service->refundAndReturnUpdateStatus(
                $order_num,
                $result,
                $orderStatus,
                $remark,
                $ip,
                $reason_add
            );

            // 发送模板消息
            switch ($type) {
                // 拒绝退款
                case 2:
                    // 发送模板消息
                    $orderObj = DpOpderForm::query()
                                           ->with(['user'])
                                           ->where('order_code', $order_num)
                                           ->first();

                    $goodsName = DpCartInfo::query()
                                           ->with('goods')
                                           ->where('id', $result->orderOperation->aboutOrder[0]->order_goods_id)
                                           ->first();
                    $templateData = [
                        'first'    => ['value' => '卖家拒绝了您的退款申请。', 'color' => '#173177'],
                        'keyword1' => ['value' => '申请退款', 'color' => '#173177'],
                        'keyword2' => ['value' => $goodsName->goods->gname.'等', 'color' => '#173177'],
                        'keyword3' => ['value' => $orderObj->order_code, 'color' => '#173177'],
                        'keyword4' => ['value' => $result->created_at->format('Y-m-d H:i:s'), 'color' => '#173177'],
                        'remark'   => ['value' => '点击详情查看订单详细。', 'color' => '#173177'],
                    ];
                    $this->sendWechatMsg(
                        $orderObj->user->OpenID,
                        $templateData,
                        'OPENTM401701827',
                        config('groupon.wechat.url.buyerOrderDetail').$orderObj->order_code
                    );
                    break;
                // 拒绝退货
                case 3:
                    // 发送模板消息
                    $orderObj = DpOpderForm::query()
                                           ->with(['user'])
                                           ->where('order_code', $order_num)
                                           ->first();

                    $goodsName = DpCartInfo::query()
                                           ->with('goods')
                                           ->where('id', $result->orderOperation->aboutOrder[0]->order_goods_id)
                                           ->first();
                    $templateData = [
                        'first'    => ['value' => '卖家拒绝了您的退货申请。', 'color' => '#173177'],
                        'keyword1' => ['value' => '申请退货', 'color' => '#173177'],
                        'keyword2' => ['value' => $goodsName->goods->gname.'等', 'color' => '#173177'],
                        'keyword3' => ['value' => $orderObj->order_code, 'color' => '#173177'],
                        'keyword4' => ['value' => $result->created_at->format('Y-m-d H:i:s'), 'color' => '#173177'],
                        'remark'   => ['value' => '点击详情查看订单详细。', 'color' => '#173177'],
                    ];
                    $this->sendWechatMsg(
                        $orderObj->user->OpenID,
                        $templateData,
                        'OPENTM401701827',
                        config('groupon.wechat.url.buyerOrderDetail').$orderObj->order_code
                    );
                    break;
            }
        });
        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [],
        ];
    }

    /**
     * 取消的数据更新
     *
     * @param        $data         array 更新的数据
     * @param        $orderStatus  integer 订单日志表的状态
     * @param        $remark       string 订单日志表的备注
     * @param array  $refuse_goods 退款退货的商品信息
     */
    public function refundAndReturnUpdate(
        $data,
        $orderStatus,
        $remark,
        $refuse_goods = []
    ) {
        DB::connection('mysql_zdp_main')->transaction(function () use (
            $data,
            $refuse_goods,
            $orderStatus,
            $remark
        ) {
            // 更新订单日志信息
            $snapshots_id =
                $this->service->updateOrderLog($data['order_code'], $orderStatus, $remark);
            $createArr = array_merge($data, ['snapshots_id' => $snapshots_id]);
            // 将信息添加到退款退货表
            $operation_id = DpOrderOperation::create($createArr)->id;

            if (!empty($refuse_goods)) {
                // 将此次退款的商品信息添加到退款退货的商品表
                foreach ($refuse_goods as $item) {
                    if ($item['cancel_num'] > 0) {
                        $goodsData = [
                            'operation_id'   => $operation_id,
                            'order_goods_id' => $item['id'],
                            'old_buy_num'    => $item['buy_num'],
                            'refund_num'     => $item['cancel_num'],
                            'refund_price'   => $item['refund_price'],
                        ];
                        DpOrderRefundDetailGoodsLog::create($goodsData);
                        // 判断是否下架这个商品
                        if ($item['sold_out'] == 1) {
                            $goodsId = DpCartInfo::find($item['id'])->value('goodid');
                            /** @var \App\Services\Goods\GoodsOperationService $soldGoodsObj */
                            $soldGoodsObj = app()->make('App\Services\Goods\GoodsOperationService');
                            $soldGoodsObj->soldOutOrdinaryGoods($goodsId, '后台操作下架', 0);
                        }
                    }
                }
            }
        });
    }

    /**
     * 提醒卖家发货
     * @param $order_num
     *
     * @return array
     */
    public function remindSend($order_num)
    {
        $this->remindDataHandle($order_num, 'buyer', DpOrderSnapshot::ORDER_FLOW_STATUS_BUYER_REMIND);

        return [
            'code'=>0,
            'message'=>'ok',
            'data'=>[]
        ];
    }

    /**
     * 提醒买家收货
     * @param $order_num
     *
     * @return array
     */
    public function remindReceive($order_num)
    {
        $this->remindDataHandle($order_num, 'seller', DpOrderSnapshot::ORDER_FLOW_STATUS_SELLER_REMIND);

        return [
            'code'=>0,
            'message'=>'ok',
            'data'=>[]
        ];
    }

    /**
     * 提醒的缓存和日志数据更新
     * @param $order_num string 子订单号
     * @param $remindName string 缓存的名字后缀
     * @param $type int 订单日志的状态
     *
     * @return array
     * @throws \Exception
     */
    public function remindDataHandle($order_num, $remindName, $type)
    {
        $orderObj = DpOpderForm::query()
                               ->with(['user', 'shop.user','orderGoods.goods'])
                               ->where('order_code', $order_num)
                               ->first();

        // 获取提醒次数的缓存
        $time = Carbon::tomorrow()->diffInMinutes(Carbon::now());
        $name = $orderObj->order_code . $orderObj->shopid . $remindName;
        $remindTime = Cache::remember($name, $time, function () {
            return DpOpderForm::REMIND_TIME;
        });

        if ($remindTime == 0) {
            throw new \Exception(
                '您今天的提醒次数已经用完了！'
            );
        }

        // 发送模板消息
        $template = 'OPENTM411627058';
        $shopUserOpenId = $orderObj->user->OpenID;
        $templateData = [
            'first'    => ['value' => '您有订单，卖家提醒您确认收货。', 'color' => '#173177'],
            'keyword1' => ['value' => '待收货', 'color' => '#173177'],
            'keyword2' => ['value' => $orderObj->order_clear, 'color' => '#173177'],
            'remark'   => ['value' => '点击详情，查看订单并处理。', 'color' => '#173177'],
        ];
        $url = config('groupon.wechat.url.buyerOrderDetail').$orderObj->order_code;
        $templateId = '';
        if ($remindName == 'buyer') {
            // 提醒卖家
            $template = '';
            $shopUserOpenId = [];
            foreach ($orderObj->shop->user as $shopUser) {
                $shopUserOpenId[] = $shopUser->OpenID;
            }
            $goodsName = empty($orderObj->orderGoods[0]->goods) ? '' : $orderObj->orderGoods[0]->goods->gname;
            $place = empty($orderObj->address) ? $orderObj->vehicle_location : $orderObj->address;
            $templateData = [
                'first'    => ['value' => '您有订单，买家提醒您尽快发货', 'color' => '#173177'],
                'keyword1' => ['value' => $orderObj->buy_realpay,     'color' => '#173177'],
                'keyword2' => ['value' => $goodsName.'等',             'color' => '#173177'],
                'keyword3' => ['value' => $place,                      'color' => '#173177'],
                'remark'   => ['value' => '点击详情查看订单详细',        'color' => '#173177'],
            ];
            $url = config('groupon.wechat.remind_seller');
            $templateId = config('groupon.wechat.remind_seller');
        }

        $this->sendWechatMsg(
            $shopUserOpenId,
            $templateData,
            $template,
            $url,
            $templateId
        );

        Cache::decrement($name, 1);

        // 更新订单日志
        $remark = DpOrderSnapshot::$snapshotTypeArr[$type];
        $this->service->updateOrderLog(
            $order_num,
            $type,
            $remark
        );
    }


    /**
     * 将退款信息写入退款表并通知财务
     * @param $order_num string 子订单号
     *
     * @return array
     * @throws \Exception
     */
    private function informFinance($order_num)
    {
        // 签名数据
        /** @var \App\Utils\RequestDataEncapsulationUtil $requestObj */
        $requestObj = app()->make('App\Utils\RequestDataEncapsulationUtil');
        $data = [
            'order_num'=>$order_num
        ];
        $requestData = $requestObj->getHttpRequestSign(
            $data,
            config('signature.main_sign_key')
        );
        // 请求地址
        $url = config('request_url.main_request_url').'/seller/order/sendInformFinance';
        // 发起请求
        /** @var \App\Utils\HTTPRequestUtil $httpObj */
        $httpObj = app()->make('App\Utils\HTTPRequestUtil');
        $res = $httpObj->post(
            $url,
            $requestData,
            [
                'Accept' => 'application/json',
            ]
        );
        $resArr = json_decode($res, true);
        if (json_last_error() != 0 || $resArr['code'] > 0) {
            throw new \Exception($resArr['message'], $resArr['code']);
        }

        return [
            'code'    => 0,
            'data'    => [],
            'message' => 'ok',
        ];
    }

    /**
     * 卖家确认退款
     * @param $order_num string 子订单号
     * @param $type int 申请类型 1：卖家取消订单成功后；2：买家退货成功后
     * @param $ip string 操作者ip
     *
     * @return array
     * @throws \Exception
     */
    public function sellerRefund($order_num, $type, $ip)
    {
        if ($type == 1) {
            $result = DpOrderSnapshot::query()
                           ->with('orderOperation')
                           ->where('sub_order_no', $order_num)
                           ->whereIn('snapshot_type', [
                               DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER,
                               DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME,
                           ])
                           ->orderBy('id', 'desc')
                           ->first();
            if (empty($result)) {
                throw new \Exception(
                    '流程信息不完整'
                );
            }
        } else {
            $result = DpOrderSnapshot::query()
                                     ->with('orderOperation')
                                     ->where('sub_order_no', $order_num)
                                     ->whereIn('snapshot_type', [
                                         DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL,
                                         DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME,
                                     ])
                                     ->orderBy('id', 'desc')
                                     ->first();
            $returnGoodsObj =  DpOrderSnapshot::query()
                                              ->with('orderOperation')
                                              ->where('sub_order_no', $order_num)
                                              ->whereIn('snapshot_type', [
                                                  DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_YES,
                                                  DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_YES,
                                              ])
                                              ->orderBy('id', 'desc')
                                              ->first();
            if (empty($result) || empty($returnGoodsObj)) {
                throw new \Exception(
                    '流程信息不完整'
                );
            }
        }


        $useState = $this->service->getSellerRefundState($result, $type);
        $remark = $useState['remark'];
        $orderStatus = $useState['orderStatus'];
        $orderact = $useState['orderact'];
        $good_act = $useState['good_act'];

        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $result,
            $orderStatus,
            $remark,
            $orderact,
            $good_act,
            $ip,
            $type
        ) {
            // 将退款信息写入退款表并通知财务
            $res = $this->informFinance($order_num);
            if ($res['message'] != 'ok') {
                throw new \Exception($res['message']);
            }
            // 更新日志和退款退货表状态
            $this->service->refundAndReturnUpdateStatus(
                $order_num,
                $result,
                $orderStatus,
                $remark,
                $ip
            );
            if ($type == 1) {
                // 同意退款导致订单信息更改
                $this->service->agreeDataHandle($order_num, $orderact, $good_act, $result);
            } else {
                // 更新订单和订单商品的状态
                $this->service->refundUpdateOrder($order_num, $orderact, $good_act);
            }
            // 发送模板消息
            switch ($type) {
                case 1:
                    // 发送模板消息
                    $orderObj = DpOpderForm::query()
                                           ->with('user')
                                           ->where('order_code', $order_num)
                                           ->first();
                    $templateData = [
                        'first'    => ['value' => '卖家已确认退款。', 'color' => '#173177'],
                        'keyword1' => ['value' => $order_num, 'color' => '#173177'],
                        'keyword2' => ['value' => $result->orderOperation->real_refund, 'color' => '#173177'],
                        'keyword3' => ['value' => '原路径返回', 'color' => '#173177'],
                        'keyword4' => ['value' => '3~7个工作日', 'color' => '#173177'],
                        'remark'   => ['value' => '如有疑问请联系客服：400-0999138。', 'color' => '#173177']
                    ];
                    $this->sendWechatMsg(
                        $orderObj->user->OpenID,
                        $templateData,
                        'OPENTM200565278'
                    );
                    break;
                case 2:
                    // 发送模板消息
                    $orderObj = DpOpderForm::query()
                                           ->with('user')
                                           ->where('order_code', $order_num)
                                           ->first();
                    $templateData = [
                        'first'    => ['value' => '卖家已收到退货，并已同意退款。', 'color' => '#173177'],
                        'keyword1' => ['value' => $order_num, 'color' => '#173177'],
                        'keyword2' => ['value' => $result->orderOperation->real_refund, 'color' => '#173177'],
                        'keyword3' => ['value' => '原路径返回', 'color' => '#173177'],
                        'keyword4' => ['value' => '3~7个工作日', 'color' => '#173177'],
                        'remark'   => ['value' => '如有疑问请联系客服：400-0999138。', 'color' => '#173177']
                    ];
                    $this->sendWechatMsg(
                        $orderObj->user->OpenID,
                        $templateData,
                        'OPENTM200565278'
                    );
                    break;
            }
        });
        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [],
        ];
    }

    /**
     * 买家发货
     * @param $order_num
     * @param $driver_tel
     * @param $car_num
     * @param $shipment_address
     * @param $shipment_time
     *
     * @return array
     * @throws \Exception
     */
    public function buyerSend(
        $order_num,
        $driver_tel,
        $car_num,
        $shipment_address,
        $shipment_time
    ) {
        $result = DpOrderSnapshot::query()
            ->where('sub_order_no', $order_num)
            ->whereIn('snapshot_type', [
                DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_YES,
                DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_YES
            ])
            ->orderBy('id', 'desc')
            ->first();

        if (empty($result)) {
            throw new \Exception('当前订单还不能发货');
        }

        // 默认部分退货
        $snapshotType = DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_RETURN;
        $remark = DpOrderSnapshot::$snapshotTypeArr[$snapshotType];
        $orderact = DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_SHIPMENTS;
        $good_act = DpCartInfo::REFUND_ORDER_FROM_RETURN_GOODS_ING_SHIPMENTS;
        if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_YES) {
            // 全部退货
            $snapshotType = DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_RETURN;
            $remark = DpOrderSnapshot::$snapshotTypeArr[$snapshotType];
        }
        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $driver_tel,
            $car_num,
            $shipment_address,
            $shipment_time,
            $snapshotType,
            $remark,
            $orderact,
            $good_act
        ) {
            // 更新日志
            $this->service->updateOrderLog($order_num, $snapshotType, $remark);
            // 更新退款退货表
            DpOrderOperation::query()
                            ->where('order_code', $order_num)
                            ->orderBy('id', 'desc')
                            ->first()
                            ->update(
                                [
                                    'license_plates'   => $car_num,
                                    'vehicle_location' => $shipment_address,
                                    'driver_tel'       => $driver_tel,
                                    'arrive_time'      => $shipment_time,
                                    "order_status"     => $snapshotType,
                                ]
                            );
            // 更新订单和订单商品的状态
            $this->service->refundUpdateOrder($order_num, $orderact, $good_act);
        });
        return [
            'code'=>0,
            'message'=>'ok',
            'data'=>[]
        ];
    }

    /**
     * 买家确认收货（访问的微信端确认收货接口）
     * @param $order_num string 子订单号
     *
     * @return array
     * @throws \Exception
     */
    public function buyerDelivery($order_num)
    {
        $result = DpOpderForm::query()
            ->where('order_code', $order_num)
            ->first();
        if ($result->orderact !== DpOpderForm::DELIVERY_ORDER) {
            throw new \Exception('当前订单不可收货');
        }

        // 签名数据
        /** @var \App\Utils\RequestDataEncapsulationUtil $requestObj */
        $requestObj = app()->make('App\Utils\RequestDataEncapsulationUtil');
        $user = Auth::user();
        $personName = $user->user_name;
        $data = [
            'sub_order_id'=>$order_num,
            'person'=>$personName
        ];
        $requestData = $requestObj->requestDataSign(
            $data,
            config('signature.main_sign_key')
        );
        // 请求地址
        $url = config('request_url.main_request_url').'/order/buy/receive';
        // 发起请求
        /** @var \App\Utils\HTTPRequestUtil $httpObj */
        $httpObj = app()->make('App\Utils\HTTPRequestUtil');
        $res = $httpObj->post(
            $url,
            $requestData,
            [
                'Accept' => 'application/json',
            ]
        );
        $resArr = json_decode($res, true);
        if (json_last_error() != 0 || $resArr['code'] > 0) {
            throw new \Exception($resArr['message']);
        }

        return [
            'code'    => 0,
            'data'    => [],
            'message' => '收货成功，感谢您的支持！',
        ];
    }

    /**
     * 再次申请-卖家取消/买家退款/买家退货
     * @param $order_num string 子订单号
     * @param $type int 申请类型
     *
     * @return array
     * @throws \Exception
     */
    public function againApply($order_num, $type)
    {
        $resultObj = DpOrderSnapshot::query()
                                 ->with('orderOperation.aboutOrder')
                                 ->where('sub_order_no', $order_num)
                                 ->orderBy('id', 'desc')
                                 ->first();
        if (!in_array($resultObj->snapshot_type, [
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO_SOME,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_NO,
            DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_NO
        ])) {
            throw new \Exception('当前订单不可再次申请');
        }

        $result = DpOrderSnapshot::query()
                                 ->with('orderOperation.aboutOrder')
                                 ->where('sub_order_no', $order_num)
                                 ->whereIn('snapshot_type', [
                                     DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER,
                                     DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME,
                                     DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL,
                                     DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME,
                                     DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL,
                                     DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME,
                                 ])
                                 ->orderBy('id', 'desc')
                                 ->first();

        $orderact = 0;
        $good_act = 0;
        switch ($type) {
            case 1:
                $orderact = DpOpderForm::SELLER_CANCEL_ORDER_ING;
                $good_act = DpCartInfo::SELLER_CANCEL_ORDER_ING;
                break;
            case 2:
                $orderact = DpOpderForm::REFUND_ORDER_FROM_PRICE_ING;
                $good_act = DpCartInfo::REFUND_GOODS_ING;
                break;
            case 3:
                $orderact = DpOpderForm::REFUND_ORDER_FROM_GOODS_ING;
                $good_act = DpCartInfo::REFUND_ORDER_FROM_GOODS_ING;
                break;
        }
        DB::connection('mysql_zdp_main')->transaction(function () use (
            $order_num,
            $result,
            $orderact,
            $good_act
        ) {
            // 更新订单和订单商品状态
            $this->service->refundUpdateOrder($order_num, $orderact, $good_act);
            // 重新申请需要写入的信息
            // 写入日志
            $id = $this->service->updateOrderLog($order_num, $result->snapshot_type, $result->remark);
            // 写入退款退货表
            $data = [
                'order_code'              => $result->orderOperation->order_code,
                'order_status'            => $result->orderOperation->order_status,
                'presenter'               => $result->orderOperation->presenter,
                'uid'                     => $result->orderOperation->uid,
                'license_plates'          => $result->orderOperation->license_plates,
                'vehicle_location'        => $result->orderOperation->vehicle_location,
                'driver_tel'              => $result->orderOperation->driver_tel,
                'arrive_time'             => $result->orderOperation->arrive_time,
                'shop_id'                 => $result->orderOperation->shop_id,
                'good_num'                => $result->orderOperation->good_num,
                'good_count'              => $result->orderOperation->good_count,
                'reduced_price_increment' => $result->orderOperation->reduced_price_increment,
                'real_refund'             => $result->orderOperation->real_refund,
                'refund'                  => $result->orderOperation->refund,
                'reason'                  => $result->orderOperation->reason,
                'reason_info'             => $result->orderOperation->reason_info,
                'refuse_reason'           => $result->orderOperation->refuse_reason,
                'form_ip'                 => $result->orderOperation->form_ip,
                'snapshots_id'            => $id
            ];

            // 将信息添加到退款退货表
            $operation_id = DpOrderOperation::create($data)->id;

            // 将此次退款的商品信息添加到退款退货的商品表
            foreach ($result->orderOperation->aboutOrder as $item) {
                $goodsData = [
                    'operation_id'   => $operation_id,
                    'order_goods_id' => $item->order_goods_id,
                    'old_buy_num'    => $item->old_buy_num,
                    'refund_num'     => $item->refund_num,
                    'refund_price'   => $item->refund_price,
                ];
                DpOrderRefundDetailGoodsLog::create($goodsData);
            }
        });
        return [
            'code'=>0,
            'message'=>'ok',
            'data'=>[]
        ];
    }

    /**
     * 修改退款金额
     * @param $orderNum string 子订单号
     * @param $money int 退款金额（分）
     *
     * @return array
     * @throws \Exception
     */
    public function editRefund($orderNum, $money)
    {
        $OrderInfo = DpOpderForm::query()
                                ->where('order_code', $orderNum)
                                ->with('orderGoods.goods.goodsAttribute')
                                ->first();

        $lastSnapshotInfo = DpOrderSnapshot::query()
                                           ->with('orderOperation')
                                           ->whereIn('snapshot_type', [
                                               DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL,
                                               DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME,
                                           ])
                                           ->where('sub_order_no', $orderNum)
                                           ->orderBy('id', 'desc')
                                           ->first();
        $money = MoneyUnitConvertUtil::fenToYuan($money);  //分转为元
        if ($money > $lastSnapshotInfo->orderOperation->refund) {
            throw new \Exception('超过所退金额总价格');
        }

        $operation = $lastSnapshotInfo->orderOperation;
        $userId = $OrderInfo->uid;
        DB::connection('mysql_zdp_main')->transaction(function () use ($operation, $money, $userId) {
            DpOrderOperation::query()
                            ->where('id', $operation->id)
                            ->update(
                                [
                                    'real_refund' => $money,
                                ]
                            );
            DpOrderOperationMoneyLog::create(
                [
                    'old_money'         => $operation->real_refund,
                    'new_money'         => $money,
                    'operation_user_id' => $userId,
                    'operation_id'      => $operation->id,
                ]
            );
        });

        return [
            'code'    => 0,
            'data'    => [],
            'message' => '修改成功',
        ];
    }

    /**
     * 调用包中的微信发送服务
     *
     * @param string|array $openId          接收者openid
     * @param string $template        模板id
     * @param array  $data            发送数据
     * @param null   $url             详情url
     * @param null   $miniProgram     小程序
     * @param null   $templateShortId 模板迷你id
     */
    private function sendWechatMsg(
        $openId,
        $data,
        $templateShortId = null,
        $url = null,
        $template = null,
        $miniProgram = null
    ) {
        /** @var \Zdp\WechatJob\Services\WechatJobService $service */
        $service = app()->make('Zdp\WechatJob\Services\WechatJobService');
        $service->sendTemplateNotify(
            $openId,
            $template,
            $data,
            $url,
            $miniProgram,
            $templateShortId
        );
    }

    /**
     * 财务撤回
     * @param $refund_num string 退款编号
     *
     * @return array
     * @throws \Exception
     */
    public function financerRecall($refund_num)
    {
        // 获取退款信息
        $refundObj = DpOrderRefund::query()
            ->where('refund_no', $refund_num)
            ->first();
        if ($refundObj->status != DpOrderRefund::PENDING) {
            throw  new \Exception('当前退款已处理不可再撤回');
        }

        // 获取订单信息
        $orderObj = DpOpderForm::query()
            ->where('order_code', $refundObj->sub_order_no)
            ->first();

        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use (
                $self,
                $refund_num,
                $refundObj,
                $orderObj
            ) {
                // 订单还原
                $this->service->recallOrderData($orderObj->order_code);

                // 进行退款记录处理
                $user = Auth::user();
                $disposeUserId = $user->id;
                $disposeUserName = $user->user_name;

                // 签名数据
                /** @var \App\Utils\RequestDataEncapsulationUtil $requestObj */
                $requestObj = app()->make('App\Utils\RequestDataEncapsulationUtil');
                $data = [
                    'refundNo'=>$refund_num,
                    'disposeUserId'=>$disposeUserId,
                    'disposeUserName'=>$disposeUserName
                ];
                $requestData = $requestObj->getHttpRequestSign(
                    $data,
                    config('signature.main_sign_key')
                );
                // 请求地址
                $url = config('request_url.main_request_url').'/seller/order/sendRecallInformFinance';
                // 发起请求
                /** @var \App\Utils\HTTPRequestUtil $httpObj */
                $httpObj = app()->make('App\Utils\HTTPRequestUtil');
                $res = $httpObj->post(
                    $url,
                    $requestData,
                    [
                        'Accept' => 'application/json',
                    ]
                );
                $resArr = json_decode($res, true);
                if (json_last_error() != 0 || $resArr['code'] > 0) {
                    throw new \Exception($resArr['message']);
                }
            }
        );
        return [
            'code'    => 0,
            'data'    => [],
            'message' => 'ok',
        ];
    }
}