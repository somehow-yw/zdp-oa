<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/9 0009
 * Time: 上午 9:54
 */

namespace App\Services\Order;

use App\Exceptions\Order\OrderException;
use App\Repositories\Orders\Contracts\OrderGoodsRepository;
use App\Utils\GenerateRandomNumber;
use App\Utils\MoneyUnitConvertUtil;
use Carbon\Carbon;
use DB;
use function GuzzleHttp\Promise\queue;
use Illuminate\Support\Facades\Auth;
use Zdp\Main\Data\Events\OrderLogEvent;
use Zdp\Main\Data\Models\DpCartInfo;
use Zdp\Main\Data\Models\DpOpderForm;
use Zdp\Main\Data\Models\DpOrderBatches;
use Zdp\Main\Data\Models\DpOrderCancelReason;
use Zdp\Main\Data\Models\DpOrderOperation;
use Zdp\Main\Data\Models\DpOrderRefund;
use Zdp\Main\Data\Models\DpOrderRefundLog;
use Zdp\Main\Data\Models\DpOrderSnapshot;


/**
 * 订单处理
 * Class OrderService
 * @package App\Services\Order
 */
class OrderService
{
    /**
     * @var OrderGoodsRepository
     */
    private $repository;

    public function __construct(OrderGoodsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取订单列表信息
     *
     * @param $order_id int 订单ID
     * @param $order_num string 订单号
     * @param $order_type int 订单类型
     * @param $buy_shop string 买家店铺名称
     * @param $seller_shop string 卖家店铺名称
     * @param $buy_phone string 买家电话
     * @param $seller_phone string 卖家电话
     * @param $page int 第几页
     * @param $size int 每页显示的数量
     *
     * @return array
     */
    public function getList(
        $order_id,
        $order_num,
        $order_type,
        $buy_shop,
        $seller_shop,
        $buy_phone,
        $seller_phone,
        $page,
        $size
    ) {
        $resData = [];
        $orderState = $this->frontToEnd($order_type);
        $orderInfo = $this->repository->getList(
            $order_id,
            $order_num,
            $orderState,
            $buy_shop,
            $seller_shop,
            $buy_phone,
            $seller_phone,
            $page,
            $size
        );

        $waitData = [];
        if (!empty($orderInfo['waitPay']) && !$orderInfo['waitPay']->isEmpty()) {
            foreach ($orderInfo['waitPay'] as $key => $value) {
                $waitPayData['order_id'] = $value->id . '等';
                $waitPayData['order_num'] = $value->codenumber;
                $waitPayData['seller_shop_name'] = '';
                $waitPayData['buyer_shop_name'] = '';
                if (!empty($value->buyShopName)) {
                    $waitPayData['buyer_shop_name'] = $value->buyShopName;
                }
                $waitPayData['time'] = $value->addtime;
                $waitPayData['order_state'] = $this->endToFront($value->orderact);
                if (!empty($value->dianPuName)) {
                    $waitPayData['seller_shop_name'] = $value->dianPuName . '等';
                }
                $waitPayData['goods_num'] = $value->good_count;
                $waitPayData['total_price'] = $value->buy_realpay;
                $waitData[] = $waitPayData;
            }
        }
        $Data = [];
        if (!empty($orderInfo['data']) && !$orderInfo['data']->isEmpty()) {
            foreach ($orderInfo['data'] as $key => $value) {
                $otherData['order_id'] = $value->id;
                $otherData['order_num'] = $value->order_code;
                $otherData['seller_shop_name'] = '';
                $otherData['buyer_shop_name'] = '';
                if (!empty($value->buyShopName)) {
                    $otherData['buyer_shop_name'] = $value->buyShopName;
                }
                $otherData['time'] = $value->addtime;
                $otherData['order_state'] = $this->endToFront($value->orderact);
                if (!empty($value->dianPuName)) {
                    $otherData['seller_shop_name'] = $value->dianPuName;
                }
                $otherData['goods_num'] = $value->good_count;
                $otherData['total_price'] = $value->buy_realpay;
                $Data[] = $otherData;
            }
        }

        $resData['data_info'] = array_merge($waitData, $Data);
        $resData['current'] = $page;
        $resData['total'] = $orderInfo['countNum'];

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => $resData,
        ];
    }

    /**
     * 将前端请求的状态转换成后端搜索的状态
     *
     * @param $order_type int 前端请求的状态
     *
     * @return array|string
     */
    public function frontToEnd($order_type)
    {
        $orderState = '';
        switch ($order_type) {
            // 交易订单
            case DpOpderForm::SHOW_TRADE_ORDER:
                $orderState = [
                    DpOpderForm::NEW_ORDER,
                    DpOpderForm::COMMUNICATE_ORDER,
                    DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER,
                    DpOpderForm::CONFIRM_ORDER,
                    DpOpderForm::DELIVERY_ORDER,
                ];
                break;
            // 待付款
            case DpOpderForm::BUY_PAGE_STATUS_WAIT_PAY:
                $orderState = [
                    DpOpderForm::NEW_ORDER,
                    DpOpderForm::COMMUNICATE_ORDER,
                    DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER,
                ];
                break;
            // 待发货
            case DpOpderForm::BUY_PAGE_STATUS_WAIT_SHIPMENT:
                $orderState = [
                    DpOpderForm::CONFIRM_ORDER,
                ];
                break;
            // 已发货
            case DpOpderForm::BUY_PAGE_STATUS_ALREADY_SHIPMENT:
                $orderState = [
                    DpOpderForm::DELIVERY_ORDER,
                ];
                break;
            // 退款订单
            case DpOpderForm::SHOW_REFUND_ORDER:
                $orderState = [
                    DpOpderForm::REFUND_ORDER_FROM_PRICE_ING,
                    DpOpderForm::FREZE_ORDER,
                    DpOpderForm::SELLER_CANCEL_ORDER_ING,
                ];
                break;
            // 买家退款
            case DpOpderForm::BUY_PAGE_STATUS_BUYER_APPLY_REFUND:
                $orderState = [
                    DpOpderForm::REFUND_ORDER_FROM_PRICE_ING,
                    DpOpderForm::FREZE_ORDER,
                ];
                break;
            // 卖家取消订单
            case DpOpderForm::BUY_PAGE_STATUS_SELLER_APPLY_CANCEL_ORDER:
                $orderState = [
                    DpOpderForm::SELLER_CANCEL_ORDER_ING,
                ];
                break;
            // 退货订单
            case DpOpderForm::SHOW_RETURN_ORDER:
                $orderState = [
                    DpOpderForm::REFUND_ORDER_FROM_GOODS_ING,
                    DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_WAIT,
                    DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_SHIPMENTS,
                ];
                break;
            // 申请退货
            case DpOpderForm::BUY_PAGE_STATUS_BUYER_APPLY_RETURN:
                $orderState = [
                    DpOpderForm::REFUND_ORDER_FROM_GOODS_ING,
                ];
                break;
            // 退货中-待发货
            case DpOpderForm::SHOW_RETURN_ORDER_WAIT_SEND:
                $orderState = [
                    DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_WAIT,
                ];
                break;
            // 退货中-已发货
            case DpOpderForm::SHOW_RETURN_ORDER_SEND:
                $orderState = [
                    DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_SHIPMENTS,
                ];
                break;
            // 财务待确认订单
            case DpOpderForm::SHOW_FINANACE_ORDER:
                $orderState = [
                    DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER,
                ];
                break;
            // 财务待确认
            case DpOpderForm::SHOW_FINANACE_WAIT_ORDER:
                $orderState = [
                    DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER,
                ];
                break;
            // 提现订单
            case DpOpderForm::SHOW_WITHDRAWED_ORDER:
                $orderState = [
                    DpOpderForm::TAKE_ORDER,
                    DpOpderForm::HAVE_EVALUATION,
                    DpOpderForm::WITHDRAW_BEING_PROCESSED_ORDER,
                    DpOpderForm::WITHDRAW_ACCOMPLISH_ORDER,
                ];
                break;
            // 可提现
            case DpOpderForm::BUY_PAGE_STATUS_WITHDRAW:
                $orderState = [
                    DpOpderForm::TAKE_ORDER,
                    DpOpderForm::HAVE_EVALUATION,
                ];
                break;
            // 提现中
            case DpOpderForm::BUY_PAGE_STATUS_WITHDRAWING:
                $orderState = [
                    DpOpderForm::WITHDRAW_BEING_PROCESSED_ORDER,
                ];
                break;
            // 提现完成
            case DpOpderForm::BUY_PAGE_STATUS_WITHDRAWED:
                $orderState = [
                    DpOpderForm::WITHDRAW_ACCOMPLISH_ORDER,
                ];
                break;
            // 交易关闭
            case DpOpderForm::BUY_PAGE_STATUS_ORDER_CLOSE:
                $orderState = [
                    DpOpderForm::INVALID_ORDER,
                    DpOpderForm::DEL_ORDER,
                    DpOpderForm::TIMEOUT_ORDER,
                    DpOpderForm::REFUND_ORDER_GOODS,
                ];
                break;
            // 退货-交易关闭
            case DpOpderForm::SHOW_BUY_PAGE_STATUS_ORDER_CLOSE_RRTURN:
                $orderState = [
                    DpOpderForm::REFUND_ORDER_GOODS,
                ];
                break;
            // 退款-交易关闭
            case DpOpderForm::SHOW_BUY_PAGE_STATUS_ORDER_CLOSE_REFUND:
                $orderState = [
                    DpOpderForm::INVALID_ORDER,
                ];
                break;
        }

        return $orderState;
    }

