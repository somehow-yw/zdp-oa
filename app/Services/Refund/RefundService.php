<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24 0024
 * Time: 下午 4:36
 */
namespace App\Services\Refund;

use App\Repositories\Refund\Contracts\RefundRepository;
use App\Utils\MoneyUnitConvertUtil;
use Zdp\Main\Data\Models\DpOpderForm;
use Zdp\Main\Data\Models\DpOrderRefund;
use App\Utils\GenerateRandomNumber;
use App\Utils\RequestDataEncapsulationUtil;
use Zdp\Main\Data\Models\DpShangHuInfo;
use Zdp\Main\Data\Models\DpShopInfo;
use App\Utils\HTTPRequestUtil;


class RefundService
{
    /**
     * @var RefundRepository
     */
    private $repository;
    /**
     * @var HTTPRequestUtil
     */
    private $requestUtil;

    public function __construct(
        RefundRepository $repository,
        HTTPRequestUtil $requestUtil
    ) {
        $this->repository = $repository;
        $this->requestUtil = $requestUtil;
    }

    /**
     * 订单退款申请
     *
     * @param string      $subOrderNo            子订单编号
     * @param array       $orderGoodsIdArr       退款的订单商品ID
     * @param string      $remark                退款说明
     * @param int         $applyUserId           申请者ID
     * @param string      $applyUserName         申请者名称
     * @param array       $scoreTypes            店铺处罚规则编号 格式如：[1,2,3]
     * @param string      $assentor              同意此次操作人
     * @param int         $refundFreightPriceFen 需退掉的运费金额 单位：分
     * @param object|null $refundObj             退款数据
     *
     * @return array
     * @throws \Exception
     */
    public function genRefundApply(
        $subOrderNo,
        array $orderGoodsIdArr,
        $remark,
        $applyUserId,
        $applyUserName,
        $scoreTypes,
        $assentor = '系统',
        $refundFreightPriceFen = 0,
        $refundObj = null
    )
    {
        // 查询此订单是否有未处理完的退款申请，有就不允许再次申请
        $refundInfoObj =
            $this->repository->getProcessingRefundInfo($subOrderNo);
        if (!$refundInfoObj->isEmpty()) {
            throw new \Exception('请先等待之前的退款处理完成后再次申请');
        }

        // 取得订单信息
        $subOrderInfoObj = DpOpderForm::query()
                                      ->where('order_code', $subOrderNo)
                                      ->first();
        if (!$subOrderInfoObj) {
            throw new \Exception('订单不存在');
        }

        // 取得待退款的订单商品信息
        $refundOrderGoodsInfoObj = $refundObj;
        if (!$refundOrderGoodsInfoObj) {
            throw new \Exception('订单商品信息不存在');
        }

        // 生成退款编号
        asort($orderGoodsIdArr);
        $orderGoodsIds = implode(',', $orderGoodsIdArr);
        $refundNo = md5(time() . $subOrderNo . $orderGoodsIds);
        $refundNo = GenerateRandomNumber::generateString(16, $refundNo);

        $goodsTotalPrice =
            MoneyUnitConvertUtil::yuanToFen($refundOrderGoodsInfoObj->real_refund);   // 退款商品总金额
        $orderRealpayPrice =
            MoneyUnitConvertUtil::yuanToFen($subOrderInfoObj->buy_realpay);        // 订单实付金额
        $orderTotalPrice =
            MoneyUnitConvertUtil::yuanToFen($subOrderInfoObj->total_price);          // 订单总金额
        $orderReliefPrice =
            MoneyUnitConvertUtil::yuanToFen($subOrderInfoObj->relief_amount);       // 订单减免金额
        if ($goodsTotalPrice != $orderTotalPrice &&
            $goodsTotalPrice > $orderRealpayPrice) {
            throw new \Exception('退款商品的金额与订单实付金额不相符');
        } elseif ($goodsTotalPrice > $orderTotalPrice) {
            throw new \Exception('实付金额与订单应付金额不相符');
        }

        // 减免金额（分）
        if ($orderRealpayPrice != ($orderTotalPrice - $orderReliefPrice)) {
            $orderReliefPrice = $orderTotalPrice - $orderRealpayPrice;
        }

        // 得到退款金额
        if ($goodsTotalPrice == $orderTotalPrice) {
            // 全退
            $refundAmount = $subOrderInfoObj->buy_realpay;
            $allRefund = DpOrderRefund::ALL_REFUND;
        } else {
            // 部分商品退款
            // 如果是白条支付的订单，则不可以进行部分退款
            if ($subOrderInfoObj->payment_method ==
                DpOpderForm::PAYMENT_TYPE_IOUS) {
                throw new \Exception(
                    '白条支付的订单不支持部分退货'
                );
            }
            if ($refundFreightPriceFen > 0) {
                // 如果有部分运费需做退款
                $refundOrderGoodsTotalPriceFen = $goodsTotalPrice;
                $refundAmount =
                    MoneyUnitConvertUtil::fenToYuan($refundOrderGoodsTotalPriceFen +
                                                    $refundFreightPriceFen);
            } else {
                $refundAmount =
                    MoneyUnitConvertUtil::fenToYuan($goodsTotalPrice);
            }
            $allRefund = DpOrderRefund::PART_REFUND;
        }
        // 如果退款金额大于了实际付款金额，则只能退实际付款金额。一般出现在有减免时
        if ($refundAmount > $subOrderInfoObj->buy_realpay) {
            $refundAmount = $subOrderInfoObj->buy_realpay;
        }

        if ($refundAmount >
            config('payment.pay_setting.we_chat_refund_limit')) {
            throw new \Exception(
                '退款金额超过了最高支付限额，请选用其它退款方式'
            );
        }

        // 取得买家信息
        $buyersShopInfoObj = DpShangHuInfo::query()
                                          ->where('shId', $subOrderInfoObj->uid)
                                          ->first();
        // 取得卖家信息
        $sellShopInfoObj = DpShopInfo::query()
                                     ->with([
                                         'user' => function ($query) {
                                             $query->where('laoBanHao', DpShangHuInfo::SHOP_BOOS);
                                         },
                                     ])
                                     ->where('shopId', $subOrderInfoObj->shopid)
                                     ->first();


        // 记录退款信息并通知财务系统
        $this->addRefundInfo(
            $refundNo,
            $subOrderInfoObj,
            $subOrderNo,
            $orderGoodsIdArr,
            $refundAmount,
            $remark,
            $applyUserId,
            $applyUserName,
            $allRefund,
            $refundOrderGoodsInfoObj,
            $buyersShopInfoObj,
            $sellShopInfoObj,
            $orderReliefPrice,
            $scoreTypes,
            $assentor
        );
    }

