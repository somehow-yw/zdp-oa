<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24 0024
 * Time: 下午 4:38
 */
namespace App\Repositories\Refund\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface RefundRepository
{
    /**
     * 查询此订单是否有未处理完的退款申请
     * @param $subOrderNo  string 子订单号
     *
     * @return Collection
     */
    public function getProcessingRefundInfo($subOrderNo);

    /**
     * 订单退款申请
     *
     * @param string $refundNo          退款编号
     * @param int    $subOrderId        子订单ID
     * @param string $subOrderNo        子订单编号
     * @param array  $orderGoodsIdArr   退款的订单商品ID串
     * @param float  $paymentAmount     实付金额
     * @param float  $refundAmount      退款金额
     * @param string $remark            退款说明
     * @param int    $applyUserId       申请者ID
     * @param string $applyUserName     申请者名称
     * @param int    $buyersShopId      买家店铺ID
     * @param int    $sellShopId        卖家店铺ID
     * @param int    $rawState          退款前订单状态
     * @param float  $refundGoodsAmount 退款商品总价
     * @param int    $goodsCount        退款商品个数
     * @param int    $buyGoodsTotalNum  退款商品总购买量
     * @param array  $scoreTypes        店铺处罚规则编号 格式如：[1,2,3]
     * @param string $assentor          退款同意人
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
    );

}