    /**
     * 将后端状态转换成前端状态展示
     *
     * @param $orderState int 订单状态
     *
     * @return int|string
     */
    public function endToFront($orderState)
    {
        $returnState = '';
        switch ($orderState) {
            // 待付款
            case DpOpderForm::NEW_ORDER:
            case DpOpderForm::COMMUNICATE_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_WAIT_PAY;
                break;
            // 待发货
            case DpOpderForm::CONFIRM_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_WAIT_SHIPMENT;
                break;
            // 已发货
            case DpOpderForm::DELIVERY_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_ALREADY_SHIPMENT;
                break;
            // 买家退款
            case DpOpderForm::REFUND_ORDER_FROM_PRICE_ING:
            case DpOpderForm::FREZE_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_BUYER_APPLY_REFUND;
                break;
            // 卖家取消订单
            case DpOpderForm::SELLER_CANCEL_ORDER_ING:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_SELLER_APPLY_CANCEL_ORDER;
                break;
            // 申请退货
            case DpOpderForm::REFUND_ORDER_FROM_GOODS_ING:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_BUYER_APPLY_RETURN;
                break;
            // 退货中-待发货
            case DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_WAIT:
                $returnState = DpOpderForm::SHOW_RETURN_ORDER_WAIT_SEND;
                break;
            // 退货中-已发货
            case DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_SHIPMENTS:
                $returnState = DpOpderForm::SHOW_RETURN_ORDER_SEND;
                break;
            // 财务待确认
            case DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER:
                $returnState = DpOpderForm::SHOW_FINANACE_WAIT_ORDER;
                break;
            // 可提现
            case DpOpderForm::TAKE_ORDER:
            case DpOpderForm::HAVE_EVALUATION:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_WITHDRAW;
                break;
            // 提现中
            case DpOpderForm::WITHDRAW_BEING_PROCESSED_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_WITHDRAWING;
                break;
            // 提现完成
            case DpOpderForm::WITHDRAW_ACCOMPLISH_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_WITHDRAWED;
                break;
            // 退款-交易关闭
            case DpOpderForm::INVALID_ORDER:
            case DpOpderForm::REFUND_ORDER:
                $returnState = DpOpderForm::SHOW_BUY_PAGE_STATUS_ORDER_CLOSE_REFUND;
                break;
            // 退货-交易关闭
            case DpOpderForm::REFUND_ORDER_GOODS:
                $returnState = DpOpderForm::SHOW_BUY_PAGE_STATUS_ORDER_CLOSE_RRTURN;
                break;
            // 交易关闭-超时订单
            case DpOpderForm::TIMEOUT_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_ORDER_CLOSE;
                break;
            // 交易关闭-买家取消订单
            case DpOpderForm::DEL_ORDER:
                $returnState = DpOpderForm::BUY_PAGE_STATUS_ORDER_CLOSE;
                break;
        }

        return $returnState;
    }

    /**
     * 订单详情获取售后子状态
     * @param $type int 订单流程中的最新状态
     *
     * @return int|string
     */
    public function cEndToFront($type)
    {
        $state = '';
        switch ($type) {
            // 买家申请退款
            case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL:
            case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME:
                $state = DpOpderForm::BUY_PAGE_STATUS_BUYER_APPLY_REFUND;
                break;
            // 卖家拒绝退款
            case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL_NO:
            case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME_NO:
                $state = DpOpderForm::BUY_PAGE_STATUS_SELLER_REFUSE_REFUND;
                break;
            // 卖家取消订单
            case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER:
            case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME:
                $state = DpOpderForm::BUY_PAGE_STATUS_SELLER_APPLY_CANCEL_ORDER;
                break;
            // 买家拒绝卖家取消订单
            case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO:
            case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO_SOME:
                $state = DpOpderForm::BUY_PAGE_STATUS_BUYER_REFUSE_CANCEL_ORDER;
                break;
            // 买家申请退货
            case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL:
            case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME:
                $state = DpOpderForm::BUY_PAGE_STATUS_BUYER_APPLY_RETURN;
                break;
            // 卖家拒绝退货
            case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_NO:
            case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_NO:
                $state = DpOpderForm::BUY_PAGE_STATUS_SELLER_REFUSE_RETURN;
                break;
        }

        return $state;
    }