    /**
     * 记录退款信息，并通知财务系统
     *
     * @param $refundNo
     * @param $subOrderInfoObj
     * @param $subOrderNo
     * @param $orderGoodsIdArr
     * @param $refundAmount
     * @param $remark
     * @param $applyUserId
     * @param $applyUserName
     * @param $allRefund
     * @param $refundOrderGoodsInfoObj
     * @param $buyersShopInfoObj
     * @param $sellShopInfoObj
     * @param $orderReliefPrice
     * @param $scoreTypes
     * @param $assentor
     */
    private function addRefundInfo(
        $refundNo,
        $subOrderInfoObj,
        $subOrderNo,
        $orderGoodsIdArr,
        $refundAmount,
        $remark,
        $applyUserId,
        $applyUserName,
        $allRefund,
        $refundOrderGoodsInfoObj,
        $buyersShopInfoObj,
        $sellShopInfoObj,
        $orderReliefPrice,
        $scoreTypes,
        $assentor
    ) {
        $self = $this;
        DB::transaction(
            function () use (
                $self,
                $refundNo,
                $subOrderInfoObj,
                $subOrderNo,
                $orderGoodsIdArr,
                $refundAmount,
                $remark,
                $applyUserId,
                $applyUserName,
                $allRefund,
                $refundOrderGoodsInfoObj,
                $buyersShopInfoObj,
                $sellShopInfoObj,
                $orderReliefPrice,
                $scoreTypes,
                $assentor
            ) {
                $self->repository->genRefundApply(
                    $refundNo,
                    $subOrderInfoObj->id,
                    $subOrderNo,
                    $orderGoodsIdArr,
                    $subOrderInfoObj->buy_realpay,
                    $refundAmount,
                    $remark,
                    $applyUserId,
                    $applyUserName,
                    $buyersShopInfoObj->shop->shopId,
                    $subOrderInfoObj->shopid,
                    $subOrderInfoObj->orderact,
                    $refundOrderGoodsInfoObj->refund,
                    $refundOrderGoodsInfoObj->good_count,
                    $refundOrderGoodsInfoObj->good_num,
                    $scoreTypes,
                    $assentor
                );

                // 通知财务系统处理
                $self->refundNoticeFinance(
                    $refundNo,
                    $subOrderInfoObj,
                    $subOrderNo,
                    $orderGoodsIdArr,
                    $buyersShopInfoObj,
                    $orderReliefPrice,
                    $refundAmount,
                    $sellShopInfoObj,
                    $applyUserId,
                    $applyUserName,
                    $remark,
                    $assentor
                );
            }
        );
    }

