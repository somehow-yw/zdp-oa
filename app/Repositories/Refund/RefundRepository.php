<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24 0024
 * Time: 下午 4:39
 */
namespace App\Repositories\Refund;

use App\Repositories\Refund\Contracts\RefundRepository as RepositoriesContract;
use Zdp\Main\Data\Models\DpOpderForm;
use Zdp\Main\Data\Models\DpOrderRefund;

class RefundRepository implements RepositoriesContract
{
    /**
     * @see \App\Repositories\Contracts\RefundRepository::getProcessingRefundInfo
     */
    public function getProcessingRefundInfo($subOrderNo)
    {
        $refundStateArr = [
            DpOrderRefund::PENDING,
            DpOrderRefund::BEING_PROCESSED,
            DpOrderRefund::FAILURE,
        ];

        return DpOrderRefund::where('sub_order_no', $subOrderNo)
                            ->whereIn('status', $refundStateArr)
                            ->get();
    }

    /**
     * @see \App\Repositories\Contracts\RefundRepository::genRefundApply
     */
    public function genRefundApply(
        $refundNo,
        $subOrderId,
        $subOrderNo,
        $orderGoodsIdArr,
        $paymentAmount,
        $refundAmount,
        $remark,
        $applyUserId,
        $applyUserName,
        $buyersShopId,
        $sellShopId,
        $rawState,
        $refundGoodsAmount,
        $goodsCount,
        $buyGoodsTotalNum,
        $scoreTypes,
        $assentor
    ) {
        $refundAddArr = [
            'refund_no'           => $refundNo,
            'sub_order_id'        => $subOrderId,
            'sub_order_no'        => $subOrderNo,
            'raw_state'           => $rawState,
            'buyers_shop_id'      => $buyersShopId,
            'sell_shop_id'        => $sellShopId,
            'order_goods_ids'     => implode(',', $orderGoodsIdArr),
            'goods_num'           => $goodsCount,
            'buy_goods_total_num' => $buyGoodsTotalNum,
            'refund_goods_amount' => $refundGoodsAmount,
            'payment_amount'      => $paymentAmount,
            'refund_amount'       => $refundAmount,
            'status'              => DpOrderRefund::PENDING,
            'apply_user_id'       => $applyUserId,
            'apply_user_name'     => $applyUserName,
            'remark'              => $remark,
            'shop_score_type_ids' => json_encode($scoreTypes),
            'assentor'            => $assentor,
        ];
        $refundLogAddArr = [
            'refund_no'         => $refundNo,
            'operator_type'     => DpOrderRefundLog::APPLY,
            'amount'            => $refundAmount,
            'dispose_user_id'   => $applyUserId,
            'dispose_user_name' => $applyUserName,
            'remark'            => $remark,
        ];
        DpOrderRefund::create($refundAddArr);
        DpOrderRefundLog::create($refundLogAddArr);
    }

}