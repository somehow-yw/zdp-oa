<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/13 0013
 * Time: 下午 3:34
 */

namespace App\Exceptions\Order;

use App\Exceptions\AppException;

/**
 * 订单异常说明处理
 * Order exception code definitions
 */
class OrderException extends AppException
{
    /**
     * 订单不存在
     */
    const ORDER_NO_EXIST = 101;

    /**
     * 订单实付金额大于订单总金额
     */
    const PAY_PRICE_ERROR = [
        'code'    => 102,
        'message' => '订单实付金额已大于订单总金额',
    ];

    /**
     * 还有集采未报价订单，不可支付
     */
    const NOT_PAYMENT = [
        'code'    => 103,
        'message' => '还有集采未报价订单，不可支付',
    ];

    public function __construct(array $throwInfo, $message = '')
    {
        if (count($throwInfo)) {
            $message = $throwInfo['message'];
            $exceptionCode = $throwInfo['code'];
        } else {
            $exceptionCode = null;
        }

        parent::__construct($message, $exceptionCode);
    }
}