    /**
     * 退款通知财务系统
     *
     * @param string $refundNo          退款编号
     * @param object $subOrderInfoObj   子订单信息对象
     * @param string $subOrderNo        子订单编号
     * @param array  $orderGoodsIdArr   需退款订单商品ID 格式:[1,2,3]
     * @param object $buyersShopInfoObj 买家店铺信息对象
     * @param float  $orderReliefPrice  退款订单减免金额
     * @param float  $refundAmount      退款金额
     * @param object $sellShopInfoObj   退款订单卖家信息对象
     * @param int    $applyUserId       退款申请者ID
     * @param string $applyUserName     退款申请者名称
     * @param string $remark            退款备注
     * @param        $assentor
     *
     * @throws \Exception
     */
    private function refundNoticeFinance(
        $refundNo,
        $subOrderInfoObj,
        $subOrderNo,
        $orderGoodsIdArr,
        $buyersShopInfoObj,
        $orderReliefPrice,
        $refundAmount,
        $sellShopInfoObj,
        $applyUserId,
        $applyUserName,
        $remark,
        $assentor
    ) {
        $url = sprintf('%s/refund/apply', config('serverDomain.zdp_finance'));

        $data = [
            'batch_no'         => $refundNo,
            'sub_order_id'     => $subOrderInfoObj->id,
            'sub_order_no'     => $subOrderNo,
            'order_goods_ids'  => json_encode($orderGoodsIdArr),
            'buyers_shop_name' => $buyersShopInfoObj->shop->dianPuName,
            'payment_method'   => $subOrderInfoObj->payment_method,
            'subsidy_amount'   => $orderReliefPrice,
            'payment_amount'   => MoneyUnitConvertUtil::yuanToFen($subOrderInfoObj->buy_realpay),
            'refund_amount'    => MoneyUnitConvertUtil::yuanToFen($refundAmount),
            'sell_shop_id'     => $subOrderInfoObj->shopid,
            'sell_shop_name'   => $sellShopInfoObj->dianPuName,
            'apply_user_id'    => $applyUserId,
            'apply_user_name'  => $applyUserName,
            'remark'           => $remark,
            'assentor'         => $assentor,
        ];
        // 签名
        $requestData = RequestDataEncapsulationUtil::getHttpRequestSign(
            $data,
            config('signature.financeSignKey')
        );
        $res = $this->requestUtil->post(
            $url,
            $requestData,
            [
                'Accept' => 'application/json',
            ]
        );
        $resArr = json_decode($res, true);
        if (json_last_error() != 0 || $resArr['code'] > 0) {
            throw new \Exception(
                sprintf('提交财务系统处理失败-错误码%d-错误信息%s', $resArr['code'], $resArr['message'])
            );
        }
    }
}