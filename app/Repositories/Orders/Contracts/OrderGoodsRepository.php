<?php
/**
 * Created by PhpStorm.
 * 订单商品类相关数据操作
 * User: fer
 * Date: 2016/8/31
 * Time: 11:09
 */

namespace App\Repositories\Orders\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface OrderGoodsRepository
{
    /**
     * 取得商品昨日销量
     *
     * @param int $goodsId 商品ID
     *
     * @return int
     */
    public function getYesterdaySales($goodsId);

    /**
     * 获取全部订单的列表信息
     * @param $order_id integer 订单id
     * @param $order_num string 订单号
     * @param $orderState int 订单的状态
     * @param $buy_shop string 买家店铺名字
     * @param $seller_shop string 卖家店铺名字
     * @param $buy_phone integer 买家手机号
     * @param $seller_phone integer 卖家手机号
     * @param $page integer 页数
     * @param $size integer 每页显示数量
     *
     * @return array
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
    );

    /**
     * 获取订单详情
     * @param $order_num string 大订单号
     *
     * @return Collection
     */
    public function getDetail($order_num);

    /**
     * 获取财务退款的订单列表信息
     * @param $order_id integer 订单id
     * @param $order_num string 订单号
     * @param $orderState int 订单的状态
     * @param $buy_shop string 买家店铺名字
     * @param $seller_shop string 卖家店铺名字
     * @param $buy_phone integer 买家手机号
     * @param $seller_phone integer 卖家手机号
     * @param $page integer 页数
     * @param $size integer 每页显示数量
     *
     * @return Collection
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
    );


    /**
     * 获取取消订单的理由列表
     * @param $type
     *
     * @return Collection
     */
    public function getReasonList($type);

    /**
     * 获取财务退款表中的信息
     * @param $order_num string 子订单号
     *
     * @return Collection
     */
    public function getFinanceRefund($order_num);
}