    /**
     * 获取订单的详情信息
     *
     * @param $order_num  string 订单号
     *
     * @return array
     * @throws \Exception
     */
    public function getDetail($order_num)
    {
        $resData = [];
        $resultObj = $this->repository->getDetail($order_num);
        if ($resultObj->isEmpty()) {
            throw new \Exception('订单不存在', OrderException::ORDER_NO_EXIST);
        }
        $resObj = $resultObj->first();
        $financeRefundObj = $this->repository->getFinanceRefund($resObj->order_code);
        $finance_refund_data = [];
        if (!empty($financeRefundObj)) {
            $finance_refund_data[0]['id'] = $financeRefundObj->id;
            $finance_refund_data[0]['refund_num'] = $financeRefundObj->refund_no;
            $finance_refund_data[0]['order_num'] = $financeRefundObj->sub_order_no;
            $finance_refund_data[0]['buyer_shop_name'] = '';
            if (!empty($resObj->user)) {
                $finance_refund_data[0]['buyer_shop_name'] = $resObj->user->shop->dianPuName;
            }
            $finance_refund_data[0]['payer'] = '';
            if (!empty($resObj->shop->boss) && !empty($resObj->shop)) {
                $finance_refund_data[0]['payer'] = $resObj->shop->boss->unionName;
            }
            $finance_refund_data[0]['price'] = $financeRefundObj->payment_amount;
            $finance_refund_data[0]['refund_price'] = $financeRefundObj->refund_amount;
            $finance_refund_data[0]['time'] = $financeRefundObj->created_at->toDateTimeString();
            $finance_refund_data[0]['hand_time'] = $financeRefundObj->updated_at->toDateTimeString();
            $finance_refund_data[0]['order_state'] = $financeRefundObj->status;
        }
        $resData['finance_refund_data'] = $finance_refund_data;
        // 待付款
        if (in_array($resObj->orderact, [
            DpOpderForm::NEW_ORDER,
            DpOpderForm::COMMUNICATE_ORDER,
            DpOpderForm::DEPOSIT_BEING_PROCESSED_ORDER,
        ])) {
            $resData['order_id'] = '';
            $resData['order_num'] = $resObj->codenumber;
        } else {
            $resData['order_id'] = $resObj->id;
            $resData['order_num'] = $resObj->order_code;
        }
        $pay_way = '在线支付';
        if ($resObj->buy_way == 2) {
            $pay_way = '集中采购';
        }
        $resData['pay_way'] = $pay_way;
        $resData['order_state'] = $this->endToFront($resObj->orderact);
        $resData['refund_order_type'] = '';
        if (!$resObj->orderLog->isEmpty()) {
            $refund_order_type = $resObj->orderLog->toArray();
            $resData['refund_order_type'] = $this->cEndToFront($refund_order_type[0]['snapshot_type']);
        }
        $resData['special_order'] = ($resObj->method == DpOpderForm::ORDER_PAY_METHOD_NEGOTIATE) ? 0 : 1;

        // 统合流程信息
        if ($resObj->orderLog->isEmpty()) {
            // 老订单没有流程
            $resData['order_flow'] = [];
        } else {
            $progress_list = $resObj->orderLog->toArray();
            $reDataArr['progress_list'] = [];
            krsort($progress_list);
            foreach ($progress_list as $k3 => $v3) {
                $reDataArr['progress_list'][] = $v3;
            }
            $resData['order_flow'] = $this->getOrderFlow($reDataArr['progress_list'], $resData['special_order']);
        }

        // 统合商品信息
        $goodsInfo = $this->getGoodsInfo($resultObj);
        $resData = array_merge($resData, $goodsInfo);

        // 统合买家信息
        $resData['buyer_news'] = [];
        $resData['buyer_news'][0]['buyer_shop_name'] = $resObj->user->shop->dianPuName;
        $resData['buyer_news'][0]['name'] = $resObj->real_name;
        $resData['buyer_news'][0]['tel'] = $resObj->user_tel;
        $resData['buyer_news'][0]['address'] = $resObj->address;

        // 统合卖家信息
        $resData['seller_news'] = [];
        foreach ($resultObj as $k => $value) {
            $resData['seller_news'][$k]['seller_shop_name'] = $value->shop->dianPuName;
            $resData['seller_news'][$k]['register_tel'] = $value->shop->dianPuName;
            $resData['seller_news'][$k]['seller_tel'] = $value->shop->jieDanTel;
            $resData['seller_news'][$k]['seller_address'] =
                $value->shop->province . $value->shop->city .
                $value->shop->county . $value->shop->house_number . $value->shop->xiangXiDiZi;
        }
        // 统合发货信息
        $resData['logistics'] = [];
        if ($resObj->delivery == DpOpderForm::ORDER_LOGISTICS_TAKE_GOODS) {
            // 自己有车（买家找车）logistics
            $resData['logistics']['buyer_find_car'] = [];
            $resData['logistics']['buyer_find_car'][0]['buyer_market'] = $resObj->shop->market->pianqu;
            $resData['logistics']['buyer_find_car'][0]['car_tel'] = $resObj->driver_tel;
            $resData['logistics']['buyer_find_car'][0]['car_num'] = $resObj->license_plates;
            $resData['logistics']['buyer_find_car'][0]['contacts'] = $resObj->contacts;
            $resData['logistics']['buyer_find_car'][0]['contact_tel'] = $resObj->contact_tel;
            $resData['logistics']['buyer_find_car'][0]['car_address'] = $resObj->vehicle_location;
            $resData['logistics']['buyer_find_car'][0]['car_time'] = $resObj->shipment_time;
        } else {
            // 卖家找车
            $resData['logistics']['seller_find_car'] = [];
            $resData['logistics']['seller_find_car'][0]['seller_market'] = $resObj->shop->market->pianqu;
            $resData['logistics']['seller_find_car'][0]['car_tel'] = $resObj->driver_tel;
            $resData['logistics']['seller_find_car'][0]['car_num'] = $resObj->license_plates;
            $resData['logistics']['seller_find_car'][0]['contacts'] = $resObj->contacts;
            $resData['logistics']['seller_find_car'][0]['contact_tel'] = $resObj->contact_tel;
            $resData['logistics']['seller_find_car'][0]['get_area'] =
                $resObj->user->shop->province . $resObj->user->shop->city .
                $resObj->user->shop->county . $resObj->user->shop->house_number;
            $resData['logistics']['seller_find_car'][0]['get_address'] =
                $resObj->user->shop->xiangXiDiZi;
        }

        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => $resData,
        ];
    }

    /**
     * 统合详情页的商品信息
     * @param $result object 详情订单对象
     *
     * @return array
     */
    private function getGoodsInfo($result)
    {
        $goodsInfo = [];

        $goodsInfo['buy_kind'] = '';
        $goodsInfo['order_price'] = 0;
        $goodsInfo['order_reduce_price'] = 0;
        $goodsInfo['relief_amount'] = 0;
        $goodsInfo['buy_realpay'] = 0;
        $goodsInfo['transport_pay'] = MoneyUnitConvertUtil::fenToYuan(0);
        $goodsInfo['goods_info'] = [];
        $goodsInfo['seller_cancel_order'] = [];
        $goodsInfo['buyer_refund_data'] = [];
        $goodsInfo['buyer_return_data'] = [];
        $refundKey = 0;
        $sellerRefundKey = 0;
        $returnKey = 0;
        $goodsKey = 0;
        foreach ($result as $k => $value) {
            // 购买的商品信息
            $goodsInfo['buy_kind'] += $value->good_num;
            $goodsInfo['relief_amount'] += MoneyUnitConvertUtil::yuanToFen($value->relief_amount);
            $goodsInfo['buy_realpay'] += MoneyUnitConvertUtil::yuanToFen($value->buy_realpay);
            foreach ($value->orderGoods as $k1 => $v1) {
                $goodsInfo['order_price'] += $v1->buy_num * MoneyUnitConvertUtil::yuanToFen($v1->good_new_price);
                $goodsInfo['order_reduce_price'] += MoneyUnitConvertUtil::yuanToFen($v1->preferential_value);
                $goodsInfo['goods_info'][$goodsKey]['goods_id'] = $v1->id;
                $goodsInfo['goods_info'][$goodsKey]['goods_title'] =
                    $v1->goods->goods_title;
                $goodsInfo['goods_info'][$goodsKey]['price'] = $v1->good_new_price;
                $goodsInfo['goods_info'][$goodsKey]['num'] = $v1->buy_num;
                $goodsInfo['goods_info'][$goodsKey]['reduce_price'] = $v1->preferential_value;
                $goodsInfo['goods_info'][$goodsKey]['total_price'] = MoneyUnitConvertUtil::fenToYuan($v1->buy_num * MoneyUnitConvertUtil::yuanToFen($v1->good_new_price));
                $goodsKey ++;
            }

            if (!$value->orderLog->isEmpty()) {
                // 退款退货时的商品信息
                foreach ($value->orderLog as $k2 => $v2) {
                    switch ($v2->snapshot_type) {
                        // 买家取消订单
                        case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL:
                            break;
                        // 买家申请退款
                        case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL:
                        case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME:
                            $goodsInfo['buyer_refund_data'][$refundKey] = $this->getRefundGoodsInfo($v2);
                            $refundKey ++;
                            break;
                        // 卖家取消订单
                        case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER:
                        case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME:
                            $goodsInfo['seller_cancel_order'][$sellerRefundKey] = $this->getRefundGoodsInfo($v2);
                            $sellerRefundKey ++;
                            break;
                        // 买家退货
                        case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL:
                        case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME:
                            $goodsInfo['buyer_return_data'][$returnKey] = $this->getRefundGoodsInfo($v2);
                            $returnKey++;
                            break;
                    }
                }
            }
        }
        $goodsInfo['order_price'] = MoneyUnitConvertUtil::fenToYuan($goodsInfo['order_price']);
        $goodsInfo['order_reduce_price'] = MoneyUnitConvertUtil::fenToYuan($goodsInfo['order_reduce_price']);
        $goodsInfo['relief_amount'] = MoneyUnitConvertUtil::fenToYuan($goodsInfo['relief_amount']);
        $goodsInfo['buy_realpay'] = MoneyUnitConvertUtil::fenToYuan($goodsInfo['buy_realpay']);

        return $goodsInfo;
    }

    /**
     * 统合详情退款退货返回的商品信息
     * @param $order_log object 订单日志
     *
     * @return array
     */
    private function getRefundGoodsInfo($order_log)
    {
        $order_obj = json_decode($order_log->snapshot_info);
        $order_goods_obj = $order_obj->order_goods;
        // 退款退货商品信息
        $goodsInfo['cancel_goods'] = [];
        $goodsInfo['cancel_goods']['cancel_count'] =
            $order_log->orderOperation->good_num;
        $goodsInfo['cancel_goods']['transport_pay'] = MoneyUnitConvertUtil::fenToYuan(0);
        $goodsInfo['cancel_goods']['cancel_total_price'] = 0;
        $goodsInfo['cancel_goods']['reduce_total_price'] = 0;
        $goodsInfo['cancel_goods']['cancel_relief_amount'] = MoneyUnitConvertUtil::fenToYuan(0);
        $goodsInfo['cancel_goods']['cancel_buy_realpay'] =
            $order_log->orderOperation->real_refund;
        $goodsInfo['cancel_goods']['cancel_total_refund'] =
            $order_log->orderOperation->real_refund;
        $goodsInfo['cancel_goods']['cancel_goods_info'] =
            [];
        foreach ($order_log->orderOperation->aboutOrder as $k3 => $v3) {
            $goodsInfo['cancel_goods']['cancel_goods_info'][$k3]['goods_id'] =
                $v3->order_goods_id;
            $goodsInfo['cancel_goods']['cancel_goods_info'][$k3]['price'] =
                $v3->refund_price;
            $goodsInfo['cancel_goods']['cancel_goods_info'][$k3]['num'] =
                $v3->refund_num;
            foreach ($order_goods_obj as $k4 => $v4) {
                if ($v4->id == $v3->order_goods_id) {
                    $refundData = $this->getReducePrice(
                        $v3->old_buy_num,
                        $v3->refund_num,
                        $v4->count_price,
                        $v4->preferential_value,
                        $v4->good_new_price
                    );
                    $goodsInfo['cancel_goods']['cancel_total_price'] += $v3->refund_num * MoneyUnitConvertUtil::yuanToFen($v4->good_new_price);
                    $goodsInfo['cancel_goods']['reduce_total_price'] += MoneyUnitConvertUtil::yuanToFen($refundData[2]);
                    $goodsInfo['cancel_goods']['cancel_goods_info'][$k3]['goods_title'] =
                        $v4->goods->goods_title;
                    $goodsInfo['cancel_goods']['cancel_goods_info'][$k3]['reduce_price'] =
                        $refundData[2];
                    $goodsInfo['cancel_goods']['cancel_goods_info'][$k3]['total_price'] =
                        MoneyUnitConvertUtil::fenToYuan($v3->refund_num * MoneyUnitConvertUtil::yuanToFen($v4->good_new_price));
                    $goodsInfo['cancel_goods']['cancel_goods_info'][$k3]['refund'] =
                        $refundData[0];
                }
            }
        }
        $goodsInfo['cancel_goods']['cancel_total_price'] =
            MoneyUnitConvertUtil::fenToYuan($goodsInfo['cancel_goods']['cancel_total_price']);
        $goodsInfo['cancel_goods']['reduce_total_price'] =
            MoneyUnitConvertUtil::fenToYuan($goodsInfo['cancel_goods']['reduce_total_price']);

        // 退款退货的仍购商品
        $goodsInfo['residue_goods'] =
            [];
        $residue_goods_info = [];
        $goodsInfo['residue_goods']['residue_goods_info'] = [];
        $residue_count = 0;
        $residue_total_price = 0;
        $residue_buy_realpay = 0;
        $reduce_total_price = 0;
        $refundArr = collect($order_log->orderOperation->aboutOrder)->pluck('order_goods_id')
                                                                    ->all();
        foreach ($order_goods_obj as $k4 => $v4) {
            if (in_array($v4->id, $refundArr)) {
                foreach ($order_log->orderOperation->aboutOrder as $k3 => $v3) {
                    if ($v4->id === $v3->order_goods_id) {
                        $refundData = $this->getReducePrice(
                            $v3->old_buy_num,
                            $v3->refund_num,
                            $v4->count_price,
                            $v4->preferential_value,
                            $v4->good_new_price
                        );
                        if ($v3->old_buy_num == $v3->refund_num) {
                            $residue_count++;
                            break;
                        }
                        $reduce_total_price +=  MoneyUnitConvertUtil::yuanToFen($refundData[3]);
                        $residue_goods_info['goods_id'] = $v4->id;
                        $residue_goods_info['goods_title'] = $v4->goods->goods_title;
                        $residue_goods_info['price'] = $v3->refund_price;
                        $residue_goods_info['num'] = $v3->old_buy_num - $v3->refund_num;
                        $residue_goods_info['reduce_price'] = $refundData[3];
                        $residue_goods_info['total_price'] = MoneyUnitConvertUtil::fenToYuan(($v3->old_buy_num -
                                                                                              $v3->refund_num) *
                                                                                             MoneyUnitConvertUtil::yuanToFen($v4->good_new_price));
                        $residue_total_price += ($v3->old_buy_num -
                                                 $v3->refund_num) *
                                                MoneyUnitConvertUtil::yuanToFen($v4->good_new_price);
                        $residue_buy_realpay += MoneyUnitConvertUtil::yuanToFen($refundData[4]);
                        $goodsInfo['residue_goods']['residue_goods_info'][] = $residue_goods_info;
                    }
                }
            } else {
                $reduce_total_price +=  MoneyUnitConvertUtil::yuanToFen($v4->preferential_value);
                $residue_goods_info['goods_id'] = $v4->id;
                $residue_goods_info['goods_title'] = $v4->goods->goods_title;
                $residue_goods_info['price'] = $v4->good_new_price;
                $residue_goods_info['num'] = $v4->buy_num;
                $residue_goods_info['reduce_price'] = $v4->preferential_value;
                $residue_goods_info['total_price'] = MoneyUnitConvertUtil::fenToYuan($v4->buy_num * MoneyUnitConvertUtil::yuanToFen($v4->good_new_price));
                $residue_total_price += $v4->buy_num * MoneyUnitConvertUtil::yuanToFen($v4->good_new_price);
                $residue_buy_realpay += MoneyUnitConvertUtil::yuanToFen($v4->count_price);
                $goodsInfo['residue_goods']['residue_goods_info'][] = $residue_goods_info;
            }
        }
        $goodsInfo['residue_goods']['residue_count'] =
            $order_obj->good_num - $residue_count;
        $goodsInfo['residue_goods']['reduce_total_price'] = MoneyUnitConvertUtil::fenToYuan($reduce_total_price);
        $goodsInfo['residue_goods']['residue_total_price'] =
            MoneyUnitConvertUtil::fenToYuan($residue_total_price);
        $goodsInfo['residue_goods']['residue_relief_amount'] =
            $order_obj->relief_amount;
        $goodsInfo['residue_goods']['transport_pay'] =
            MoneyUnitConvertUtil::fenToYuan(0);
        $goodsInfo['residue_goods']['residue_buy_realpay'] =
            MoneyUnitConvertUtil::fenToYuan($residue_buy_realpay);
        // 表示此次是全退
        if ($order_obj->good_num - $residue_count == 0) {
            $goodsInfo['residue_goods'] = [];
        }

        // 退款退货的理由
        $goodsInfo['cancel_reason'] =
            [];
        $goodsInfo['cancel_reason'][0]['cancel_person'] =
            '买家';
        if (in_array($order_log->snapshot_type, [
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER,
            DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME
        ])) {
            $goodsInfo['cancel_reason'][0]['cancel_person'] =
                '卖家';
        }
        $goodsInfo['cancel_reason'][0]['reason'] =
            $order_log->orderOperation->reason_info;
        $goodsInfo['cancel_reason'][0]['reason_add'] =
            $order_log->orderOperation->reason;

        // 退货凭证(图片)
        $goodsInfo['return_img'] = $order_log->orderOperation->aboutImg->pluck('img_url')->all();

        // 拒绝退款退货的理由
        $goodsInfo['refuse_reason'] =
            [];
        if (!empty($order_log->orderOperation->refuse_reason)) {
            $goodsInfo['refuse_reason'][0]['cancel_person'] =
                '卖家';
            $goodsInfo['refuse_reason'][0]['reason'] =
                $order_log->orderOperation->refuse_reason;
        }
        return $goodsInfo;
    }

    /**
     * 详情信息中的退款信息计算
     * @param $buy_num int 原购数量
     * @param $return_num int 退款退货数量
     * @param $total_price float 一种商品的总价
     * @param $reduce_price int 一种商品的优惠值
     * @param $price float 一种商品的下单价格
     *
     * @return array
     *
     */
    private function getReducePrice($buy_num, $return_num, $total_price, $reduce_price, $price)
    {
        $reduce_price = MoneyUnitConvertUtil::yuanToFen($reduce_price);
        // 一种商品实际支付的金额
        $real_price = MoneyUnitConvertUtil::yuanToFen($total_price);
        // 实际退款金额的计算
        $real_refund = ($real_price / $buy_num) * $return_num;
        // 应退金额的计算
        $total_refund = $return_num * MoneyUnitConvertUtil::yuanToFen($price);
        if ($return_num >= $buy_num) {
            $reduce_refund = $reduce_price;
            $still_reduce = 0;
            $still_price = 0;
        } else {
            // 仍购优惠
            $still_reduce = 0;
            // 退款优惠
            $reduce_refund = 0;
            if ($reduce_price !== 0) {
                // 退款优惠
                $reduce_refund = $total_refund - $real_refund;
                // 仍购优惠
                $still_reduce = $reduce_price - $reduce_refund;
            }
            // 仍购商品总价
            $still_price = ($buy_num - $return_num) * MoneyUnitConvertUtil::yuanToFen($price) - $still_reduce;
        }

        return [
            MoneyUnitConvertUtil::fenToYuan($real_refund),
            MoneyUnitConvertUtil::fenToYuan($total_refund),
            MoneyUnitConvertUtil::fenToYuan($reduce_refund),
            MoneyUnitConvertUtil::fenToYuan($still_reduce),
            MoneyUnitConvertUtil::fenToYuan($still_price)
        ];
    }

    /**
     * 获取流程信息
     *
     * @param $snapshot array 流程对象
     * @param $type     int 自行协商（卖家与卖家调货）流程不一样
     *
     * @return array
     */
    private function getOrderFlow($snapshot, $type)
    {
        $flow = [];
        foreach ($snapshot as $k => $value) {
            switch ($value['snapshot_type']) {
                // 订单创建
                case DpOrderSnapshot::CREATE:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    if ($type == 0) {
                        $flow[$k]['person'] = '卖家';
                    }
                    $flow[$k]['time'] = $value['created_at'];
                    break;
                // 买家取消订单
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    if ($type == 0) {
                        $flow[$k]['person'] = '卖家';
                    } elseif ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    $flow[$k]['time'] = $value['created_at'];
                    break;
                // 支付超时
                case DpOrderSnapshot::ORDER_FLOW_STATUS_OUT_TIME:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    break;
                // 订单支付
                case DpOrderSnapshot::ORDER_FLOW_STATUS_PAY_YES:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家取消订单
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家取消申请
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_CANCEL:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_CANCEL_SOME:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家拒绝卖家取消订单
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_NO_SOME:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家同意卖家取消订单
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_YES:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_YES_SOME:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家申请退款
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家取消申请退款
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL_CANCEL:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME_CANCEL:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家拒绝退款
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL_NO:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME_NO:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家同意退款
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL_YES:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME_YES:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家发货
                case DpOrderSnapshot::ORDER_FLOW_STATUS_DELIVER_GOODS:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家申请退货
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家取消申请退货
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_CANCEL_ALL_NO:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_CANCEL_SOME_NO:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家拒绝退货
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_NO:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_NO:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家同意退货
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_YES:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_YES:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家发货
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_RETURN:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_RETURN:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 卖家退货退款
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_YES_REFUND:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_YES_REFUND:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_YES_REFUND_YES:
                case DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_YES_REFUND_YES:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '卖家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 买家收货
                case DpOrderSnapshot::ORDER_FLOW_STATUS_DELIVERY:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '买家';
                    $flow[$k]['time'] = $value['created_at'];
                    if ($value['operation'] !== 0) {
                        $flow[$k]['person'] = $value['name'];
                    }
                    break;
                // 提现中
                case DpOrderSnapshot::ORDER_FLOW_STATUS_WITHDRAW_APPLY:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '';
                    $flow[$k]['time'] = $value['created_at'];
                    break;
                // 提现完成
                case DpOrderSnapshot::ORDER_FLOW_STATUS_WITHDRAW:
                    $flow[$k]['field'] = $value['remark'];
                    $flow[$k]['person'] = '';
                    $flow[$k]['time'] = $value['created_at'];
                    break;
            }
        }

        return $flow;
    }

    /**
     * 支付处理
     *
     * @param $mainOrderNo string 主订单编号
     * @param $payAmount   float 支付金额
     *
     * @return string
     * @throws OrderException
     */
    public function payment($mainOrderNo, $payAmount)
    {
        // 订单查询对象
        $orderActArr = [
            DpOpderForm::NEW_ORDER,
            DpOpderForm::COMMUNICATE_ORDER,
        ];
        $query = DpOpderForm::query()
            ->where('codenumber', $mainOrderNo)
            ->whereIn('orderact', $orderActArr)
            ->where('method_act', DpOpderForm::ORDER_PAY_STATUS_WAIT);
        // 判断是否还有集采未报价订单
        $countQuery = clone $query;
        $orderCount = $countQuery->where('method', DpOpderForm::CENTRALIZED_PURCHASE)->count();
        if ($orderCount > 0) {
            throw new OrderException(OrderException::NOT_PAYMENT);
        }
        unset($countQuery);
        // 统计订单总金额等信息
        $selectArr = [
            DB::raw("SUM('total_price')"),
            DB::raw("SUM('buy_realpay') as pay_amount"),
        ];
        $totalQuery = clone $query;
        $orderSumInfo = $totalQuery->where('method', DpOpderForm::ORDER_PAY_METHOD_COMPANY)
            ->select($selectArr)
            ->first();
        unset($totalQuery);
        // 检查所支付金额是否可以进行支付
        if (is_null($orderSumInfo) || $orderSumInfo->total_price < $payAmount) {
            throw new OrderException(OrderException::PAY_PRICE_ERROR);
        }
        // 取得所有子订单信息
        $subOrderQuery = clone $query;
        $subOrdersInfo = $subOrderQuery->with('user')
            ->where('method', DpOpderForm::ORDER_PAY_METHOD_COMPANY)
            ->get();
        unset($subOrderQuery);
        DB::connection('mysql_zdp_main')->transaction(function () use ($subOrdersInfo, $payAmount, $orderSumInfo) {
            $subOrdersInfoOld = $subOrdersInfo;
            // 循环添加每个子订单到支付中，并计算每个单的优惠金额(如果有优惠)
            $addBatchArr = [];
            //$financeAddArr = [];
            //$subOrderNoArr = [];
            $userName = request()->user()->user_name;
            foreach ($subOrdersInfo as $orderInfo) {
                $randomStr = GenerateRandomNumber::generateString(4);
                $batchNo = $orderInfo->id . '-' . $randomStr . time();
                // 写入支付处理表的信息
                $addBatchArr[] = [
                    'order_id'     => $orderInfo->id, // 订单ID
                    'batch_no'     => $batchNo,      // 批处理号
                    'order_number' => $orderInfo->order_code,  // 订单号
                ];
                $realPayAmount = MoneyUnitConvertUtil::yuanToFen($payAmount);
                $orderAmount = MoneyUnitConvertUtil::yuanToFen($orderInfo->total_price);
                $orderTotalAmount = MoneyUnitConvertUtil::yuanToFen($orderSumInfo->total_price);
                $subsidyAmount = $orderTotalAmount - $realPayAmount;
                if ($subsidyAmount > 0) {
                    // 如果买家少付了，算入优惠
                    // 这里是按每单金额所占总金额的百分比计算优惠金额
                    $deductAmount = round($orderAmount / $orderTotalAmount * $subsidyAmount);
                    $paymentAmount = $orderAmount - $deductAmount;
                } else {
                    //$deductAmount = 0;
                    $paymentAmount = MoneyUnitConvertUtil::yuanToFen($orderInfo->buy_realpay);
                }
                // 更改订单信息
                $orderInfo->buy_realpay = MoneyUnitConvertUtil::fenToYuan($paymentAmount);
                $orderInfo->buy_payment_handler = $userName;
                $orderInfo->buy_payment_bank = DpOpderForm::MONEY_PLACE_FROM_PLAY;
                $orderInfo->payment_method = DpOpderForm::PAYMENT_TYPE_BANK;
                $orderInfo->order_count = DpOpderForm::VALID_ORDER;
                $orderInfo->save();
                // 提交给财务系统的信息
                /*$financeAddArr[] = [
                    'main_order_no'       => $orderInfo->codenumber,
                    'sub_order_no'        => $orderInfo->order_code,
                    'type'                => 1,
                    'batch_no'            => $batchNo,
                    'user_id'             => $orderInfo->uid,
                    'buyer_name'          => $orderInfo->real_name,
                    'shop_id'             => $orderInfo->user->shopId,
                    'shop_name'           => $orderInfo->user->shop->dianPuName,
                    'pay_amount'          => $paymentAmount,
                    'order_amount'        => $orderAmount,
                    'deduct_amount'       => $deductAmount,
                    'pay_channel'         => DpOpderForm::PAYMENT_TYPE_BANK,
                    'money_place'         => DpOpderForm::MONEY_PLACE_FROM_PLAY,
                    'remark'              => '运营确认收款',
                    'order_operator_name' => 'OA管理:' . $userName,
                    'order_create_time'   => $orderInfo->addtime,
                    'order_transfer'      => $orderInfo->order_transfer,
                ];
                // 每个子订单编号
                $subOrderNoArr[] = $orderInfo->order_code;*/
            }
            if (count($addBatchArr)) {
                DpOrderBatches::inster($addBatchArr);
                // 写入订单更改日志(快照)
                $snapshotType = DpOrderSnapshot::ORDER_FLOW_STATUS_PAY_YES;
                $this->orderSnapshot($snapshotType, $subOrdersInfoOld, '支付确认');
            }
        });

        return '提交成功，请联系财务及时处理';
    }

    /**
     * 订单快照记录
     *
     * @param       $snapshotType     integer 快照类型 参考MODEL
     * @param       $orderSnapshotInfo
     *                                \Illuminate\Database\Eloquent\Collection|Collection|\Illuminate\Support\Collection|null
     *                                订单历史信息
     * @param       $remark           string 备注说明
     * @param array $requestDataArr   array 请求操作的数据
     *
     * @throws \Exception
     */
    public function orderSnapshot(
        $snapshotType = 0,
        $orderSnapshotInfo = null,
        $remark = '',
        array $requestDataArr = []
    ) {
        if (is_null($orderSnapshotInfo)) {
            throw new \Exception('订单历史数据不可为空');
        } elseif (!array_key_exists($snapshotType, DpOrderSnapshot::$snapshotTypeArr)) {
            throw new \Exception('日志类型不正确');
        }
        $orderSnapshotInfo = is_null($orderSnapshotInfo) ? collect([]) : $orderSnapshotInfo;
        $remark = empty($remark) ? DpOrderSnapshot::$snapshotTypeArr['$snapshotType'] : $remark;
        // 触发事件记录快照
        event(new OrderLogEvent($orderSnapshotInfo, $snapshotType, $remark, $requestDataArr));
    }

    /**
     * 获取财务退款的订单列表信息
     * @param $order_id int 订单ID
     * @param $order_num string 订单号
     * @param $order_type int 订单类型
     * @param $buy_shop string 买家店铺名称
     * @param $seller_shop string 卖家店铺名称
     * @param $buy_phone string 买家电话
     * @param $seller_phone string 卖家电话
     * @param $page int 第几页
     * @param $size int 每页显示的数量
     *
     * @return array
     */
    public function getRefundList(
        $order_id,
        $order_num,
        $order_type,
        $buy_shop,
        $seller_shop,
        $buy_phone,
        $seller_phone,
        $page,
        $size
    ) {
        $reData = [];
        $resObj = $this->repository->getRefundList(
            $order_id,
            $order_num,
            $order_type,
            $buy_shop,
            $seller_shop,
            $buy_phone,
            $seller_phone,
            $page,
            $size
        );
        $reData['current'] = $resObj->currentPage();
        $reData['total'] = $resObj->total();
        $reData['data_info'] = [];
        foreach ($resObj as $key => $value) {
            $reData['data_info'][$key]['refund_num'] = $value->refund_no;
            $reData['data_info'][$key]['order_num'] = $value->sub_order_no;
            $reData['data_info'][$key]['buyer_shop_name'] = '';
            $reData['data_info'][$key]['payer'] = '';
            $reData['data_info'][$key]['seller_shop_name'] = '';
            if (!empty($value->order->shop)) {
                $reData['data_info'][$key]['seller_shop_name'] = $value->order->shop->dianPuName;
                if (!empty($value->order->shop->boss)) {
                    $reData['data_info'][$key]['payer'] = $value->order->shop->boss->unionName;
                }
            }
            if (!empty($value->order->user->shop)) {
                $reData['data_info'][$key]['buyer_shop_name'] = $value->order->user->shop->dianPuName;
            }

            $reData['data_info'][$key]['price'] = $value->payment_amount;
            $reData['data_info'][$key]['refund_price'] = $value->refund_amount;
            $reData['data_info'][$key]['person'] = $value->apply_user_name;
            $reData['data_info'][$key]['hand_person'] = $value->assentor;
            $reData['data_info'][$key]['time'] = $value->created_at->format('Y-m-d H:i:s');
            $reData['data_info'][$key]['hand_time'] = $value->updated_at->format('Y-m-d H:i:s');
            $reData['data_info'][$key]['order_state'] = $value->status;
        }

        return [
            'code'=>0,
            'message'=>'ok',
            'data'=>$reData
        ];
    }

    /**
     * 获取取消订单的理由列表
     * @param $type int 理由类型
     *
     * @return array
     * @throws \Exception
     */
    public function getReasonList($type)
    {
        $resData = [];

        // 状态转换
        $state = '';
        switch ($type) {
            // 买家取消
            case 1:
                $state = DpOrderCancelReason::BUYER_CANCEL_ORDER;
                break;
            // 卖家取消
            case 2:
                $state = DpOrderCancelReason::SELLER_CANCEL_ORDER;
                break;
            // 退款
            case 3:
                $state = DpOrderCancelReason::BUYER_REFUND;
                break;
            // 退货
            case 4:
                $state = DpOrderCancelReason::BUYER_RETURN;
                break;

        }
        $resObj = $this->repository->getReasonList($state);

        if ($resObj->isEmpty()) {
            throw new \Exception('理由类型不存在');
        }

        // 统合返回的数据
        foreach ($resObj as $k => $v) {
            $resData[$k]['id'] = $v->id;
            $resData[$k]['name'] = $v->content;
        }

        return [
            'code'    => 0,
            'data'    => $resData,
            'message' => 'OK',
        ];
    }

    /**
     * 更新订单的日志
     *
     * @param $order_num    string 子订单号
     * @param $snapshotType int 订单日志表的状态
     * @param $remark       string 此次操作的名称
     * @param $requestData  string 请求的json数据
     *
     * @return mixed
     */
    public function updateOrderLog(
        $order_num,
        $snapshotType,
        $remark,
        $requestData = ''
    ) {
        $orderSnapshotInfo = DpOpderForm::query()
                                        ->with([
                                            'orderGoods.goods' => function (
                                                $query
                                            ) {
                                                $goodsWithArr = [
                                                    'goodsAttribute',
                                                    'specialAttribute',
                                                    'priceRule',
                                                    'goodsPicture',
                                                    'goodsInspectionReport',
                                                ];
                                                $query->with($goodsWithArr);
                                            },
                                        ])
                                        ->where('order_code', $order_num)
                                        ->first();
        $mainOrderNo = '';
        $subOrderNo = '';
        if (!empty($orderSnapshotInfo)) {
            $mainOrderNo = $orderSnapshotInfo->codenumber;
            $subOrderNo = $orderSnapshotInfo->order_code;
        } else {
            $orderSnapshotInfo = collect([]);
        }
        if (empty($requestData)) {
            $request_info = json_encode(request()->all());
        } else {
            $request_info = $requestData;
        }
        // 获取后台操作员信息
        $user = Auth::user();
        if (empty($user)) {
            throw new \Exception('登录已过期，请重新登录！');
        }
        $admin_name = $user->user_name;
        // 默认是0；1代表运营人员操作
        $operation = 1;
        $createArr = [
            'main_order_no' => $mainOrderNo,
            'sub_order_no'  => $subOrderNo,
            'request_info'  => $request_info,
            'snapshot_info' => $orderSnapshotInfo->toJson(),
            'snapshot_type' => $snapshotType,
            'remark'        => $remark,
            'operation'     => $operation,
            'name'          => $admin_name
        ];
        return DpOrderSnapshot::create($createArr)->id;
    }

    /**
     * 退款退货导致订单和订单商品表的状态更新
     *
     * @param $order_num string 子订单号
     * @param $orderact  int 订单状态
     * @param $good_act  int 订单商品表的状态
     */
    public function refundUpdateOrder($order_num, $orderact, $good_act)
    {
        DB::transaction(function () use ($order_num, $orderact, $good_act) {
            DpOpderForm::query()
                       ->where('order_code', $order_num)
                       ->update(['orderact' => $orderact]);
            DpCartInfo::query()
                      ->where('coid', $order_num)
                      ->update(['good_act' => $good_act]);
        });
    }

    /**
     * 获取退款/退货/取消的金额
     * @param $order_num
     * @param $refuse_goods
     *
     * @return array
     */
    public function getRefundPrice($order_num, $refuse_goods)
    {
        $orderObj = DpOpderForm::query()
                               ->with('orderGoods')
                               ->where('order_code', $order_num)
                               ->first();



        // 当没有退款商品信息时
        if (empty($refuse_goods)) {
            return [
                'code'    => 0,
                'message' => 'ok',
                'data'    => [
                    'goods_count'  => $orderObj->good_num,
                    'total_price'  => $orderObj->buy_realpay,
                    'reduce_price' => MoneyUnitConvertUtil::fenToYuan(
                        MoneyUnitConvertUtil::yuanToFen($orderObj->total_price) -
                        MoneyUnitConvertUtil::yuanToFen($orderObj->buy_realpay)
                    ),
                    'price'        => MoneyUnitConvertUtil::fenToYuan(0),
                ],
            ];
        } else {
            // 退款的总金额
            $totalPrice = 0;
            // 退款后应该支付的金额
            $afterRefund = 0;
            // 退完的种类数
            $goodsCount = 0;
            foreach ($refuse_goods as $item) {
                foreach ($orderObj->orderGoods as $key => $value) {
                    if ($item['id'] == $value->id) {
                        // 判断是否退完一种商品
                        if ($item['num'] >= $value->buy_num) {
                            $goodsCount = $goodsCount  + 1;
                        }
                        // 当前订单中的一种商品的实际支付金额
                        $goodsRealPay = MoneyUnitConvertUtil::yuanToFen($value->count_price);
                        $totalPrice += $item['num'] *
                                       ($goodsRealPay / $value->buy_num);

                        $afterRefund += ($value->buy_num - $item['num']) *
                                        MoneyUnitConvertUtil::yuanToFen($value->good_new_price) -
                                        MoneyUnitConvertUtil::yuanToFen($value->preferential_value);

                    }
                }
            }

            // 退款后实际支付的金额
            $afterPay = MoneyUnitConvertUtil::yuanToFen($orderObj->buy_realpay) - $totalPrice;
            // 退款后的优惠金额
            $reduce_price = $afterRefund - $afterPay;
            $reduce_price = ($reduce_price > 0) ? MoneyUnitConvertUtil::fenToYuan($reduce_price) : 0;

            return [
                'code'    => 0,
                'message' => 'ok',
                'data'    => [
                    'goods_count'  => $orderObj->good_num - $goodsCount,
                    'total_price'  => MoneyUnitConvertUtil::fenToYuan($afterPay),
                    'reduce_price' => $reduce_price,
                    'price'        => MoneyUnitConvertUtil::fenToYuan($totalPrice),
                ],
            ];
        }
    }

    /**
     * 退款退货冻结流程中的日志写入和退款退货表状态更新
     *
     * @param        $order_num     string 子订单号
     * @param        $result        object 前一个流程的对象
     * @param        $orderStatus   int 当前流程的状态
     * @param        $remark        string 备注
     * @param        $ip            string 操作的IP
     * @param string $refuse_reason 拒绝的原因
     */
    public function refundAndReturnUpdateStatus(
        $order_num,
        $result,
        $orderStatus,
        $remark,
        $ip,
        $refuse_reason = ''
    ) {
        DB::transaction(function () use (
            $order_num,
            $result,
            $refuse_reason,
            $orderStatus,
            $remark,
            $ip
        ) {
            // 更新订单日志信息
            $this->updateOrderLog($order_num, $orderStatus, $remark);
            // 更新退款退货表的状态
            if (empty($refuse_reason)) {
                $updateData = [
                    'order_status' => $orderStatus,
                    'form_ip'      => $ip,
                ];
            } else {
                $updateData = [
                    'order_status'  => $orderStatus,
                    'refuse_reason' => $refuse_reason,
                    'form_ip'       => $ip,
                ];
            }
            DpOrderOperation::query()
                            ->where('snapshots_id', $result->id)
                            ->update($updateData);
        });
    }

    /**
     * 同意退款和同意退货数据处理
     *
     * @param $order_num string 子订单号
     * @param $orderact  int 订单状态
     * @param $good_act  int 订单商品状态
     * @param $result    object 申请的流程对象（first）
     *
     */
    public function agreeDataHandle(
        $order_num,
        $orderact,
        $good_act,
        $result
    ) {
        $orderArr = \GuzzleHttp\json_decode($result->snapshot_info);
        // 统计退完商品的种类数量
        $goods_count = 0;
        // 更新订单商品信息
        DpCartInfo::query()
                  ->where('coid', $order_num)
                  ->update(['good_act'=>$good_act]);
        foreach ($result->orderOperation->aboutOrder as $key => $value) {
            $cartInfoObj = DpCartInfo::query()
                                     ->where('id', $value->order_goods_id)
                                     ->first();
            $buy_num = $value->old_buy_num - $value->refund_num;
            if ($buy_num <= 0) {
                // 订单商品中的购买数量没有时（即一种商品退完了）
                $goods_count += 1;
                switch ($good_act) {
                    case DpCartInfo::CONFIRM_ORDER_GOODS:
                    case DpCartInfo::REFUND_GOODS:
                        $good_act = DpCartInfo::REFUND_GOODS;
                        break;
                    case DpCartInfo::DELIVERY_ORDER_GOODS:
                    case DpCartInfo::REFUND_ORDER_GOODS:
                        $good_act = DpCartInfo::REFUND_ORDER_GOODS;
                        break;
                }
                $cartUpdate = [
                    'good_act' => $good_act,
                ];
                DpCartInfo::query()
                          ->where('coid', $order_num)
                          ->where('id', $value->order_goods_id)
                          ->update($cartUpdate);
            } else {
                // 一种商品部分退

                // 退款前一种商品的实际支付金额
                $goodsRealPay = MoneyUnitConvertUtil::yuanToFen($cartInfoObj->count_price);
                // 一种商品的退款金额
                $refund = ($goodsRealPay / $cartInfoObj->buy_num) * $value->refund_num;
                // 退款后一种商品的实际支付金额
                $goodsRealPay = $goodsRealPay - $refund;
                // 退款后的优惠值(退款后应支付 - 退款后实际支付)
                $relief_amount = $buy_num *
                                 MoneyUnitConvertUtil::yuanToFen($cartInfoObj->good_new_price) -
                                 $goodsRealPay;
                // 剩余一种商品的价格
                $remainPrice = $buy_num * MoneyUnitConvertUtil::yuanToFen($value->refund_price) - $relief_amount;
                $cartUpdate = [
                    'buy_num'            => $buy_num,
                    'good_act'           => $good_act,
                    'count_price'        => MoneyUnitConvertUtil::fenToYuan($remainPrice),
                    'preferential_value' => MoneyUnitConvertUtil::fenToYuan($relief_amount),
                ];
                DpCartInfo::query()
                          ->where('coid', $order_num)
                          ->where('id', $value->order_goods_id)
                          ->update($cartUpdate);
            }
        }

        // 更新订单信息
        // 从快照中获取订单信息
        $old_good_num = $orderArr->good_num;
        $old_good_count = $orderArr->good_count;
        $old_total_price = $orderArr->total_price;
        $old_buy_realpay = $orderArr->buy_realpay;
        // 当前剩余商品种类数量
        $update_good_num = $old_good_num - $goods_count;
        // 当前剩余商品总量
        $update_good_count = $old_good_count - $result->orderOperation->good_count;
        // 当前剩余商品的总价
        $update_total_price =
            MoneyUnitConvertUtil::fenToYuan(MoneyUnitConvertUtil::yuanToFen($old_total_price) -
                                            MoneyUnitConvertUtil::yuanToFen($result->orderOperation->refund));
        // 当前剩余商品的实际支付金额
        $update_buy_realpay =
            MoneyUnitConvertUtil::fenToYuan(MoneyUnitConvertUtil::yuanToFen($old_buy_realpay) -
                                            MoneyUnitConvertUtil::yuanToFen($result->orderOperation->real_refund));

        if ($update_good_num <= 0) {
            // 订单中剩余商品的种类数没有时（全部退完了）
            $orderUpdate = [
                'orderact' => $orderact,
            ];
            DpOpderForm::query()
                       ->where('order_code', $order_num)
                       ->update($orderUpdate);
        } else {
            // 订单部分退时
            $orderUpdate = [
                'good_num'    => $update_good_num,
                'good_count'  => $update_good_count,
                'total_price' => $update_total_price,
                'buy_realpay' => $update_buy_realpay,
                'orderact'    => $orderact,
            ];
            DpOpderForm::query()
                       ->where('order_code', $order_num)
                       ->update($orderUpdate);
        }
    }

    /**
     * 获取 申请-卖家取消/买家退款/买家退货 的状态
     * @param $result object 订单对象
     * @param $type int 申请类型
     * @param $kindNum int 取消/退款/退货的种类数量
     * @param $num int 取消/退款/退货的总数量
     *
     * @return array
     * @throws \Exception
     */
    public function getSaleApplyState($result, $type, $kindNum, $num)
    {
        $remark = '';
        $orderStatus = 0;
        $orderact = 0;
        $good_act = 0;
        switch ($type) {
            // 卖家申请取消
            case 1:
                $orderact = DpOpderForm::SELLER_CANCEL_ORDER_ING; // 订单状态变为卖家申请取消订单
                $good_act = DpCartInfo::SELLER_CANCEL_ORDER_ING; // 订单商品的状态变为卖家申请取消订单
                // 判断是部分退还是全退
                if ($kindNum == $result->good_num && $num == $result->good_count) {
                    // 全退
                    $remark = DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER];
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER;
                } else {
                    // 部分
                    $remark = DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME];
                    $orderStatus =
                        DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME;
                    // 如果是白条支付的订单，则不可以进行部分退款
                    if ($result->payment_method == DpOpderForm::PAYMENT_TYPE_IOUS) {
                        throw new \Exception('白条支付的订单不支持部分取消');
                    }
                }
                break;
            // 买家退款
            case 2:
                $orderact = DpOpderForm::REFUND_ORDER_FROM_PRICE_ING; // 订单状态变为申请退款
                $good_act = DpCartInfo::REFUND_GOODS_ING; // 订单商品的状态变为申请退款
                // 判断是部分退还是全退
                if ($kindNum == $result->good_num && $num == $result->good_count) {
                    // 全退
                    $remark = DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL];
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_ALL;
                } else {
                    // 部分
                    $remark = DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME];
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_SOME;
                    // 如果是白条支付的订单，则不可以进行部分退款
                    if ($result->payment_method == DpOpderForm::PAYMENT_TYPE_IOUS) {
                        throw new \Exception('白条支付的订单不支持部分取消');
                    }
                }
                break;
            // 买家退货
            case 3:
                $orderact = DpOpderForm::REFUND_ORDER_FROM_GOODS_ING; // 订单状态变为申请退货
                $good_act = DpCartInfo::REFUND_ORDER_FROM_GOODS_ING; // 订单商品的状态变为申请退货
                // 判断是部分退还是全退
                if ($kindNum == $result->good_num && $num == $result->good_count) {
                    // 全退
                    $remark = DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL];
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_ALL;
                } else {
                    // 部分
                    $remark = DpOrderSnapshot::$snapshotTypeArr[DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME];
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_SOME;
                    // 如果是白条支付的订单，则不可以进行部分退款
                    if ($result->payment_method == DpOpderForm::PAYMENT_TYPE_IOUS) {
                        throw new \Exception('白条支付的订单不支持部分取消');
                    }
                }
                break;
        }

        return [
            'remark'=>$remark,
            'orderStatus'=>$orderStatus,
            'orderact'=>$orderact,
            'good_act'=>$good_act
        ];
    }

    /**
     * 获取 取消申请-卖家取消/买家退款/买家退货 的状态
     * @param $result object 订单日志对象
     * @param $type int 申请类型
     *
     * @return array
     */
    public function getCancelApplyState($result, $type)
    {
        $remark = '';
        $orderStatus = 0;
        $orderact = 0;
        $good_act = 0;
        switch ($type) {
            // 卖家取消申请取消订单
            case 1:
                // 默认部分取消
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER_CANCEL_SOME;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER) {
                    // 全部取消
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER_CANCEL;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                }
                $orderact = DpOpderForm::CONFIRM_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::CONFIRM_ORDER_GOODS; // 状态变为卖家已确认
                break;
            // 买家取消退款
            case 2:
                // 默认部分取消
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_SOME_CANCEL;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL) {
                    // 全部取消
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_ALL_CANCEL;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                }
                $orderact = DpOpderForm::CONFIRM_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::CONFIRM_ORDER_GOODS; // 状态变为卖家已确认
                break;
            // 买家取消退货
            case 3:
                // 默认部分取消
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_CANCEL_SOME_NO;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL) {
                    // 全部取消
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_CANCEL_ALL_NO;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                }
                $orderact = DpOpderForm::DELIVERY_ORDER; // 状态变为已发货
                $good_act = DpCartInfo::DELIVERY_ORDER_GOODS; // 状态变为已发货
                break;
        }

        return [
            'remark'=>$remark,
            'orderStatus'=>$orderStatus,
            'orderact'=>$orderact,
            'good_act'=>$good_act
        ];
    }

    /**
     * 获取 拒绝-卖家取消/买家退款/买家退货 的状态
     * @param $result object 订单日志对象
     * @param $type int 申请类型
     *
     * @return array
     */
    public function getRefuseApplyState($result, $type)
    {
        $remark = '';
        $orderStatus = 0;
        $orderact = 0;
        $good_act = 0;
        switch ($type) {
            // 买家拒绝卖家取消订单
            case 1:
                // 默认部分取消
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER_NO_SOME;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER) {
                    // 全部取消
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER_NO;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                }
                $orderact = DpOpderForm::CONFIRM_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::CONFIRM_ORDER_GOODS; // 状态变为卖家已确认
                break;
            // 卖家拒绝买家退款
            case 2:
                // 默认部分取消
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_SOME_NO;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL) {
                    // 全部取消
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_ALL_NO;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                }
                $orderact = DpOpderForm::CONFIRM_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::CONFIRM_ORDER_GOODS; // 状态变为卖家已确认
                break;
            // 卖家拒绝买家退货
            case 3:
                // 默认部分取消
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_SOME_NO;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL) {
                    // 全部取消
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_ALL_NO;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                }
                $orderact = DpOpderForm::DELIVERY_ORDER; // 状态变为已发货
                $good_act = DpCartInfo::DELIVERY_ORDER_GOODS; // 状态变为已发货
                break;
        }

        return [
            'remark'=>$remark,
            'orderStatus'=>$orderStatus,
            'orderact'=>$orderact,
            'good_act'=>$good_act
        ];
    }

    /**
     * 获取 同意-卖家取消/买家退款/买家退货 的状态
     * @param $result object 订单日志对象
     * @param $type int 申请类型
     *
     * @return array
     */
    public function getAgreeApplyState($result, $type)
    {
        $remark = '';
        $orderStatus = 0;
        $orderact = 0;
        $good_act = 0;
        switch ($type) {
            // 买家同意卖家取消订单
            case 1:
                // 默认部分同意
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER_YES_SOME;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                $orderact = DpOpderForm::CONFIRM_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::CONFIRM_ORDER_GOODS; // 状态变为卖家已确认
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER) {
                    // 全部同意
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_CANCEL_ORDER_YES;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                    $orderact = DpOpderForm::INVALID_ORDER; // 状态变为无效订单-卖家删除
                    $good_act = DpCartInfo::INVALID_ORDER_GOODS; // 状态变为无效订单-卖家删除
                }
                break;
            // 卖家同意买家退款
            case 2:
                // 默认部分同意
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_SOME_YES;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                $orderact = DpOpderForm::CONFIRM_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::CONFIRM_ORDER_GOODS; // 状态变为卖家已确认
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL) {
                    // 全部同意
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_ALL_YES;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                    $orderact = DpOpderForm::REFUND_ORDER; // 状态变为退款订单
                    $good_act = DpCartInfo::REFUND_GOODS; // 状态变为退款订单
                }
                break;
            // 卖家同意买家退货
            case 3:
                // 默认部分同意
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_SOME_YES;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                $orderact = DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_WAIT; // 状态变为申请退货-退货中-待发货
                $good_act = DpCartInfo::REFUND_ORDER_FROM_RETURN_GOODS_ING_WAIT; // 状态变为申请退货-退货中-待发货
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL) {
                    // 全部同意
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_ALL_YES;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                    $orderact = DpOpderForm::REFUND_ORDER_FROM_RETURN_GOODS_ING_WAIT; // 状态变为申请退货-退货中-待发货
                    $good_act = DpCartInfo::REFUND_ORDER_FROM_RETURN_GOODS_ING_WAIT; // 状态变为申请退货-退货中-待发货
                }
                break;
        }

        return [
            'remark'=>$remark,
            'orderStatus'=>$orderStatus,
            'orderact'=>$orderact,
            'good_act'=>$good_act
        ];
    }

    /**
     * 获取 卖家确认退款 的状态
     * @param $result object 订单日志对象
     * @param $type int 申请类型
     *
     * @return array
     */
    public function getSellerRefundState($result, $type)
    {
        $remark = '';
        $orderStatus = 0;
        $orderact = 0;
        $good_act = 0;
        switch ($type) {
            // 卖家申请取消订单（买家同意后-卖家退款）
            case 1:
                // 默认部分同意
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_SOME_YES;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                $orderact = DpOpderForm::CONFIRM_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::CONFIRM_ORDER_GOODS; // 状态变为卖家已确认
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER) {
                    // 全部同意
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_REFUND_ALL_YES;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                    $orderact = DpOpderForm::INVALID_ORDER; // 状态变为无效订单-卖家删除
                    $good_act = DpCartInfo::INVALID_ORDER_GOODS; // 状态变为无效订单-卖家删除
                }
                break;
            // 买家退货后-卖家退款
            case 2:
                // 默认部分同意
                $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_SOME_YES_REFUND;
                $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                $orderact = DpOpderForm::DELIVERY_ORDER; // 状态变为卖家已确认
                $good_act = DpCartInfo::DELIVERY_ORDER_GOODS; // 状态变为卖家已确认
                if ($result->snapshot_type == DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL) {
                    // 全部同意
                    $orderStatus = DpOrderOperation::ORDER_FLOW_STATUS_RETURN_ALL_YES_REFUND;
                    $remark = DpOrderSnapshot::$snapshotTypeArr[$orderStatus];
                    $orderact = DpOpderForm::REFUND_ORDER_GOODS; // 状态变为退货订单
                    $good_act = DpCartInfo::REFUND_ORDER_GOODS; // 状态变为退货订单
                }
                break;
        }

        return [
            'remark'=>$remark,
            'orderStatus'=>$orderStatus,
            'orderact'=>$orderact,
            'good_act'=>$good_act
        ];
    }

    /**
     * 退款撤回的处理
     *
     * @see \App\Repositories\Contracts\RefundRepository::cancelRefund()
     *
     * @param string $refundNo
     * @param float  $refundAmount
     * @param int    $disposeUserId
     * @param string $disposeUserName
     * @param string $remark
     *
     * @return void
     */
    public function cancelRefund($refundNo, $refundAmount, $disposeUserId, $disposeUserName, $remark = '')
    {
        $updateRefundArr = [
            'status' => DpOrderRefund::CANCEL,
        ];
        $createRefundLogArr = [
            'refund_no'         => $refundNo,
            'operator_type'     => DpOrderRefundLog::CANCEL,
            'amount'            => $refundAmount,
            'dispose_user_id'   => $disposeUserId,
            'dispose_user_name' => $disposeUserName,
            'remark'            => $remark,
        ];

        DpOrderRefund::where('refund_no', $refundNo)
                     ->update($updateRefundArr);

        DpOrderRefundLog::create($createRefundLogArr);
    }

    /**
     * 还原订单商品信息
     * @param $order_num string 子订单号
     * @throws \Exception
     */
    public function recallOrderData($order_num)
    {
        // 获取申请的快照信息
        $orderLogObj = DpOrderSnapshot::query()
            ->where('sub_order_no', $order_num)
            ->whereIn('snapshot_type', [
                DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL,
                DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME,
                DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER,
                DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_SOME,
                DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL,
                DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME,
            ])
            ->orderBy('id', 'desc')
            ->first();
        if (empty($orderLogObj)) {
            throw new \Exception('订单流程错误,无法撤回');
        }

        $agreeOrderObj = DpOrderSnapshot::query()
            ->where('sub_order_no', $order_num)
            ->whereIn('snapshot_type', [
                DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_ALL_YES,
                DpOrderSnapshot::ORDER_FLOW_STATUS_REFUND_SOME_YES,
                DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_YES,
                DpOrderSnapshot::ORDER_FLOW_STATUS_CANCEL_ORDER_YES_SOME,
                DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_ALL_YES_REFUND,
                DpOrderSnapshot::ORDER_FLOW_STATUS_RETURN_SOME_YES_REFUND,
            ])
            ->orderBy('id', 'desc')
            ->first();

        DB::transaction(function () use (
            $orderLogObj,
            $agreeOrderObj
        ) {
            $orderObj = \GuzzleHttp\json_decode($agreeOrderObj->snapshot_info);
            // 删除最新的一条同意流程
            DpOrderSnapshot::query()
                           ->where('id', $agreeOrderObj->id)
                           ->delete();

            // 更新订单商品表的信息
            foreach ($orderObj->order_goods as $k => $v) {
                $cartData = [
                    'buy_num'            => $v->buy_num,
                    'count_price'        => $v->count_price,
                    'good_act'           => $v->good_act,
                    'reason'             => $v->reason,
                    'ip'                 => $v->ip,
                    'preferential_unit'  => $v->preferential_unit,
                    'preferential_name'  => $v->preferential_name,
                    'preferential_value' => $v->preferential_value,
                    'preferential_rule'  => $v->preferential_rule,
                ];
                DpCartInfo::query()
                          ->where('coid', $v->coid)
                          ->update($cartData);
            }

            // 更新订单信息
            $orderData = [
                'good_num'       => $orderObj->good_num,
                'good_count'     => $orderObj->good_count,
                'total_price'    => $orderObj->total_price,
                'relief_amount'  => $orderObj->relief_amount,
                'coupons'        => $orderObj->coupons,
                'method'         => $orderObj->method,
                'delivery'       => $orderObj->delivery,
                'method_act'     => $orderObj->method_act,
                'orderact'       => $orderObj->orderact,
                'buy_realpay'    => $orderObj->buy_realpay,
                'order_clear'    => $orderObj->order_clear,
                'order_received' => $orderObj->order_received,
            ];

            DpOpderForm::query()
                       ->where('order_code', $orderObj->order_code)
                       ->update($orderData);
        });
    }
}
